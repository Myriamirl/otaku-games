<?php
require_once __DIR__ . '/../../db_connect.php';
require_once __DIR__ . '/../../php/security.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

$user_id    = $_SESSION['user_id'];
$action     = $_POST['action'] ?? '';
$csrf_token = $_POST['csrf_token'] ?? '';

// Contrôle de sécurité Anti-CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verify_csrf_token($csrf_token)) {
    header('Location: ../pages/undercover.php?erreur=Session+expir%C3%A9e');
    exit();
}

//------------------------------------------------------------------------------------------------------------------------------
// ==================================== CRÉER UN SALON =====================================================================
//------------------------------------------------------------------------------------------------------------------------------

if ($action === 'creer') {
    $univers_id    = intval($_POST['univers_id'] ?? 1);
    $nb_undercover = intval($_POST['nb_undercover'] ?? 1);
    $nb_tours      = intval($_POST['nb_tours'] ?? 3);
    $tours_vote    = intval($_POST['tours_vote'] ?? 1);

    if ($nb_tours < 2 || $nb_tours > 10 || $nb_undercover < 1) {
        header('Location: ../pages/undercover.php?erreur=Champs+invalides');
        exit();
    }

    $caracteres = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    $code_room  = substr(str_shuffle($caracteres), 0, 6);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO partie_undercover (code_room, univers_id, mode, statut, nb_tours, tours_vote, nb_undercover) VALUES (?, ?, 'Original', 'attente', ?, ?, ?)");
        $stmt->execute([$code_room, $univers_id, $nb_tours, $tours_vote, $nb_undercover]);
        $partie_id = $pdo->lastInsertId();

        $stmtJoueur = $pdo->prepare("INSERT INTO partie_joueur (partie_id, utilisateur_id, vivant) VALUES (?, ?, 1)");
        $stmtJoueur->execute([$partie_id, $user_id]);

        $pdo->commit();
        header('Location: ../pages/undercover.php?code=' . $code_room);
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        header('Location: ../pages/undercover.php?erreur=Erreur+de+creation');
        exit();
    }
}

//------------------------------------------------------------------------------------------------------------------------------
// ================================ REJOINDRE UN SALON ============================================================================
//------------------------------------------------------------------------------------------------------------------------------
if ($action === 'rejoindre') {
    $code_room = strtoupper(trim($_POST['code_room'] ?? ''));

    if (empty($code_room) || !preg_match('/^[A-Z0-9]{6}$/', $code_room)) {
        header('Location: ../pages/undercover.php?erreur=Code+invalide');
        exit();
    }

    $stmt = $pdo->prepare("SELECT id, statut FROM partie_undercover WHERE code_room = ?");
    $stmt->execute([$code_room]);
    $partie = $stmt->fetch();

    if (!$partie || $partie['statut'] !== 'attente') {
        header('Location: ../pages/undercover.php?erreur=Salon+introuvable+ou+deja+lance');
        exit();
    }

    $stmtCheck = $pdo->prepare("SELECT id FROM partie_joueur WHERE partie_id = ? AND utilisateur_id = ?");
    $stmtCheck->execute([$partie['id'], $user_id]);

    if (!$stmtCheck->fetch()) {
        $stmtJoin = $pdo->prepare("INSERT INTO partie_joueur (partie_id, utilisateur_id, vivant) VALUES (?, ?, 1)");
        $stmtJoin->execute([$partie['id'], $user_id]);
    }

    header('Location: ../pages/undercover.php?code=' . $code_room);
    exit();
}

//------------------------------------------------------------------------------------------------------------------------------
// ==================================== LANCER LA PARTIE ====================================================================
//------------------------------------------------------------------------------------------------------------------------------

if ($action === 'lancer') {
    $code_room = $_POST['code_room'] ?? '';

    $stmt = $pdo->prepare("SELECT id, univers_id, nb_undercover FROM partie_undercover WHERE code_room = ?");
    $stmt->execute([$code_room]);
    $partie = $stmt->fetch();

    if (!$partie) {
        header('Location: ../pages/undercover.php?erreur=Partie+introuvable');
        exit();
    }

    $stmtJoueurs = $pdo->prepare("SELECT id, utilisateur_id FROM partie_joueur WHERE partie_id = ? ORDER BY id ASC");
    $stmtJoueurs->execute([$partie['id']]);
    $joueurs = $stmtJoueurs->fetchAll();

    // SÉCURITÉ : Seul l'hôte (1er joueur du salon) lance la partie
    if (empty($joueurs) || $joueurs[0]['utilisateur_id'] != $user_id) {
        header('Location: ../pages/undercover.php?code=' . $code_room . '&erreur=Seul+l+hote+peut+lancer');
        exit();
    }

    $total_joueurs = count($joueurs);
    $nb_undercover = intval($partie['nb_undercover']);

    if ($total_joueurs <= $nb_undercover) {
        header('Location: ../pages/undercover.php?code=' . $code_room . '&erreur=Pas+assez+de+joueurs');
        exit();
    }

    // Sélection de la paire dans la BDD
    $stmtPaire = $pdo->prepare("SELECT personnage_1_id, personnage_2_id FROM paire_undercover WHERE univers_id = ? ORDER BY RAND() LIMIT 1");
    $stmtPaire->execute([$partie['univers_id']]);
    $paire = $stmtPaire->fetch();

    if (!$paire) {
        header('Location: ../pages/undercover.php?code=' . $code_room . '&erreur=Aucune+paire+disponible');
        exit();
    }

    $id_mot_civil = (rand(0, 1) === 0) ? $paire['personnage_1_id'] : $paire['personnage_2_id'];
    $id_mot_undercover = ($id_mot_civil === $paire['personnage_1_id']) ? $paire['personnage_2_id'] : $paire['personnage_1_id'];

    shuffle($joueurs);

    try {
        $pdo->beginTransaction();

        foreach ($joueurs as $index => $j) {
            $role = ($index < $nb_undercover) ? 'undercover' : 'civil';
            $mot_id = ($role === 'undercover') ? $id_mot_undercover : $id_mot_civil;

            $stmtRole = $pdo->prepare("UPDATE partie_joueur SET role = ?, personnage_id = ? WHERE id = ?");
            $stmtRole->execute([$role, $mot_id, $j['id']]);
        }

        $stmtStatus = $pdo->prepare("UPDATE partie_undercover SET statut = 'en_cours', tour_actuel = 1, joueur_actuel_id = ? WHERE id = ?");
        $stmtStatus->execute([$joueurs[0]['id'], $partie['id']]);

        $pdo->commit();
        header('Location: ../pages/undercover.php?code=' . $code_room);
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        header('Location: ../pages/undercover.php?code=' . $code_room . '&erreur=Erreur+de+lancement');
        exit();
    }
}

//------------------------------------------------------------------------------------------------------------------------------
// ==================================== POSTER UN INDICE ====================================================================
//------------------------------------------------------------------------------------------------------------------------------

if ($action === 'indice') {
    $code_room = $_POST['code_room'] ?? '';
    $contenu   = trim(htmlspecialchars(strip_tags($_POST['contenu'] ?? ''), ENT_QUOTES, 'UTF-8'));

    if (empty($code_room) || empty($contenu) || mb_strlen($contenu) > 50) {
        header('Location: ../pages/undercover.php?code=' . $code_room . '&erreur=Indice+invalide');
        exit();
    }

    $stmt = $pdo->prepare("SELECT id, tour_actuel, joueur_actuel_id FROM partie_undercover WHERE code_room = ?");
    $stmt->execute([$code_room]);
    $partie = $stmt->fetch();

    $stmtJoueur = $pdo->prepare("SELECT id, vivant FROM partie_joueur WHERE partie_id = ? AND utilisateur_id = ?");
    $stmtJoueur->execute([$partie['id'], $user_id]);
    $joueur = $stmtJoueur->fetch();

    if (!$joueur || !$joueur['vivant'] || $partie['joueur_actuel_id'] != $joueur['id']) {
        header('Location: ../pages/undercover.php?code=' . $code_room . '&erreur=Pas+ton+tour');
        exit();
    }

    try {
        $pdo->beginTransaction();

        $stmtInsert = $pdo->prepare("INSERT INTO indice (partie_joueur_id, contenu, tour_numero) VALUES (?, ?, ?)");
        $stmtInsert->execute([$joueur['id'], $contenu, $partie['tour_actuel']]);

        $stmtVivants = $pdo->prepare("SELECT id FROM partie_joueur WHERE partie_id = ? AND vivant = 1 ORDER BY id ASC");
        $stmtVivants->execute([$partie['id']]);
        $vivants = $stmtVivants->fetchAll(PDO::FETCH_COLUMN);

        $index_actuel = array_search($joueur['id'], $vivants);

        if ($index_actuel !== false && isset($vivants[$index_actuel + 1])) {
            $prochain_joueur_id = $vivants[$index_actuel + 1];
            $pdo->prepare("UPDATE partie_undercover SET joueur_actuel_id = ? WHERE id = ?")->execute([$prochain_joueur_id, $partie['id']]);
        } else {
            $premier_joueur_id = $vivants[0];
            $pdo->prepare("UPDATE partie_undercover SET tour_actuel = tour_actuel + 1, joueur_actuel_id = ? WHERE id = ?")->execute([$premier_joueur_id, $partie['id']]);
        }

        $pdo->commit();
        header('Location: ../pages/undercover.php?code=' . $code_room);
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        header('Location: ../pages/undercover.php?code=' . $code_room . '&erreur=Erreur+publication');
        exit();
    }
}

//------------------------------------------------------------------------------------------------------------------------------
// ==================================== VOTER ====================================================================
//------------------------------------------------------------------------------------------------------------------------------

if ($action === 'voter') {
    $code_room = $_POST['code_room'] ?? '';
    $id_cible  = intval($_POST['id_cible'] ?? 0);

    $stmt = $pdo->prepare("SELECT id FROM partie_undercover WHERE code_room = ?");
    $stmt->execute([$code_room]);
    $partie = $stmt->fetch();

    $stmtVotant = $pdo->prepare("SELECT id, vivant FROM partie_joueur WHERE partie_id = ? AND utilisateur_id = ?");
    $stmtVotant->execute([$partie['id'], $user_id]);
    $votant = $stmtVotant->fetch();

    if (!$votant || empty($votant['vivant'])) {
        header('Location: ../pages/undercover.php?code=' . $code_room . '&erreur=Impossible+de+voter');
        exit();
    }

    try {
        $pdo->beginTransaction();

        $stmtVote = $pdo->prepare("INSERT INTO vote (partie_id, id_votant, id_cible) VALUES (?, ?, ?)");
        $stmtVote->execute([$partie['id'], $votant['id'], $id_cible]);

        $stmtTotalVivants = $pdo->prepare("SELECT COUNT(*) FROM partie_joueur WHERE partie_id = ? AND vivant = 1");
        $stmtTotalVivants->execute([$partie['id']]);
        $total_vivants = (int)$stmtTotalVivants->fetchColumn();

        $stmtTotalVotes = $pdo->prepare("SELECT COUNT(*) FROM vote WHERE partie_id = ?");
        $stmtTotalVotes->execute([$partie['id']]);
        $total_votes = (int)$stmtTotalVotes->fetchColumn();

        if ($total_votes >= $total_vivants) {
            $stmtResult = $pdo->prepare("SELECT id_cible, COUNT(*) as nb_voix FROM vote WHERE partie_id = ? GROUP BY id_cible ORDER BY nb_voix DESC LIMIT 1");
            $stmtResult->execute([$partie['id']]);
            $elimine = $stmtResult->fetch();

            if ($elimine) {
                $pdo->prepare("UPDATE partie_joueur SET vivant = 0 WHERE id = ?")->execute([$elimine['id_cible']]);
            }

            $pdo->prepare("DELETE FROM vote WHERE partie_id = ?")->execute([$partie['id']]);

            $stmtRoles = $pdo->prepare("SELECT role, COUNT(*) as nb FROM partie_joueur WHERE partie_id = ? AND vivant = 1 GROUP BY role");
            $stmtRoles->execute([$partie['id']]);
            $roles_count = $stmtRoles->fetchAll(PDO::FETCH_KEY_PAIR);

            $undercovers = (int)($roles_count['undercover'] ?? 0);
            $civils      = (int)($roles_count['civil'] ?? 0);

            if ($undercovers === 0 || $undercovers >= $civils) {
                $pdo->prepare("UPDATE partie_undercover SET statut = 'termine' WHERE id = ?")->execute([$partie['id']]);
            }
        }

        $pdo->commit();
        header('Location: ../pages/undercover.php?code=' . $code_room);
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        header('Location: ../pages/undercover.php?code=' . $code_room . '&erreur=Erreur+de+vote');
        exit();
    }
}

header('Location: ../pages/undercover.php');
exit();