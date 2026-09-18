<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$code_room = strtoupper(trim($_POST['code_room'] ?? ''));

if (empty($code_room)) {
    header('Location: ../pages/undercover.php?erreur=Veuillez+entrer+un+code');
    exit();
}

// 1. Chercher la partie avec le code
$stmt = $pdo->prepare("SELECT id, statut FROM partie_undercover WHERE code_room = ?");
$stmt->execute([$code_room]);
$partie = $stmt->fetch();

if (!$partie) {
    header('Location: ../pages/undercover.php?erreur=Code+de+salon+invalide');
    exit();
}

if ($partie['statut'] !== 'attente') {
    header('Location: ../pages/undercover.php?erreur=La+partie+a+deja+commence');
    exit();
}

// 2. Vérifier si le joueur est déjà inscrit dans la partie
$stmtCheck = $pdo->prepare("SELECT id FROM partie_joueur WHERE partie_id = ? AND utilisateur_id = ?");
$stmtCheck->execute([$partie['id'], $user_id]);

if (!$stmtCheck->fetch()) {
    // Si non inscrit, on l'ajoute
    $stmtJoin = $pdo->prepare("INSERT INTO partie_joueur (partie_id, utilisateur_id, vivant) VALUES (?, ?, 1)");
    $stmtJoin->execute([$partie['id'], $user_id]);
}

header('Location: ../pages/lobby.php?code=' . $code_room);
exit();