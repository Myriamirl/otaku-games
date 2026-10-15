<?php
/* =========================================================
             TRAITEMENTS BACKEND & SÉCURITÉ
   ========================================================= */
require_once __DIR__ . '/../../db_connect.php';
require_once __DIR__ . '/../../php/security.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

$user_id    = $_SESSION['user_id'];
$code_room  = $_GET['code'] ?? '';
$token_csrf = generate_csrf_token();

// --- Récupération de la partie ---
$partie = null;
if (!empty($code_room)) {
    $stmt = $pdo->prepare("
        SELECT pu.id, pu.code_room, pu.statut, pu.mode, pu.nb_tours, pu.tours_vote, pu.tour_actuel, pu.joueur_actuel_id, u.nom AS univers_nom
        FROM partie_undercover pu
        JOIN univers u ON pu.univers_id = u.id
        WHERE pu.code_room = ?
    ");
    $stmt->execute([$code_room]);
    $partie = $stmt->fetch();
}

// --- Endpoint AJAX (Polling) ---
if (isset($_GET['action']) && $_GET['action'] === 'check_statut' && $partie) {
    header('Content-Type: application/json');
    echo json_encode([
        'statut' => $partie['statut'],
        'joueur_actuel_id' => $partie['joueur_actuel_id'],
        'tour_actuel' => $partie['tour_actuel']
    ]);
    exit();
}

// --- Données du formulaire & du joueur ---
$univers_list = $pdo->query("SELECT id, nom FROM univers")->fetchAll();

$mon_mot = null;
$mon_image = null;
$mon_jeu_vivant = false;

if ($partie && $partie['statut'] === 'en_cours') {
    $stmtMot = $pdo->prepare("
        SELECT p.nom, p.image, pj.vivant
        FROM partie_joueur pj 
        LEFT JOIN personnage p ON pj.personnage_id = p.id 
        WHERE pj.partie_id = ? AND pj.utilisateur_id = ?
    ");
    $stmtMot->execute([$partie['id'], $user_id]);
    $res = $stmtMot->fetch();
    $mon_mot        = $res['nom'] ?? 'Inconnu';
    $mon_image      = !empty($res['image']) ? $res['image'] : 'default.jpg';
    $mon_jeu_vivant = (bool)($res['vivant'] ?? false);
}

// --- Liste des joueurs ---
$joueurs = [];
$hote_id = 0;
$mon_partie_joueur_id = null;
$pseudo_joueur_actuel = 'Inconnu';

if ($partie) {
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

    foreach ($joueurs as $j) {
        if ($j['id'] == $user_id) {
            $mon_partie_joueur_id = $j['partie_joueur_id'];
        }
        if ($j['partie_joueur_id'] == $partie['joueur_actuel_id']) {
            $pseudo_joueur_actuel = $j['pseudo'];
        }
    }
}

$est_hote     = ($hote_id == $user_id);
$est_mon_tour = ($mon_partie_joueur_id == $partie['joueur_actuel_id']);

// --- Historique des indices ---
$indices_par_joueur = [];
if ($partie && $partie['statut'] === 'en_cours') {
    $stmtIndices = $pdo->prepare("
        SELECT i.partie_joueur_id, i.contenu 
        FROM indice i
        JOIN partie_joueur pj ON i.partie_joueur_id = pj.id
        WHERE pj.partie_id = ? 
        ORDER BY i.id ASC
    ");
    $stmtIndices->execute([$partie['id']]);
    foreach ($stmtIndices->fetchAll() as $ind) {
        $indices_par_joueur[$ind['partie_joueur_id']][] = $ind['contenu'];
    }
}

$frequence_vote = intval($partie['tours_vote'] ?? 1);
$tour_courant   = intval($partie['tour_actuel'] ?? 1);
$est_heure_du_vote = ($tour_courant % $frequence_vote === 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- =========================================================
                    EN-TÊTE HTML & STYLES
         ========================================================= -->
    <meta charset="UTF-8">
    <title>Undercover · Otak's Games</title>
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="stylesheet" href="../css/undercover.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="game-page-bg">

<!-- =========================================================
                     BARRE DE NAVIGATION
     ========================================================= -->
<header class="navbar">
    <div class="logo-container">
        <span class="logo-text">Otak’s Games</span>
    </div>
    <nav class="nav-links">
        <a href="../../index.php" class="nav-item">Accueil</a>
        <a href="../../profil.php" class="nav-item"><i class="fa-solid fa-user"></i> Mon compte</a>
    </nav>
</header>

<!-- =========================================================
                    CONTENU PRINCIPAL
     ========================================================= -->
<main class="undercover-container">
    <div class="game-panel">

        <!-- --- Bloc Alertes --- -->
        <?php if (isset($_GET['erreur'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_GET['erreur']) ?>
            </div>
        <?php endif; ?>

        <?php if (!$partie): ?>
            <!-- --- BLOC : Créer / Rejoindre un salon --- -->
            <h1 class="game-title"><i class="fa-solid fa-mask"></i> UNDERCOVER</h1>

            <div class="game-actions" style="max-width: 400px; margin: 20px auto; flex-direction: column;">
                <!-- Formulaire Création -->
                <form action="../php/undercover_actions.php" method="POST" style="width: 100%;">
                    <input type="hidden" name="action" value="creer">
                    <input type="hidden" name="csrf_token" value="<?= $token_csrf ?>">

                    <div class="input-group">
                        <label>Thème / Univers</label>
                        <select name="univers_id">
                            <?php foreach ($univers_list as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Nombre d'Undercovers</label>
                        <select name="nb_undercover" required>
                            <option value="1">1 Undercover</option>
                            <option value="2">2 Undercovers</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Nombre de tours total</label>
                        <select name="nb_tours" required>
                            <?php for ($i = 2; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> Tours</option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Fréquence des votes</label>
                        <select name="tours_vote" required>
                            <option value="1">À chaque tour</option>
                            <option value="2">Tous les 2 tours</option>
                            <option value="3">Tous les 3 tours</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary" style="width:100%; margin-top:15px;">
                        <i class="fa-solid fa-plus"></i> Créer un salon
                    </button>
                </form>

                <hr style="margin: 25px 0; border:0; border-top:1px solid #ddd; width:100%;">

                <!-- Formulaire Rejoindre -->
                <form action="../php/undercover_actions.php" method="POST" style="width: 100%;">
                    <input type="hidden" name="action" value="rejoindre">
                    <input type="hidden" name="csrf_token" value="<?= $token_csrf ?>">

                    <div class="input-group">
                        <label>Code de salon</label>
                        <input type="text" name="code_room" placeholder="Ex: 4B9Z1A" maxlength="6" style="text-transform:uppercase; text-align:center; font-weight:bold; letter-spacing:2px;" required>
                    </div>
                    <button type="submit" class="btn-secondary" style="width:100%; margin-top:10px;">
                        <i class="fa-solid fa-right-to-bracket"></i> Rejoindre la room
                    </button>
                </form>
            </div>

        <?php else: ?>
            <!-- --- BLOC : En partie (Lobby / Jeu) --- -->
            
            <!-- En-tête Room -->
            <div class="game-header">
                <h2>
                    <i class="fa-solid <?= $partie['statut'] === 'en_cours' ? 'fa-mask' : 'fa-users' ?>"></i>
                    <?= $partie['statut'] === 'en_cours' ? 'Tour ' . $tour_courant . '/' . $partie['nb_tours'] : 'Salon d\'attente' ?>
                </h2>
                <div class="room-code"><i class="fa-solid fa-hashtag"></i> <?= htmlspecialchars($partie['code_room']) ?></div>
            </div>

            <!-- Personnage secret du joueur -->
            <?php if ($partie['statut'] === 'en_cours'): ?>
                <div class="character-card">
                    <span style="font-size:12px; color:#666; font-weight:bold; text-transform:uppercase;">Ton personnage secret</span>
                    <div style="margin: 10px 0;">
                        <img src="../../asset/images_personnages/<?= htmlspecialchars($mon_image) ?>" alt="Personnage">
                    </div>
                    <h2 style="color:var(--primary-orange); margin:0; font-size:28px;"><?= htmlspecialchars($mon_mot) ?></h2>
                </div>
            <?php endif; ?>

            <!-- Grille des Joueurs & Indices -->
            <div class="players-grid">
                <?php foreach ($joueurs as $j): ?>
                    <?php $est_actif = ($j['partie_joueur_id'] == $partie['joueur_actuel_id']); ?>
                    <div class="player-card <?= $est_actif && $partie['statut'] === 'en_cours' ? 'active-turn' : '' ?> <?= !$j['vivant'] ? 'dead' : '' ?>">
                        <img src="../../asset/logo_avatar/<?= htmlspecialchars($j['avatar'] ?? 'avatar1.jpg') ?>" class="avatar">
                        <div class="name"><?= htmlspecialchars($j['pseudo']) ?></div>
                        
                        <?php if ($partie['statut'] !== 'attente'): ?>
                            <small style="color: <?= $j['vivant'] ? 'green' : 'red' ?>; font-weight:bold; font-size:10px;">
                                <?= $j['vivant'] ? '🟢 En vie' : '🔴 Éliminé' ?>
                            </small>
                        <?php endif; ?>

                        <?php if (!empty($indices_par_joueur[$j['partie_joueur_id']])): ?>
                            <div style="margin-top:8px; border-top:1px solid #eee; padding-top:5px; width:100%;">
                                <?php foreach ($indices_par_joueur[$j['partie_joueur_id']] as $ind): ?>
                                    <span style="display:block; font-size:11px; background:#f5f5f5; padding:3px; border-radius:4px; margin-top:2px;">💬 <?= htmlspecialchars($ind) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Saisie de l'indice -->
            <?php if ($partie['statut'] === 'en_cours' && $mon_jeu_vivant): ?>
                <?php if ($est_mon_tour): ?>
                    <form action="../php/undercover_actions.php" method="POST" style="background:#fff3cd; padding:15px; border-radius:12px; display:flex; gap:10px; margin:20px 0;">
                        <input type="hidden" name="action" value="indice">
                        <input type="hidden" name="code_room" value="<?= htmlspecialchars($partie['code_room']) ?>">
                        <input type="hidden" name="csrf_token" value="<?= $token_csrf ?>">
                        <input type="text" name="contenu" placeholder="Écris ton indice..." required style="flex:1; padding:10px; border-radius:8px; border:1px solid #ccc;">
                        <button type="submit" class="btn-primary" style="padding:10px 20px;">Publier</button>
                    </form>
                <?php else: ?>
                    <p style="text-align:center; color:#666; font-style:italic;">⏳ Tour de <strong><?= htmlspecialchars($pseudo_joueur_actuel) ?></strong>...</p>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Zone de vote -->
            <?php if ($partie['statut'] === 'en_cours' && $mon_jeu_vivant && $est_heure_du_vote): ?>
                <form action="../php/undercover_actions.php" method="POST" style="background:#fff; border:2px solid var(--primary-orange); padding:15px; border-radius:12px; text-align:center; margin:20px 0;">
                    <input type="hidden" name="action" value="voter">
                    <input type="hidden" name="code_room" value="<?= htmlspecialchars($partie['code_room']) ?>">
                    <input type="hidden" name="csrf_token" value="<?= $token_csrf ?>">

                    <h3 style="margin-bottom:10px;">Vote d'élimination</h3>
                    <select name="id_cible" required style="padding:8px; border-radius:8px; margin-bottom:10px;">
                        <option value="" disabled selected>-- Choisir un suspect --</option>
                        <?php foreach ($joueurs as $j): ?>
                            <?php if ($j['vivant'] && $j['id'] != $user_id): ?>
                                <option value="<?= $j['partie_joueur_id'] ?>"><?= htmlspecialchars($j['pseudo']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-primary" style="display:block; margin:0 auto;">Valider le vote</button>
                </form>
            <?php endif; ?>

            <!-- Bouton Lancer (Hôte seulement) -->
            <?php if ($partie['statut'] === 'attente' && $est_hote): ?>
                <form action="../php/undercover_actions.php" method="POST" style="text-align:center; margin-top:20px;">
                    <input type="hidden" name="action" value="lancer">
                    <input type="hidden" name="code_room" value="<?= htmlspecialchars($partie['code_room']) ?>">
                    <input type="hidden" name="csrf_token" value="<?= $token_csrf ?>">
                    <button type="submit" class="btn-primary"><i class="fa-solid fa-play"></i> Lancer la partie</button>
                </form>
            <?php endif; ?>

            <div style="text-align:center; margin-top:20px;">
                <a href="undercover.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Quitter la room</a>
            </div>
        <?php endif; ?>

    </div>
</main>

<!-- =========================================================
                SYNCHRONISATION EN TEMPS RÉEL (AJAX)
     ========================================================= -->
<?php if ($partie): ?>
<script>
let statutActuel = '<?= $partie['statut'] ?>';
let joueurActuel = '<?= $partie['joueur_actuel_id'] ?>';
let tourActuel   = '<?= $partie['tour_actuel'] ?>';

setInterval(() => {
    fetch('undercover.php?code=<?= htmlspecialchars($partie['code_room']) ?>&action=check_statut')
        .then(res => res.json())
        .then(data => {
            if (data.statut !== statutActuel || data.joueur_actuel_id != joueurActuel || data.tour_actuel != tourActuel) {
                window.location.reload();
            }
        });
}, 2500);
</script>
<?php endif; ?>

<!-- =========================================================
                    PIED DE PAGE
     ========================================================= -->
<footer class="footer">
    <div class="footer-bottom">
        <p>&copy; 2026 Otak's Games. Tous droits réservés.</p>
    </div>
</footer>

</body>
</html>