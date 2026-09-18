<?php
session_start();
require_once __DIR__ . '/../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$user_id     = $_SESSION['user_id'];
$pseudo      = trim($_POST['pseudo'] ?? '');
$avatar      = trim($_POST['avatar'] ?? 'avatar1.jpg');
$nouveau_mdp = $_POST['nouveau_mdp'] ?? '';
$confirm_mdp = $_POST['confirm_mdp'] ?? '';

if (empty($pseudo)) {
    header('Location: ../profil.php?erreur=Le+pseudo+ne+peut+pas+etre+vide');
    exit();
}

// Vérifier si le pseudo est déjà pris par UN AUTRE utilisateur
$stmt = $pdo->prepare("SELECT id FROM UTILISATEUR WHERE pseudo = ? AND id != ?");
$stmt->execute([$pseudo, $user_id]);

if ($stmt->fetch()) {
    header('Location: ../profil.php?erreur=Ce+pseudo+est+deja+utilise');
    exit();
}

// Mettre à jour le pseudo et l'avatar
$stmtUpdate = $pdo->prepare("UPDATE UTILISATEUR SET pseudo = ?, avatar = ? WHERE id = ?");
$stmtUpdate->execute([$pseudo, $avatar, $user_id]);

$_SESSION['pseudo'] = $pseudo;
$_SESSION['avatar'] = $avatar;

// Mettre à jour le mot de passe si un nouveau est entré
if (!empty($nouveau_mdp)) {
    if ($nouveau_mdp !== $confirm_mdp) {
        header('Location: ../profil.php?erreur=Les+mots+de+passe+ne+correspondent+pas');
        exit();
    }

    $hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
    $stmtMdp = $pdo->prepare("UPDATE UTILISATEUR SET mot_de_passe = ? WHERE id = ?");
    $stmtMdp->execute([$hash, $user_id]);
}

header('Location: ../profil.php?succes=Profil+mis+a+jour+avec+succes');
exit();