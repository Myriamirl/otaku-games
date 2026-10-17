<?php
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/security.php';

// Vérification de la connexion de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Contrôle Anti-CSRF
$csrf_token = $_POST['csrf_token'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verify_csrf_token($csrf_token)) {
    header('Location: ../profil.php?erreur=erreur_serveur');
    exit();
}

$user_id        = $_SESSION['user_id'];
$pseudo         = trim($_POST['pseudo'] ?? '');
$nom            = trim($_POST['nom'] ?? '');
$prenom         = trim($_POST['prenom'] ?? '');
$email          = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$date_naissance = $_POST['date_naissance'] ?? '';
$avatar_saisie  = trim($_POST['avatar'] ?? '');
$nouveau_mdp    = $_POST['nouveau_mdp'] ?? '';
$confirm_mdp    = $_POST['confirm_mdp'] ?? '';

// Validation de présence de tous les champs obligatoires (avatar inclus)
if (!$pseudo || !$nom || !$prenom || !$email || !$date_naissance || empty($avatar_saisie)) {
    header('Location: ../profil.php?erreur=champs_vides');
    exit();
}

// Validation du format du pseudo (Regex Unicode 2 à 20 car.)
if (!preg_match('/^[\p{L}0-9 _-]{2,20}$/u', $pseudo)) {
    header('Location: ../profil.php?erreur=pseudo_longueur');
    exit();
}

//Whitelisting / Liste blanche sur l'avatar (Sécurité contre le Path Traversal)
$dossier = __DIR__ . '/../assets/logo_avatar/';
$avatars_fichiers = glob($dossier . 'avatar*.{jpg,jpeg,png,webp}', GLOB_BRACE);
$avatars_valides = array_map('basename', $avatars_fichiers ?: []);

if (!in_array($avatar_saisie, $avatars_valides, true)) {
    header('Location: ../profil.php?erreur=champs_vides');
    exit();
}
$avatar = $avatar_saisie;

// Vérification si le pseudo ou l'email est déjà utilisé par UN AUTRE compte
$stmt = $pdo->prepare("SELECT id FROM utilisateur WHERE (pseudo = ? OR email = ?) AND id != ?");
$stmt->execute([$pseudo, $email, $user_id]);
if ($stmt->fetch()) {
    header('Location: ../profil.php?erreur=pseudo_pris');
    exit();
}

//Mise à jour des informations personnelles dans la base de données
$stmtUpdate = $pdo->prepare("UPDATE utilisateur SET pseudo = ?, email = ?, nom = ?, prenom = ?, date_naissance = ?, avatar = ? WHERE id = ?");
$stmtUpdate->execute([$pseudo, $email, $nom, $prenom, $date_naissance, $avatar, $user_id]);

$_SESSION['pseudo'] = $pseudo;
$_SESSION['avatar'] = $avatar;

//Modification optionnelle du mot de passe si rempli
if (!empty($nouveau_mdp)) {
    if (strlen($nouveau_mdp) < 8 || !preg_match('/[A-Z]/', $nouveau_mdp) || !preg_match('/[0-9]/', $nouveau_mdp)) {
        header('Location: ../profil.php?erreur=mdp_court');
        exit();
    }

    if ($nouveau_mdp !== $confirm_mdp) {
        header('Location: ../profil.php?erreur=mdp_mismatch');
        exit();
    }

    $hash_algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
    $hash = password_hash($nouveau_mdp, $hash_algo);

    $stmtMdp = $pdo->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id = ?");
    $stmtMdp->execute([$hash, $user_id]);
}

header('Location: ../profil.php?succes=update_ok');
exit();