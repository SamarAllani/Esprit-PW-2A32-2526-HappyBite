-- Réinitialiser les mots de passe
UPDATE utilisateur SET motDePasse = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email = 'admin123@happybite.com';
UPDATE utilisateur SET motDePasse = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email = 'admin2026@happybite.com';
UPDATE utilisateur SET motDePasse = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email = 'koussay@happybite.com';

SELECT email, role, 'admin123' as nouveau_mot_de_passe FROM utilisateur WHERE email IN ('admin123@happybite.com', 'admin2026@happybite.com', 'koussay@happybite.com');
