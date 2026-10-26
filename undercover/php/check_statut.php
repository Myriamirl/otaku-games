<?php
require_once __DIR__ . '/../../db_connect.php';
require_once __DIR__ . '/../../php/security.php';

// Nettoyage pour empêcher l'envoi de caractères indésirables avant le JSON
if (ob_get_length()) ob_clean();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['statut' => 'erreur']);
    exit();
}

$code_room = $_GET['code'] ?? '';

if (empty($code_room)) {
    echo json_encode(['statut' => 'erreur']);
    exit();
}

// Récupération de l'état de la room
$stmt = $pdo->prepare("SELECT id, statut, joueur_actuel_id, tour_actuel FROM partie_undercover WHERE code_room = ?");
$stmt->execute([$code_room]);
$partie = $stmt->fetch();

if (!$partie) {
    echo json_encode(['statut' => 'erreur']);
    exit();
}

//Nombre total de joueurs
$stmtNbJoueurs = $pdo->prepare("SELECT COUNT(*) FROM partie_joueur WHERE partie_id = ?");
$stmtNbJoueurs->execute([$partie['id']]);
$nb_joueurs = (int)$stmtNbJoueurs->fetchColumn();

//Nombre de joueurs encore vivants
$stmtNbVivants = $pdo->prepare("SELECT COUNT(*) FROM partie_joueur WHERE partie_id = ? AND vivant = 1");
$stmtNbVivants->execute([$partie['id']]);
$nb_vivants = (int)$stmtNbVivants->fetchColumn();

// Nombre total d'indices
$stmtNbIndices = $pdo->prepare("
    SELECT COUNT(i.id) 
    FROM indice i 
    JOIN partie_joueur pj ON i.partie_joueur_id = pj.id 
    WHERE pj.partie_id = ?
");
$stmtNbIndices->execute([$partie['id']]);
$nb_indices = (int)$stmtNbIndices->fetchColumn();

//Nombre total de votes enregistrés pour ce tour
$stmtNbVotes = $pdo->prepare("SELECT COUNT(*) FROM vote WHERE partie_id = ?");
$stmtNbVotes->execute([$partie['id']]);
$nb_votes = (int)$stmtNbVotes->fetchColumn();

echo json_encode([
    'statut'           => (string)$partie['statut'],
    'joueur_actuel_id' => (string)($partie['joueur_actuel_id'] ?? '0'),
    'tour_actuel'      => (string)($partie['tour_actuel'] ?? '1'),
    'nb_joueurs'       => $nb_joueurs,
    'nb_vivants'       => $nb_vivants,
    'nb_indices'       => $nb_indices,
    'nb_votes'         => $nb_votes
]);
exit();