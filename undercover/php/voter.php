<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$code_room = $_POST['code_room'] ?? '';
$id_cible = intval($_POST['id_cible'] ?? 0);

if (empty($code_room) || $id_cible <= 0) {
    header('Location: ../pages/undercover.php?erreur=Action+invalide');
    exit();
}

// 1. Récupération de la partie
$stmt = $pdo->prepare("SELECT id, statut FROM partie_undercover WHERE code_room = ?");
$stmt->execute([$code_room]);
$partie = $stmt->fetch();

if (!$partie || $partie['statut'] !== 'en_cours') {
    header('Location: ../pages/lobby.php?code=' . $code_room . '&erreur=Partie+non+active');
    exit();
}

// 2. Récupération sécurisée du joueur votant
$stmtVotant = $pdo->prepare("SELECT id, vivant FROM partie_joueur WHERE partie_id = ? AND utilisateur_id = ?");
$stmtVotant->execute([$partie['id'], $user_id]);
$votant = $stmtVotant->fetch();

// Vérification : Le joueur existe-t-il et est-il en vie ?
if (!$votant || empty($votant['vivant'])) {
    header('Location: ../pages/lobby.php?code=' . $code_room . '&erreur=Impossible+de+voter');
    exit();
}

try {
    $pdo->beginTransaction();

    // 3. Enregistrement du vote
    $stmtVote = $pdo->prepare("INSERT INTO vote (partie_id, id_votant, id_cible) VALUES (?, ?, ?)");
    $stmtVote->execute([$partie['id'], $votant['id'], $id_cible]);

    // 4. Verification du nombre de votes reçus
    $stmtTotalVivants = $pdo->prepare("SELECT COUNT(*) FROM partie_joueur WHERE partie_id = ? AND vivant = 1");
    $stmtTotalVivants->execute([$partie['id']]);
    $total_vivants = (int)$stmtTotalVivants->fetchColumn();

    $stmtTotalVotes = $pdo->prepare("SELECT COUNT(*) FROM vote WHERE partie_id = ?");
    $stmtTotalVotes->execute([$partie['id']]);
    $total_votes = (int)$stmtTotalVotes->fetchColumn();

    // 5. Dépouillement si tout le monde a voté
    if ($total_votes >= $total_vivants) {
        
        $stmtResult = $pdo->prepare("
            SELECT id_cible, COUNT(*) as nb_voix 
            FROM vote 
            WHERE partie_id = ? 
            GROUP BY id_cible 
            ORDER BY nb_voix DESC 
            LIMIT 1
        ");
        $stmtResult->execute([$partie['id']]);
        $elimine = $stmtResult->fetch();

        if ($elimine) {
            $stmtElim = $pdo->prepare("UPDATE partie_joueur SET vivant = 0 WHERE id = ?");
            $stmtElim->execute([$elimine['id_cible']]);
        }

        // Nettoyage des votes
        $stmtClean = $pdo->prepare("DELETE FROM vote WHERE partie_id = ?");
        $stmtClean->execute([$partie['id']]);

        // Vérification des conditions de fin
        $stmtRoles = $pdo->prepare("
            SELECT role, COUNT(*) as nb 
            FROM partie_joueur 
            WHERE partie_id = ? AND vivant = 1 
            GROUP BY role
        ");
        $stmtRoles->execute([$partie['id']]);
        $roles_count = $stmtRoles->fetchAll(PDO::FETCH_KEY_PAIR);

        $undercovers = (int)($roles_count['undercover'] ?? 0);
        $civils = (int)($roles_count['civil'] ?? 0);

        if ($undercovers === 0 || $undercovers >= $civils) {
            $pdo->prepare("UPDATE partie_undercover SET statut = 'termine' WHERE id = ?")->execute([$partie['id']]);
        }
    }

    $pdo->commit();
    header('Location: ../pages/lobby.php?code=' . $code_room);
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    error_log($e->getMessage());
    header('Location: ../pages/lobby.php?code=' . $code_room . '&erreur=Erreur+de+vote');
    exit();
}