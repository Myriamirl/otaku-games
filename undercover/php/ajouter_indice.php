<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$code_room = $_POST['code_room'] ?? '';
$contenu = trim($_POST['contenu'] ?? '');

if (empty($code_room) || empty($contenu)) {
    header('Location: ../pages/lobby.php?code=' . $code_room . '&erreur=Indice+vide');
    exit();
}

// 1. Récupération de la partie
$stmt = $pdo->prepare("SELECT id, tour_actuel, joueur_actuel_id FROM partie_undercover WHERE code_room = ?");
$stmt->execute([$code_room]);
$partie = $stmt->fetch();

if (!$partie) {
    header('Location: ../pages/lobby.php?code=' . $code_room);
    exit();
}

$stmtJoueur = $pdo->prepare("SELECT id, vivant FROM partie_joueur WHERE partie_id = ? AND utilisateur_id = ?");
$stmtJoueur->execute([$partie['id'], $user_id]);
$joueur = $stmtJoueur->fetch();

// Sécurité : Vérification du tour et du statut vivant
if (!$joueur || !$joueur['vivant'] || $partie['joueur_actuel_id'] != $joueur['id']) {
    header('Location: ../pages/lobby.php?code=' . $code_room . '&erreur=Ce+nest+pas+ton+tour');
    exit();
}

try {
    $pdo->beginTransaction();

    // 2. Enregistrement de l'indice
    $stmtInsert = $pdo->prepare("INSERT INTO indice (partie_joueur_id, contenu, tour_numero) VALUES (?, ?, ?)");
    $stmtInsert->execute([$joueur['id'], $contenu, $partie['tour_actuel']]);

    // 3. Récupération de tous les joueurs VIVANTS dans l'ordre d'inscription
    $stmtVivants = $pdo->prepare("SELECT id FROM partie_joueur WHERE partie_id = ? AND vivant = 1 ORDER BY id ASC");
    $stmtVivants->execute([$partie['id']]);
    $vivants = $stmtVivants->fetchAll(PDO::FETCH_COLUMN);

    // 4. Passage au joueur suivant
    $index_actuel = array_search($joueur['id'], $vivants);

    if ($index_actuel !== false && isset($vivants[$index_actuel + 1])) {
        // Il reste des joueurs dans le tour actuel : on passe au suivant SANS changer le numéro de tour
        $prochain_joueur_id = $vivants[$index_actuel + 1];
        $pdo->prepare("UPDATE partie_undercover SET joueur_actuel_id = ? WHERE id = ?")
            ->execute([$prochain_joueur_id, $partie['id']]);
    } else {
        // TOUS les joueurs ont joué : la boucle est terminée !
        // On repasse au 1er joueur ET ON INCRÉMENTE le numéro de tour
        $premier_joueur_id = $vivants[0];
        $pdo->prepare("UPDATE partie_undercover SET tour_actuel = tour_actuel + 1, joueur_actuel_id = ? WHERE id = ?")
            ->execute([$premier_joueur_id, $partie['id']]);
    }

    $pdo->commit();
    header('Location: ../pages/lobby.php?code=' . $code_room);
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    error_log($e->getMessage());
    header('Location: ../pages/lobby.php?code=' . $code_room . '&erreur=Erreur+publication');
    exit();
}