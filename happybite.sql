-- ============================================
-- HAPPYBITE DATABASE - VERSION COMPLÈTE
-- ============================================

DROP DATABASE IF EXISTS happybite;
CREATE DATABASE happybite;
USE happybite;

-- Table utilisateur
CREATE TABLE utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prenom VARCHAR(100) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    motDePasse VARCHAR(255) NOT NULL,
    role ENUM('admin', 'client', 'nutritionniste', 'fournisseur') DEFAULT 'client',
    statut ENUM('actif', 'bloqué', 'inactif') DEFAULT 'actif',
    poid DECIMAL(5,2) DEFAULT 0,
    objectif VARCHAR(50) DEFAULT 'maintenir',
    allergie TEXT,
    carence TEXT,
    budget DECIMAL(10,2) DEFAULT 0,
    description TEXT,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_statut (statut),
    INDEX idx_reset_token (reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin (mot de passe: admin123)
INSERT INTO utilisateur (prenom, nom, email, motDePasse, role, statut, objectif, allergie, carence, budget, description) 
VALUES (
    'Admin', 'HappyBite', 'admin2026@happybite.com', 
    '$2y$10$d3nCx1JMZMbJzHkswPBbM.xOc/mM.fGF3zJwP8vZLWU9VvZfSmv8a', 
    'admin', 'actif', 'administrer', 'Aucune', 'Aucune', 0, 'Administrateur principal'
);

-- Client test (mot de passe: client123)
INSERT INTO utilisateur (prenom, nom, email, motDePasse, role, statut, poid, objectif, allergie, carence, budget, description) 
VALUES (
    'Jean', 'Dupont', 'client@happybite.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'client', 'actif', 75.5, 'maintenir', 'Gluten', 'Vitamine D', 500, 'Client test'
);

-- Nutritionniste test (mot de passe: nutrition123)
INSERT INTO utilisateur (prenom, nom, email, motDePasse, role, statut, poid, objectif, allergie, carence, budget, description) 
VALUES (
    'Sophie', 'Martin', 'nutrition@happybite.com', 
    '$2y$10$rQxY5qZqZqZqZqZqZqZqZu', 
    'nutritionniste', 'actif', 0, 'conseiller', 'Aucune', 'Aucune', 0, 'Nutritionniste certifiée'
);

-- Fournisseur test (mot de passe: fournisseur123)
INSERT INTO utilisateur (prenom, nom, email, motDePasse, role, statut, poid, objectif, allergie, carence, budget, description) 
VALUES (
    'Marc', 'Bernard', 'fournisseur@happybite.com', 
    '$2y$10$fQxY5qZqZqZqZqZqZqZqZu', 
    'fournisseur', 'actif', 0, 'fournir', 'Aucune', 'Aucune', 0, 'Fournisseur de produits bio'
);

SELECT * FROM utilisateur;