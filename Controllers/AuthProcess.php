<?php

declare(strict_types=1);

/**
 * Traitement connexion / inscription / WebAuthn (adapté depuis PR/Controller/PR.php).
 * Point d’entrée : POST avec champ "action".
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../Models/AuthUserModel.php';
require_once __DIR__ . '/AuthFaceSupport.php';

$pdo = Database::getConnection();
$userModel = new AuthUserModel($pdo);

function authApplyLoginSession(array $user): void
{
    $_SESSION['user_id'] = authUtilisateurIdFromRow($user);
    $_SESSION['user_prenom'] = $user['prenom'];
    $_SESSION['user_nom'] = $user['nom'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_statut'] = $user['statut'] ?? 'actif';
    $_SESSION['logged_in'] = true;
}

function authVerifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

function authColumnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column'
    );
    $stmt->execute(['table' => $table, 'column' => $column]);
    return (int) $stmt->fetchColumn() > 0;
}

/** Colonne PK de `utilisateur` : schéma ancien = id_utilisateur, schéma auth = id */
function authUtilisateurPkColumn(PDO $pdo): string
{
    static $col = null;
    if ($col !== null) {
        return $col;
    }
    if (authColumnExists($pdo, 'utilisateur', 'id')) {
        $col = 'id';
    } elseif (authColumnExists($pdo, 'utilisateur', 'id_utilisateur')) {
        $col = 'id_utilisateur';
    } else {
        $col = 'id';
    }
    return $col;
}

function authUtilisateurIdFromRow(array $row): int
{
    return (int) ($row['id'] ?? $row['id_utilisateur'] ?? 0);
}

function authHandleProfilePhotoUpload(array $file): ?string
{
    $uploadDir = __DIR__ . '/../uploads/users pictures/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }
    if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed, true)) {
        return null;
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        return null;
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $filepath = $uploadDir . $filename;
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/users pictures/' . $filename;
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $_SESSION['error'] = 'Email et mot de passe requis';
            header('Location: ../FrontOffice/auth/login.php');
            exit;
        }

        try {
            $stmt = $pdo->prepare('SELECT * FROM utilisateur WHERE email = :email');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && authVerifyPassword($password, (string) $user['motDePasse'])) {
                if (($user['statut'] ?? '') === 'bloqué') {
                    $_SESSION['error'] = 'Compte bloqué';
                    header('Location: ../FrontOffice/auth/login.php');
                    exit;
                }

                authApplyLoginSession($user);

                header('Location: ../FrontOffice/Home.php');
                exit;
            }

            $_SESSION['error'] = 'Email ou mot de passe incorrect';
            header('Location: ../FrontOffice/auth/login.php');
            exit;
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Erreur technique : ' . $e->getMessage();
            header('Location: ../FrontOffice/auth/login.php');
            exit;
        }
    }

    if ($action === 'face_enroll') {
        header('Content-Type: application/json; charset=utf-8');
        if (!authColumnExists($pdo, 'utilisateur', 'face_auth_image')) {
            echo json_encode(['ok' => false, 'error' => 'Colonne face_auth_image absente. Exécutez la migration SQL.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $snapshot = (string) ($_POST['snapshot'] ?? '');
        $jpeg = authFaceDecodeSnapshot($snapshot);
        if ($email === '' || $jpeg === null || strlen($jpeg) < 500) {
            echo json_encode(['ok' => false, 'error' => 'Email ou image invalide.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (strlen($jpeg) > 2_500_000) {
            echo json_encode(['ok' => false, 'error' => 'Image trop volumineuse.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $allowed = false;
        if (!empty($_SESSION['logged_in']) && isset($_SESSION['user_email']) && strtolower((string) $_SESSION['user_email']) === $email) {
            $allowed = true;
        }
        if (!$allowed && !empty($_SESSION['just_registered_email']) && strtolower((string) $_SESSION['just_registered_email']) === $email) {
            $allowed = true;
        }
        if (!$allowed) {
            echo json_encode(['ok' => false, 'error' => 'Non autorisé : connectez-vous ou inscrivez-vous puis utilisez le lien depuis la même session.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $user = $userModel->findByEmail($email);
        if (!$user) {
            echo json_encode(['ok' => false, 'error' => 'Utilisateur introuvable.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $uid = authUtilisateurIdFromRow($user);
        $rel = authFaceSaveEnrollmentFile($uid, $jpeg);
        if ($rel === null) {
            echo json_encode(['ok' => false, 'error' => 'Échec enregistrement fichier.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $pk = authUtilisateurPkColumn($pdo);
        $stmt = $pdo->prepare('UPDATE utilisateur SET face_auth_image = :p WHERE `' . $pk . '` = :id');
        $stmt->execute(['p' => $rel, 'id' => $uid]);
        unset($_SESSION['just_registered_email']);
        echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'face_login') {
        header('Content-Type: application/json; charset=utf-8');
        if (!authColumnExists($pdo, 'utilisateur', 'face_auth_image')) {
            echo json_encode(['ok' => false, 'error' => 'Face ID non configuré côté base (colonne face_auth_image).'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $snapshot = (string) ($_POST['snapshot'] ?? '');
        $jpeg = authFaceDecodeSnapshot($snapshot);
        if ($email === '' || $jpeg === null || strlen($jpeg) < 500) {
            echo json_encode(['ok' => false, 'error' => 'Email ou image invalide.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $user = $userModel->findByEmail($email);
        if (!$user) {
            echo json_encode(['ok' => false, 'error' => 'Aucun compte pour cet email.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (($user['statut'] ?? '') === 'bloqué') {
            echo json_encode(['ok' => false, 'error' => 'Compte bloqué.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $rel = trim((string) ($user['face_auth_image'] ?? ''));
        if ($rel === '') {
            echo json_encode(['ok' => false, 'error' => 'Aucun visage enregistré pour ce compte. Utilisez « Enregistrer Face ID » après inscription.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $abs = dirname(__DIR__) . '/' . $rel;
        if (!authFaceMatchStored($abs, $jpeg)) {
            echo json_encode(['ok' => false, 'error' => 'Visage non reconnu. Réessayez avec un bon éclairage.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        authApplyLoginSession($user);
        // Toujours rediriger vers le front-office (connexion BO séparée : BackOffice/login.php)
        echo json_encode(['ok' => true, 'redirect' => '../Home.php'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'webauthn_register_challenge') {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        if ($email === '') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Email requis']);
            exit;
        }
        $user = $userModel->findByEmail($email);
        if (!$user) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Utilisateur non trouvé']);
            exit;
        }

        $challenge = bin2hex(random_bytes(32));
        $_SESSION['webauthn_challenge_' . $email] = $challenge;

        $options = [
            'challenge' => base64_encode($challenge),
            'rp' => ['name' => 'HappyBite'],
            'user' => [
                'id' => base64_encode((string) authUtilisateurIdFromRow($user)),
                'name' => $user['email'],
                'displayName' => ($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''),
            ],
            'pubKeyCredParams' => [['type' => 'public-key', 'alg' => -7]],
            'authenticatorSelection' => ['authenticatorAttachment' => 'platform', 'userVerification' => 'preferred'],
            'timeout' => 60000,
            'attestation' => 'none',
        ];

        header('Content-Type: application/json');
        echo json_encode($options);
        exit;
    }

    if ($action === 'webauthn_register_response') {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $credId = (string) ($_POST['id'] ?? '');
        $attestation = $_POST['attestation'] ?? null;
        $clientData = $_POST['clientData'] ?? null;

        if ($email === '' || $credId === '') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Données manquantes']);
            exit;
        }

        $user = $userModel->findByEmail($email);
        if (!$user) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Utilisateur non trouvé']);
            exit;
        }

        $ok = $userModel->addWebAuthnCredential(authUtilisateurIdFromRow($user), $credId, null, $attestation, $clientData, 0, null);
        header('Content-Type: application/json');
        echo json_encode(['ok' => $ok]);
        exit;
    }

    if ($action === 'webauthn_auth_challenge') {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        if ($email === '') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Email requis']);
            exit;
        }
        $user = $userModel->findByEmail($email);
        if (!$user) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Utilisateur non trouvé']);
            exit;
        }

        $creds = $userModel->getUserCredentials(authUtilisateurIdFromRow($user));
        if ($creds === []) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Aucun appareil enregistré']);
            exit;
        }

        $allow = [];
        foreach ($creds as $c) {
            $allow[] = ['type' => 'public-key', 'id' => $c['credential_id']];
        }

        $challenge = bin2hex(random_bytes(32));
        $_SESSION['webauthn_auth_challenge_' . $email] = $challenge;

        $options = [
            'challenge' => base64_encode($challenge),
            'allowCredentials' => $allow,
            'timeout' => 60000,
            'userVerification' => 'preferred',
        ];
        header('Content-Type: application/json');
        echo json_encode($options);
        exit;
    }

    if ($action === 'webauthn_auth_response') {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $credId = (string) ($_POST['id'] ?? '');

        if ($email === '' || $credId === '') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Données manquantes']);
            exit;
        }

        $user = $userModel->findByEmail($email);
        if (!$user) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Utilisateur non trouvé']);
            exit;
        }

        $stored = $userModel->findCredentialById($credId);
        if (!$stored) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Appareil non enregistré']);
            exit;
        }

        $expectedChallenge = $_SESSION['webauthn_auth_challenge_' . $email] ?? null;
        if (!$expectedChallenge) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Challenge manquant']);
            exit;
        }

        authApplyLoginSession($user);

        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'register') {
        $prenom = trim((string) ($_POST['prenom'] ?? ''));
        $nom = trim((string) ($_POST['nom'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $role = trim((string) ($_POST['role'] ?? 'client'));
        $budget = isset($_POST['budget']) && $_POST['budget'] !== '' ? (float) $_POST['budget'] : 0.0;
        $description = trim((string) ($_POST['description'] ?? ''));
        $referralCode = trim((string) ($_POST['referral_code'] ?? ''));

        $errors = [];

        if ($prenom === '') {
            $errors[] = 'Prénom requis';
        }
        if ($nom === '') {
            $errors[] = 'Nom requis';
        }
        if ($email === '') {
            $errors[] = 'Email requis';
        }
        if ($password === '') {
            $errors[] = 'Mot de passe requis';
        } elseif (strlen($password) < 6) {
            $errors[] = '6 caractères minimum';
        }

        $allowedRoles = ['client', 'nutritionniste', 'fournisseur'];
        if (!in_array($role, $allowedRoles, true)) {
            $role = 'client';
        }

        if ($errors === []) {
            $pk = authUtilisateurPkColumn($pdo);
            $stmt = $pdo->prepare('SELECT `' . $pk . '` FROM utilisateur WHERE email = :email');
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email déjà utilisé';
            }
        }

        if ($errors !== []) {
            $_SESSION['errors'] = $errors;
            header('Location: ../FrontOffice/auth/register.php');
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $hasReferralCode = authColumnExists($pdo, 'utilisateur', 'referral_code');
        $hasProfilImage = authColumnExists($pdo, 'utilisateur', 'profil-image');
        $hasProfilePhoto = authColumnExists($pdo, 'utilisateur', 'profile_photo');

        $profilePhoto = null;
        if (($hasProfilImage || $hasProfilePhoto) && isset($_FILES['profile_photo']) && !empty($_FILES['profile_photo']['tmp_name'])) {
            $profilePhoto = authHandleProfilePhotoUpload($_FILES['profile_photo']);
        }

        $columns = ['prenom', 'nom', 'email', 'motDePasse', 'role'];
        $placeholders = [':prenom', ':nom', ':email', ':motDePasse', ':role'];
        $params = [
            'prenom' => $prenom,
            'nom' => $nom,
            'email' => $email,
            'motDePasse' => $hashedPassword,
            'role' => $role,
        ];

        if (authColumnExists($pdo, 'utilisateur', 'budget')) {
            $columns[] = 'budget';
            $placeholders[] = ':budget';
            $params['budget'] = $budget;
        }
        if (authColumnExists($pdo, 'utilisateur', 'description')) {
            $columns[] = 'description';
            $placeholders[] = ':description';
            $params['description'] = $description;
        }

        if ($hasReferralCode) {
            $columns[] = 'referral_code';
            $placeholders[] = ':referral_code';
            $params['referral_code'] = $referralCode;
        }

        if ($hasProfilImage && $profilePhoto) {
            $columns[] = '`profil-image`';
            $placeholders[] = ':profil_image';
            $params['profil_image'] = $profilePhoto;
        } elseif ($hasProfilePhoto && $profilePhoto) {
            $columns[] = 'profile_photo';
            $placeholders[] = ':profile_photo';
            $params['profile_photo'] = $profilePhoto;
        }

        $sql = 'INSERT INTO utilisateur (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute($params)) {
            $newUserId = (int) $pdo->lastInsertId();
            $userModel->ensureReferralCode($newUserId);
            if ($referralCode !== '') {
                $userModel->attachReferralByCode($newUserId, $referralCode);
            }

            $_SESSION['success'] = 'Compte créé avec succès !';
            $_SESSION['just_registered_email'] = $email;
            header('Location: ../FrontOffice/auth/login.php');
            exit;
        }

        $_SESSION['error'] = 'Erreur lors de la création';
        header('Location: ../FrontOffice/auth/register.php');
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'] ?? '/');
    }
    session_destroy();
    header('Location: ../FrontOffice/Home.php');
    exit;
}

header('Location: ../FrontOffice/auth/login.php');
exit;
