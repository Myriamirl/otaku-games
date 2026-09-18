<?php
session_start();
require_once __DIR__ . '/../db_connect.php';

$action = $_POST['action'] ?? '';

// --- TRAITEMENT INSCRIPTION ---
if ($action === 'inscription') {
    $pseudo       = trim($_POST['pseudo'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';
    $avatar       = trim($_POST['avatar'] ?? 'avatar1.jpg');

    if (empty($pseudo) || empty($mot_de_passe) || empty($confirmation)) {
        header('Location: ../index.php?erreur=Veuillez+remplir+tous+les+champs');
        exit();
    }

    if ($mot_de_passe !== $confirmation) {
        header('Location: ../index.php?erreur=Les+mots+de+passe+ne+correspondent+pas');
        exit();
    }

    if (strlen($pseudo) < 3 || strlen($pseudo) > 20) {
        header('Location: ../index.php?erreur=Le+pseudo+doit+faire+entre+3+et+20+caracteres');
        exit();
    }

    $stmt = $pdo->prepare("SELECT id FROM UTILISATEUR WHERE pseudo = ?");
    $stmt->execute([$pseudo]);

    if ($stmt->fetch()) {
        header('Location: ../index.php?erreur=Ce+pseudo+est+deja+pris');
        exit();
    }

    $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        $stmtInsert = $pdo->prepare("INSERT INTO UTILISATEUR (pseudo, mot_de_passe, avatar) VALUES (?, ?, ?)");
        $stmtInsert->execute([$pseudo, $hash, $avatar]);
        $utilisateur_id = $pdo->lastInsertId();

        $stmtLeaderboard = $pdo->prepare("INSERT INTO LEADERBOARD (utilisateur_id) VALUES (?)");
        $stmtLeaderboard->execute([$utilisateur_id]);

        $pdo->commit();
        header('Location: ../index.php?succes=Compte+cree+avec+succes+!+Vous+pouvez+vous+connecter');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        header('Location: ../index.php?erreur=Une+erreur+est+survenue+lors+de+l+inscription');
        exit();
    }
}

// --- TRAITEMENT CONNEXION ---
if ($action === 'connexion') {
    $pseudo       = trim($_POST['pseudo'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    if (empty($pseudo) || empty($mot_de_passe)) {
        header('Location: ../index.php?erreur=Veuillez+saisir+votre+pseudo+et+mot+de+passe');
        exit();
    }

    $stmt = $pdo->prepare("SELECT id, pseudo, mot_de_passe, avatar FROM UTILISATEUR WHERE pseudo = ?");
    $stmt->execute([$pseudo]);
    $user = $stmt->fetch();

    if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['pseudo']  = $user['pseudo'];
        $_SESSION['avatar']  = $user['avatar'];

        header('Location: ../index.php');
        exit();
    } else {
        header('Location: ../index.php?erreur=Identifiants+incorrects');
        exit();
    }
}

header('Location: ../index.php');
exit();