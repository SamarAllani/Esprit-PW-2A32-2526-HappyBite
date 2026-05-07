-- ============================================
-- SCRIPT DE CORRECTION BASE HAPPYBITE
-- ============================================

USE happybite;

-- Table login_attempts pour rate limiting
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    email VARCHAR(120),
    user_agent TEXT,
    success TINYINT DEFAULT 0,
    attempt_time DATETIME NOT NULL,
    INDEX idx_ip_time (ip_address, attempt_time),
    INDEX idx_attempt_time (attempt_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table user_tokens
CREATE TABLE IF NOT EXISTS user_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at),
    FOREIGN KEY (user_id) REFERENCES utilisateur(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table login_logs
CREATE TABLE IF NOT EXISTS login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    login_time DATETIME NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    success TINYINT DEFAULT 0,
    INDEX idx_user_id (user_id),
    INDEX idx_login_time (login_time),
    FOREIGN KEY (user_id) REFERENCES utilisateur(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Nettoyer les anciennes tentatives
DELETE FROM login_attempts;

-- Vérifier les colonnes reset_token et reset_expires dans utilisateur
ALTER TABLE utilisateur 
ADD COLUMN IF NOT EXISTS reset_token VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS reset_expires DATETIME DEFAULT NULL,
ADD COLUMN IF NOT EXISTS referral_code VARCHAR(20) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS referred_by INT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS loyalty_points INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS order_count INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS referral_count INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS first_order_at DATETIME DEFAULT NULL,
ADD COLUMN IF NOT EXISTS first_order_rewarded TINYINT DEFAULT 0,
ADD COLUMN IF NOT EXISTS reward_5_referrals TINYINT DEFAULT 0,
ADD COLUMN IF NOT EXISTS reward_10_referrals TINYINT DEFAULT 0,
ADD COLUMN IF NOT EXISTS reward_20_referrals TINYINT DEFAULT 0,
ADD COLUMN IF NOT EXISTS profile_photo VARCHAR(255) DEFAULT NULL,
ADD INDEX IF NOT EXISTS idx_reset_token (reset_token),
ADD INDEX IF NOT EXISTS idx_referral_code (referral_code),
ADD INDEX IF NOT EXISTS idx_referred_by (referred_by);

CREATE TABLE IF NOT EXISTS loyalty_referrals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referrer_id INT NOT NULL,
    referee_id INT NOT NULL UNIQUE,
    referral_code VARCHAR(20) NOT NULL,
    first_order_at DATETIME DEFAULT NULL,
    first_order_rewarded TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_referrer_id (referrer_id),
    INDEX idx_referral_code (referral_code),
    FOREIGN KEY (referrer_id) REFERENCES utilisateur(id) ON DELETE CASCADE,
    FOREIGN KEY (referee_id) REFERENCES utilisateur(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS loyalty_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reference_user_id INT DEFAULT NULL,
    type ENUM('order', 'referral_bonus', 'milestone_bonus', 'redeem', 'manual') NOT NULL,
    points INT NOT NULL DEFAULT 0,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    category VARCHAR(80) DEFAULT NULL,
    notes VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_category (category),
    FOREIGN KEY (user_id) REFERENCES utilisateur(id) ON DELETE CASCADE,
    FOREIGN KEY (reference_user_id) REFERENCES utilisateur(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for storing WebAuthn / platform authenticator credentials (Face ID, Windows Hello)
CREATE TABLE IF NOT EXISTS webauthn_credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    credential_id VARCHAR(512) NOT NULL,
    public_key TEXT DEFAULT NULL,
    attestation_raw MEDIUMBLOB DEFAULT NULL,
    client_data_json MEDIUMBLOB DEFAULT NULL,
    sign_count INT DEFAULT 0,
    transports VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_credential_id (credential_id(191)),
    FOREIGN KEY (user_id) REFERENCES utilisateur(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Afficher les tables
SHOW TABLES;

-- Vérifier l'admin
SELECT id, email, role, statut FROM utilisateur WHERE role = 'admin';
