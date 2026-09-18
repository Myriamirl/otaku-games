<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

$code_room = $_POST['code_room'] ?? '';

// 1. Récupération des infos de la partie
$stmt = $pdo->prepare("SELECT id, univers_id, nb_undercover FROM partie_undercover WHERE code_room = ?");
$stmt->execute([$code_room]);
$partie = $stmt->fetch();

if (!$partie) {
    header('Location: ../pages/undercover.php?erreur=Partie+introuvable');
    exit();
}

// 2. Récupération des joueurs
$stmtJoueurs = $pdo->prepare("SELECT id FROM partie_joueur WHERE partie_id = ? ORDER BY id ASC");
$stmtJoueurs->execute([$partie['id']]);
$joueurs = $stmtJoueurs->fetchAll();

$total_joueurs = count($joueurs);
$nb_undercover = intval($partie['nb_undercover'] ?? 1);

// Sécurité : il doit y avoir au moins 1 civil de plus que d'undercovers
if ($total_joueurs <= $nb_undercover) {
    header('Location: ../pages/lobby.php?code=' . $code_room . '&erreur=Pas+assez+de+joueurs+pour+' . $nb_undercover . '+Undercover(s)');
    exit();
}

// 3. Tirage aléatoire de 2 mots DISTINCTS (1 pour les Civils, 1 pour les Undercovers)
$stmtPersos = $pdo->prepare("SELECT id FROM personnage WHERE univers_id = ? ORDER BY RAND() LIMIT 2");
$stmtPersos->execute([$partie['univers_id']]);
$persos = $stmtPersos->fetchAll();

// Sécurité : S'il y a moins de 2 personnages dans cet univers en BDD
if (count($persos) < 2) {
    header('Location: ../pages/lobby.php?code=' . $code_room . '&erreur=Pas assez de personnages dans cet univers pour lancer la partie');
    exit();
}

$id_mot_civil = $persos[0]['id'];
$id_mot_undercover = $persos[1]['id'];

// 4. Mélange et attribution des rôles selon nb_undercover
shuffle($joueurs);

try {
    $pdo->beginTransaction();

    foreach ($joueurs as $index => $j) {
        if ($index < $nb_undercover) {
            $role = 'undercover';
            $mot_id = $id_mot_undercover;
        } else {
            $role = 'civil';
            $mot_id = $id_mot_civil;
        }

        $stmtRole = $pdo->prepare("UPDATE partie_joueur SET role = ?, personnage_id = ? WHERE id = ?");
        $stmtRole->execute([$role, $mot_id, $j['id']]);
    }

    $premier_joueur_id = $joueurs[0]['id']; 

    $stmtStatus = $pdo->prepare("
        UPDATE partie_undercover 
        SET statut = 'en_cours', tour_actuel = 1, joueur_actuel_id = ? 
        WHERE id = ?
    ");
    $stmtStatus->execute([$premier_joueur_id, $partie['id']]);
    
    $pdo->commit();

    header('Location: ../pages/lobby.php?code=' . $code_room);
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    error_log($e->getMessage());
    header('Location: ../pages/lobby.php?code=' . $code_room . '&erreur=Erreur+lancement');
    exit();
}