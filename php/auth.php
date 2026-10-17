<?php
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/security.php';

$action     = $_POST['action'] ?? '';
$csrf_token = $_POST['csrf_token'] ?? '';

// Contrôle CSRF Global
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verify_csrf_token($csrf_token)) {
    header('Location: ../index.php?erreur=erreur_serveur');
    exit();
}

// ----------------------------------------- TRAITEMENT INSCRIPTION -----------------------------------------------------------

if ($action === 'inscription') {
    $nom            = trim($_POST['nom'] ?? '');
    $prenom         = trim($_POST['prenom'] ?? '');
    $email          = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $date_naissance = $_POST['date_naissance'] ?? '';
    $pseudo         = trim($_POST['pseudo'] ?? '');
    $mot_de_passe   = $_POST['mot_de_passe'] ?? '';
    $confirmation   = $_POST['confirmation'] ?? '';
    $avatar_saisie  = trim($_POST['avatar'] ?? ''); 

    // Contrôle de présence de TOUS les champs (avatar inclus)
    if (!$nom || !$prenom || !$email || !$date_naissance || !$pseudo || !$mot_de_passe || !$confirmation || empty($avatar_saisie)) {
        header('Location: ../index.php?erreur=champs_vides');
        exit();
    }

    // Validation date de naissance
    $d = DateTime::createFromFormat('Y-m-d', $date_naissance);
    if (!$d || $d->format('Y-m-d') !== $date_naissance) {
        header('Location: ../index.php?erreur=champs_vides');
        exit();
    }

    //  Validation pseudo (Regex Unicode alignée sur app.js)
    if (!preg_match('/^[\p{L}0-9 _-]{2,20}$/u', $pseudo)) {
        header('Location: ../index.php?erreur=pseudo_longueur');
        exit();
    }

    // Validation mot de passe (8 car. min, 1 majuscule, 1 chiffre)
    if (strlen($mot_de_passe) < 8 || !preg_match('/[A-Z]/', $mot_de_passe) || !preg_match('/[0-9]/', $mot_de_passe)) {
        header('Location: ../index.php?erreur=mdp_court');
        exit();
    }

    if ($mot_de_passe !== $confirmation) {
        header('Location: ../index.php?erreur=mdp_mismatch');
        exit();
    }

    // Validation de l'avatar sur Liste Blanche (Whitelisting contre Path Traversal)
    $dossier = __DIR__ . '/../assets/logo_avatar/';
    $avatars_fichiers = glob($dossier . 'avatar*.{jpg,jpeg,png,webp}', GLOB_BRACE);
    $avatars_valides = array_map('basename', $avatars_fichiers ?: []);

    if (!in_array($avatar_saisie, $avatars_valides, true)) {
        header('Location: ../index.php?erreur=champs_vides');
        exit();
    }
    $avatar = $avatar_saisie;

    // Unicité du pseudo ou de l'email
    $stmt = $pdo->prepare("SELECT id FROM utilisateur WHERE pseudo = ? OR email = ?");
    $stmt->execute([$pseudo, $email]);
    if ($stmt->fetch()) {
        header('Location: ../index.php?erreur=pseudo_pris');
        exit();
    }

    // Hachage du mot de passe
    $hash_algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
    $hash = password_hash($mot_de_passe, $hash_algo);

    try {
        $pdo->beginTransaction();

        $stmtInsert = $pdo->prepare("INSERT INTO utilisateur (pseudo, email, nom, prenom, date_naissance, mot_de_passe, avatar) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtInsert->execute([$pseudo, $email, $nom, $prenom, $date_naissance, $hash, $avatar]);
        $utilisateur_id = $pdo->lastInsertId();

        $stmtLeaderboard = $pdo->prepare("INSERT INTO leaderboard (utilisateur_id) VALUES (?)");
        $stmtLeaderboard->execute([$utilisateur_id]);

        $pdo->commit();
        header('Location: ../index.php?succes=inscr_ok');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        header('Location: ../index.php?erreur=erreur_serveur');
        exit();
    }
}

// ----------------------------------------- TRAITEMENT CONNEXION -----------------------------------------------------------

if ($action === 'connexion') {
    // 1. Vérification du Rate Limit par IP
    if (!check_ip_rate_limit($pdo, 'login_attempt', 10, 900)) {
        header('Location: ../index.php?erreur=trop_de_tentatives');
        exit();
    }

    $pseudo       = trim($_POST['pseudo'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    if (empty($pseudo) || empty($mot_de_passe)) {
        header('Location: ../index.php?erreur=champs_vides');
        exit();
    }

    //Recherche de l'utilisateur
    $stmt = $pdo->prepare("SELECT id, pseudo, mot_de_passe, avatar, tentatives_echecs, bloque_jusqua, compte_suspendu FROM utilisateur WHERE pseudo = ?");
    $stmt->execute([$pseudo]);
    $user = $stmt->fetch();

    if ($user) {
        //Vérifier si le compte est suspendu définitivement
        if ($user['compte_suspendu'] == 1) {
            header('Location: ../index.php?erreur=erreur_serveur');
            exit();
        }

        //Vérifier si le compte est temporairement bloqué
        if (!empty($user['bloque_jusqua']) && strtotime($user['bloque_jusqua']) > time()) {
            header('Location: ../index.php?erreur=trop_de_tentatives');
            exit();
        }

        // Vérification du mot de passe
        if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
            // Reussite : Réinitialisation des compteurs
            $stmtReset = $pdo->prepare("UPDATE utilisateur SET tentatives_echecs = 0, bloque_jusqua = NULL WHERE id = ?");
            $stmtReset->execute([$user['id']]);

            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['pseudo']  = $user['pseudo'];
            $_SESSION['avatar']  = $user['avatar'];

            header('Location: ../index.php');
            exit();
        } else {
            // Echec : Incrémentation des tentatives
            $echecs = $user['tentatives_echecs'] + 1;

            if ($echecs >= 10) {
                // 10 échecs -> Suspension permanente du compte
                $stmtLock = $pdo->prepare("UPDATE utilisateur SET tentatives_echecs = ?, compte_suspendu = 1 WHERE id = ?");
                $stmtLock->execute([$echecs, $user['id']]);
            } else if ($echecs >= 5) {
                // 5 échecs -> Blocage temporaire de 15 minutes
                $bloque_jusqua = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                $stmtLock = $pdo->prepare("UPDATE utilisateur SET tentatives_echecs = ?, bloque_jusqua = ? WHERE id = ?");
                $stmtLock->execute([$echecs, $bloque_jusqua, $user['id']]);
            } else {
                $stmtLock = $pdo->prepare("UPDATE utilisateur SET tentatives_echecs = ? WHERE id = ?");
                $stmtLock->execute([$echecs, $user['id']]);
            }

            header('Location: ../index.php?erreur=identifiants_inc');
            exit();
        }
    } else {
        header('Location: ../index.php?erreur=identifiants_inc');
        exit();
    }
}
header('Location: ../index.php');
exit();