<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$mode = $_POST['mode'] ?? 'Original';
$univers_id = intval($_POST['univers_id'] ?? 1);
$nb_tours = isset($_POST['nb_tours']) ? intval($_POST['nb_tours']) : null;
$tours_vote = isset($_POST['tours_vote']) ? intval($_POST['tours_vote']) : null;
$nb_undercover = isset($_POST['nb_undercover']) ? intval($_POST['nb_undercover']) : null;

if (!$nb_tours || $nb_tours < 2 || $nb_tours > 10 || !$tours_vote || !$nb_undercover) {
    header('Location: ../pages/undercover.php?erreur=Veuillez+remplir+tous+les+champs');
    exit();
}

function genererCodeRoom() {
    $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle($caracteres), 0, 6);
}

$code_room = genererCodeRoom();

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO partie_undercover (code_room, univers_id, mode, statut, nb_tours, tours_vote, nb_undercover) VALUES (?, ?, ?, 'attente', ?, ?, ?)");
    $stmt->execute([$code_room, $univers_id, $mode, $nb_tours, $tours_vote, $nb_undercover]);
    $partie_id = $pdo->lastInsertId();

    $stmtJoueur = $pdo->prepare("INSERT INTO partie_joueur (partie_id, utilisateur_id, vivant) VALUES (?, ?, 1)");
    $stmtJoueur->execute([$partie_id, $user_id]);

    $pdo->commit();

    header('Location: ../pages/lobby.php?code=' . $code_room);
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    error_log($e->getMessage());
    header('Location: ../pages/undercover.php?erreur=Impossible+de+creer+la+partie');
    exit();
}