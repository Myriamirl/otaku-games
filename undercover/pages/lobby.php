<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

$code_room = $_GET['code'] ?? '';
$user_id = $_SESSION['user_id'];

// 1. Récupération des données de la partie
$stmt = $pdo->prepare("
    SELECT pu.id, pu.code_room, pu.statut, pu.mode, pu.nb_tours, pu.tours_vote, pu.tour_actuel, pu.joueur_actuel_id, u.nom AS univers_nom
    FROM partie_undercover pu
    JOIN univers u ON pu.univers_id = u.id
    WHERE pu.code_room = ?
");
$stmt->execute([$code_room]);
$partie = $stmt->fetch();

if (!$partie) {
    header('Location: undercover.php?erreur=Salon+inexistant');
    exit();
}

// ENDPOINT AJAX INTÉGRÉ
if (isset($_GET['action']) && $_GET['action'] === 'check_statut') {
    header('Content-Type: application/json');
    echo json_encode([
        'statut' => $partie['statut'],
        'joueur_actuel_id' => $partie['joueur_actuel_id'],
        'tour_actuel' => $partie['tour_actuel']
    ]);
    exit();
}

// 2. Récupération du mot secret si la partie est en cours
$mon_mot = null;
if ($partie['statut'] === 'en_cours') {
    $stmtMot = $pdo->prepare("
        SELECT p.nom 
        FROM partie_joueur pj 
        LEFT JOIN personnage p ON pj.personnage_id = p.id 
        WHERE pj.partie_id = ? AND pj.utilisateur_id = ?
    ");
    $stmtMot->execute([$partie['id'], $user_id]);
    $res = $stmtMot->fetch();
    $mon_mot = $res['nom'] ?? 'Aucun mot';
}

// 3. Récupération des joueurs
$stmtJoueurs = $pdo->prepare("
    SELECT u.id, u.pseudo, u.avatar, pj.id AS partie_joueur_id, pj.vivant
    FROM partie_joueur pj
    JOIN utilisateur u ON pj.utilisateur_id = u.id
    WHERE pj.partie_id = ?
    ORDER BY pj.id ASC
");
$stmtJoueurs->execute([$partie['id']]);
$joueurs = $stmtJoueurs->fetchAll();

$hote_id = $joueurs[0]['id'] ?? 0;
$est_hote = ($hote_id == $user_id);

$mon_partie_joueur_id = null;
$mon_jeu_vivant = false;
$pseudo_joueur_actuel = 'Inconnu';

foreach ($joueurs as $j) {
    if ($j['id'] == $user_id) {
        $mon_partie_joueur_id = $j['partie_joueur_id'];
        $mon_jeu_vivant = (bool)$j['vivant'];
    }
    if ($j['partie_joueur_id'] == $partie['joueur_actuel_id']) {
        $pseudo_joueur_actuel = $j['pseudo'];
    }
}

$est_mon_tour = ($mon_partie_joueur_id == $partie['joueur_actuel_id']);

// 4. Récupération des indices
$indices_par_joueur = [];
if ($partie['statut'] === 'en_cours') {
    $stmtIndices = $pdo->prepare("
        SELECT i.partie_joueur_id, i.contenu 
        FROM indice i
        JOIN partie_joueur pj ON i.partie_joueur_id = pj.id
        WHERE pj.partie_id = ? 
        ORDER BY i.id ASC
    ");
    $stmtIndices->execute([$partie['id']]);
    $liste_indices = $stmtIndices->fetchAll();
    foreach ($liste_indices as $ind) {
        $indices_par_joueur[$ind['partie_joueur_id']][] = $ind['contenu'];
    }
}

$frequence_vote = intval($partie['tours_vote'] ?? 1);
$tour_courant = intval($partie['tour_actuel'] ?? 1);
$est_heure_du_vote = ($tour_courant % $frequence_vote === 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Salon <?= htmlspecialchars($partie['code_room']) ?> · Otak's Games</title>
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="stylesheet" href="../../style/undercover.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="game-page-bg">

<header class="navbar">
    <div class="logo-container">
        <svg class="konoha-logo" viewBox="0 0 100 100">
            <path d="M50 10 C 25 10, 10 25, 10 50 C 10 75, 25 90, 50 90 C 75 90, 90 75, 90 50 C 90 35, 80 25, 70 20 C 60 15, 45 20, 40 30 C 35 40, 40 55, 50 60 C 60 65, 70 58, 68 48 C 66 38, 52 38, 50 48" fill="none" stroke="#e05328" stroke-width="8" stroke-linecap="round" />
            <path d="M75 15 L 90 5" stroke="#e05328" stroke-width="8" stroke-linecap="round" />
        </svg>
        <span class="logo-text">Otak’s Games</span>
    </div>

    <nav class="nav-links">
        <a href="../../index.php" class="nav-item">Accueil</a>
        <a href="#" class="nav-item"><i class="fa-solid fa-crown"></i> Leaderboard</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="../../profil.php" class="nav-item user-profile-link">
                <img src="../../asset/logo_avatar/<?= htmlspecialchars($_SESSION['avatar'] ?? 'avatar1.jpg') ?>" alt="Avatar" class="nav-avatar">
                <span><?= htmlspecialchars($_SESSION['pseudo']) ?></span>
            </a>
        <?php else: ?>
            <a href="../../index.php" class="nav-item">
                <i class="fa-solid fa-user"></i> Mon compte
            </a>
        <?php endif; ?>
    </nav>

    <div class="nav-actions">
        <button class="search-btn" aria-label="Rechercher"><i class="fa-solid fa-magnifying-glass"></i></button>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="../../php/deconnexion.php" class="btn-connexion"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
        <?php else: ?>
            <a href="../../index.php" class="btn-connexion"><i class="fa-solid fa-right-to-bracket"></i> Connexion</a>
        <?php endif; ?>
    </div>
</header>

    <main class="undercover-container">
        <div class="game-panel">
            
            <div class="game-header">
                <h1 class="game-title">
                    <i class="fa-solid <?= $partie['statut'] === 'en_cours' ? 'fa-mask' : ($partie['statut'] === 'termine' ? 'fa-trophy' : 'fa-users') ?>"></i> 
                    <?php 
                        if ($partie['statut'] === 'en_cours') echo 'PARTIE EN COURS (TOUR ' . $tour_courant . '/' . $partie['nb_tours'] . ')';
                        elseif ($partie['statut'] === 'termine') echo 'PARTIE TERMINÉE';
                        else echo 'SALON D\'ATTENTE';
                    ?>
                </h1>
                <div class="room-code">
                    <i class="fa-solid fa-hashtag"></i> <?= htmlspecialchars($partie['code_room']) ?>
                </div>
            </div>

            <p style="text-align: center; color: var(--text-muted); margin-bottom: 5px;">
                Thème : <strong><?= htmlspecialchars($partie['univers_nom']) ?></strong> | 
                Durée : <strong><?= htmlspecialchars($partie['nb_tours'] ?? '-') ?> tours</strong> | 
                Votes : <strong><?php 
                    switch ($frequence_vote) {
                        case 1: echo 'Chaque tour'; break;
                        case 2: echo 'Tous les 2 tours'; break;
                        case 3: echo 'Tous les 3 tours'; break;
                        case 4: echo 'Tous les 4 tours'; break;
                        default: echo 'Tous les ' . $frequence_vote . ' tours';
                    }
                ?></strong>
            </p>

            <!-- 1. MOT SECRET -->
            <?php if ($partie['statut'] === 'en_cours'): ?>
                <div class="word-display" style="text-align: center; background: rgba(224, 83, 40, 0.08); padding: 20px; border-radius: 15px; margin: 20px 0; border: 2px dashed var(--primary-orange);">
                    <span style="text-transform: uppercase; font-size: 12px; color: var(--text-muted); font-weight: 700;">🔎 Ton mot secret</span>
                    <h2 style="font-size: 36px; color: var(--primary-orange); margin: 5px 0;"><?= htmlspecialchars($mon_mot) ?></h2>
                    <small style="color: var(--text-muted);">
                        <?= $mon_jeu_vivant ? 'Donne ton indice quand c\'est ton tour !' : '❌ Tu as été éliminé.' ?>
                    </small>
                </div>
            <?php endif; ?>

            <!-- 2. CARTES DES JOUEURS -->
            <div class="players-grid" style="margin-top: 25px;">
                <?php foreach ($joueurs as $j): ?>
                    <?php $est_joueur_actif = ($j['partie_joueur_id'] == $partie['joueur_actuel_id']); ?>
                    <div class="player-card <?= $j['vivant'] ? '' : 'dead' ?>" style="<?= $est_joueur_actif && $partie['statut'] === 'en_cours' ? 'border: 2px solid var(--primary-orange); background: #fff8f5;' : '' ?>">
                        <img src="../../asset/logo_avatar/<?= htmlspecialchars($j['avatar'] ?? 'avatar1.jpg') ?>" class="avatar" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin: 0 auto 8px;">
                        <div class="name">
                            <?= htmlspecialchars($j['pseudo']) ?>
                            <?php if ($est_joueur_actif && $partie['statut'] === 'en_cours'): ?>
                                <span style="display: block; font-size: 10px; color: var(--primary-orange); font-weight: bold;">🎯 En train de jouer</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($j['id'] == $hote_id): ?>
                            <span style="font-size: 10px; background: var(--primary-orange); color: #fff; padding: 2px 6px; border-radius: 10px; display: inline-block; margin-top: 4px;">Hôte</span>
                        <?php endif; ?>

                        <?php if ($partie['statut'] !== 'attente'): ?>
                            <div class="status <?= $j['vivant'] ? 'alive' : 'dead' ?>" style="font-size: 11px; margin-top: 4px;">
                                <?= $j['vivant'] ? '🟢 En vie' : '🔴 Éliminé' ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($indices_par_joueur[$j['partie_joueur_id']])): ?>
                            <div style="margin-top: 8px; border-top: 1px solid #eee; padding-top: 6px; text-align: left;">
                                <small style="font-size: 10px; color: var(--text-muted); font-weight: bold;">Indices :</small>
                                <?php foreach ($indices_par_joueur[$j['partie_joueur_id']] as $ind): ?>
                                    <span style="display: block; font-size: 11px; background: #f0f0f0; padding: 2px 6px; border-radius: 6px; margin-top: 3px; word-break: break-word;">
                                        💬 <?= htmlspecialchars($ind) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- 3. ZONE DE SAISIE DE L'INDICE -->
            <?php if ($partie['statut'] === 'en_cours' && $mon_jeu_vivant): ?>
                <?php if ($est_mon_tour): ?>
                    <div style="background: #fff3cd; padding: 15px; border-radius: 15px; margin: 20px 0; border: 2px solid #ffebaa;">
                        <p style="font-weight: bold; color: #856404; margin-bottom: 10px; text-align: center;">
                            🎯 C'est à ton tour de donner un indice !
                        </p>
                        <form action="../php/ajouter_indice.php" method="POST" style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <input type="hidden" name="code_room" value="<?= htmlspecialchars($partie['code_room']) ?>">
                            <input type="text" name="contenu" placeholder="Écris ton indice..." required style="flex: 1; padding: 10px; border-radius: 10px; border: 1px solid #dcd0be; min-width: 200px;">
                            <button type="submit" class="btn-primary" style="padding: 10px 20px;">
                                <i class="fa-solid fa-paper-plane"></i> Publier
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div style="background: #f9f9f9; padding: 15px; border-radius: 15px; margin: 20px 0; text-align: center; border: 1px solid #eee;">
                        <p style="color: var(--text-muted); font-style: italic; margin: 0;">
                            ⏳ En attente de l'indice de <strong><?= htmlspecialchars($pseudo_joueur_actuel) ?></strong>...
                        </p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- 4. ZONE DE VOTE D'ÉLIMINATION -->
            <?php if ($partie['statut'] === 'en_cours' && $mon_jeu_vivant && $est_heure_du_vote): ?>
                <div style="background: #fff; padding: 20px; border-radius: 15px; margin: 20px 0; border: 2px solid var(--primary-orange); text-align: center;">
                    <h3 style="color: var(--primary-orange); margin-bottom: 10px;">
                        <i class="fa-solid fa-gavel"></i> Vote d'élimination (Tour <?= $tour_courant ?>)
                    </h3>
                    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 15px;">
                        Sélectionne le joueur que tu penses être l'Undercover :
                    </p>

                    <form action="../php/voter.php" method="POST" style="display: flex; flex-direction: column; gap: 10px; max-width: 320px; margin: 0 auto;">
                        <input type="hidden" name="code_room" value="<?= htmlspecialchars($partie['code_room']) ?>">
                        
                        <select name="id_cible" required style="padding: 10px; border-radius: 10px; border: 1px solid #dcd0be; font-size: 14px;">
                            <option value="" disabled selected>-- Désigner un suspect --</option>
                            <?php foreach ($joueurs as $j): ?>
                                <?php if ($j['vivant'] && $j['id'] != $user_id): ?>
                                    <option value="<?= $j['partie_joueur_id'] ?>"><?= htmlspecialchars($j['pseudo']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" class="btn-primary" style="justify-content: center; width: 100%;">
                            <i class="fa-solid fa-check"></i> Valider mon vote
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- ÉCRAN DE FIN DE PARTIE -->
            <?php if ($partie['statut'] === 'termine'): ?>
                <div style="background: rgba(44, 122, 74, 0.1); border: 2px solid #2c7a4a; padding: 25px; border-radius: 15px; text-align: center; margin: 20px 0;">
                    <h2 style="color: #2c7a4a; margin: 0 0 10px 0;">
                        <i class="fa-solid fa-trophy"></i> Partie Terminée !
                    </h2>
                    <p style="color: var(--text-dark); margin-bottom: 15px;">
                        Les votes sont clos et la partie est arrivée à son terme.
                    </p>
                    <a href="undercover.php" class="btn-primary" style="text-decoration: none; display: inline-flex;">
                        <i class="fa-solid fa-rotate-right"></i> Revenir au menu
                    </a>
                </div>
            <?php endif; ?>

            <!-- Actions d'attente (Hôte) -->
            <div class="game-actions" style="margin-top: 30px;">
                <?php if ($partie['statut'] === 'attente'): ?>
                    <?php if ($est_hote): ?>
                        <form action="../php/lancer_partie.php" method="POST">
                            <input type="hidden" name="code_room" value="<?= htmlspecialchars($partie['code_room']) ?>">
                            <button type="submit" class="btn-primary">
                                <i class="fa-solid fa-play"></i> Lancer la partie
                            </button>
                        </form>
                    <?php else: ?>
                        <p style="color: var(--text-muted); font-style: italic;">En attente du lancement par l'hôte...</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <a href="undercover.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Quitter le salon</a>
        </div>
    </main>

    <script>
    let statutActuel = '<?= $partie['statut'] ?>';
    let joueurActuel = '<?= $partie['joueur_actuel_id'] ?>';
    let tourActuel = '<?= $partie['tour_actuel'] ?>';

    setInterval(() => {
        fetch('lobby.php?code=<?= htmlspecialchars($partie['code_room']) ?>&action=check_statut')
            .then(res => res.json())
            .then(data => {
                if (data.statut !== statutActuel || data.joueur_actuel_id != joueurActuel || data.tour_actuel != tourActuel) {
                    window.location.reload();
                }
            });
    }, 2500);
    </script>
   
   <footer class="footer">
        <div class="footer-bottom">
            <p>&copy; 2026 Otak's Games. Tous droits réservés.| Conçu avec <i class="fa-solid fa-heart" style="color: #e05328;"></i> pour la communauté Ehpad No Sekai.</p>
        </div>
    </footer>
</body>
</html>