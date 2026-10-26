<?php
require_once __DIR__ . '/../../db_connect.php';
require_once __DIR__ . '/../../php/security.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];$code_room  = $_GET['code'] ?? '';$token_csrf = generate_csrf_token();

// Variables de configuration du template
$page_title = "Undercover · Otak's Games";
$page_css   = "/undercover/style/undercover.css";
$body_class = "game-page-bg";

// Inclusion du header global
require_once __DIR__ . '/../../includes/header.php';

// -------------------------- Récupération de la partie ------------------------------------------------------
$partie = null;
if (!empty($code_room)) {
    $stmt =$pdo->prepare("
        SELECT pu.id, pu.code_room, pu.statut, pu.mode, pu.nb_tours, pu.tours_vote, pu.tour_actuel, pu.joueur_actuel_id, u.nom AS univers_nom
        FROM partie_undercover pu
        JOIN univers u ON pu.univers_id = u.id
        WHERE pu.code_room = ?
    ");
    $stmt->execute([$code_room]);
    $partie =$stmt->fetch();
}

// ------------------------------ Endpoint AJAX (Polling) ---------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'check_statut' &&$partie) {
    header('Content-Type: application/json');
    echo json_encode([
        'statut' => $partie['statut'],
        'joueur_actuel_id' => $partie['joueur_actuel_id'],
        'tour_actuel' => $partie['tour_actuel']
    ]);
    exit();
}

// --------------------------- Données du formulaire & du joueur ----------------------------------------------
$univers_list =$pdo->query("SELECT id, nom FROM univers")->fetchAll();

$mon_mot = null;
$mon_image = null;
$mon_jeu_vivant = false;

if ($partie &&$partie['statut'] === 'en_cours') {
    $stmtMot =$pdo->prepare("
        SELECT p.nom, p.image, pj.vivant
        FROM partie_joueur pj 
        LEFT JOIN personnage p ON pj.personnage_id = p.id 
        WHERE pj.partie_id = ? AND pj.utilisateur_id = ?
    ");
    $stmtMot->execute([$partie['id'], $user_id]);$res = $stmtMot->fetch();$mon_mot = $res['nom'] ?? 'Inconnu';$mon_image = !empty($res['image']) ?$res['image'] : 'default.jpg';
    $mon_jeu_vivant = (bool)($res['vivant'] ?? false);
}

// -------------------------------- Liste des joueurs ----------------------------------------------------------
$joueurs = [];$hote_id = 0;
$mon_partie_joueur_id = null;
$pseudo_joueur_actuel = 'Inconnu';

if (!empty($partie) && is_array($partie)) {
    $stmtJoueurs =$pdo->prepare("
        SELECT u.id, u.pseudo, u.avatar, pj.id AS partie_joueur_id, pj.vivant
        FROM partie_joueur pj
        JOIN utilisateur u ON pj.utilisateur_id = u.id
        WHERE pj.partie_id = ?
        ORDER BY pj.id ASC
    ");
    $stmtJoueurs->execute([$partie['id']]);
    $resJoueurs =$stmtJoueurs->fetchAll();

    if (is_array($resJoueurs)) {
        $joueurs =$resJoueurs;
    }

    if (!empty($joueurs) && is_array($joueurs[0])) {
        $hote_id =$joueurs[0]['id'] ?? 0;

        foreach ($joueurs as$j) {
            if (is_array($j)) {
                if (isset($j['id']) && $j['id'] ==$user_id) {
                    $mon_partie_joueur_id =$j['partie_joueur_id'] ?? null;
                }
                if (isset($j['partie_joueur_id'],$partie['joueur_actuel_id']) && $j['partie_joueur_id'] ==$partie['joueur_actuel_id']) {
                    $pseudo_joueur_actuel =$j['pseudo'] ?? 'Inconnu';
                }
            }
        }
    }
}

$est_hote = ($hote_id == $user_id);$est_mon_tour = ($mon_partie_joueur_id !== null && !empty($partie['joueur_actuel_id']) && $mon_partie_joueur_id ==$partie['joueur_actuel_id']);

// --------------------------- Historique des indices ----------------------------------------------------------
$indices_par_joueur = [];
if ($partie &&$partie['statut'] === 'en_cours') {
    $stmtIndices =$pdo->prepare("
        SELECT i.partie_joueur_id, i.contenu 
        FROM indice i
        JOIN partie_joueur pj ON i.partie_joueur_id = pj.id
        WHERE pj.partie_id = ? 
        ORDER BY i.id ASC
    ");
    $stmtIndices->execute([$partie['id']]);
    foreach ($stmtIndices->fetchAll() as $ind) {$indices_par_joueur[$ind['partie_joueur_id']][] =$ind['contenu'];
    }
}

$frequence_vote = intval($partie['tours_vote'] ?? 1);$tour_courant   = intval($partie['tour_actuel'] ?? 1);$est_heure_du_vote = ($tour_courant %$frequence_vote === 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Undercover · Otak's Games</title>
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="stylesheet" href="../../undercover/style/undercover.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="game-page-bg">

<main class="undercover-container">
    <div class="game-panel">

        <?php if (isset($_GET['erreur'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_GET['erreur'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (!$partie): ?>
            <h1 class="game-title"><i class="fa-solid fa-mask"></i> UNDERCOVER</h1>

            <div class="game-actions" style="max-width: 480px; margin: 20px auto; flex-direction: column;">
                
                <!-- FORMULAIRE DE CRÉATION MODERNE -->
                <form action="../php/undercover_actions.php" method="POST" class="create-room-form">
                    <input type="hidden" name="action" value="creer">
                    <input type="hidden" name="csrf_token" value="<?= $token_csrf ?>">

                    <!-- 1. Choix du Thème -->
                    <div class="custom-select-group">
                        <label class="group-label"><i class="fa-solid fa-layer-group"></i> Thème / Univers</label>
                        <div class="theme-grid">
                            <?php foreach ($univers_list as $index =>$u): ?>
                                <label class="theme-card">
                                    <input type="radio" name="univers_id" value="<?= $u['id'] ?>" <?= $index === 0 ? 'checked' : '' ?>>
                                    <div class="theme-card-content">
                                        <span class="theme-name"><?= htmlspecialchars($u['nom'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- 2. Nombre d'Undercovers -->
                    <div class="custom-select-group">
                        <label class="group-label"><i class="fa-solid fa-user-ninja"></i> Nombre d'Undercovers</label>
                        <div class="chip-selector">
                            <label class="chip-option">
                                <input type="radio" name="nb_undercover" value="1" checked>
                                <span>1 Espion</span>
                            </label>
                            <label class="chip-option">
                                <input type="radio" name="nb_undercover" value="2">
                                <span>2 Espions</span>
                            </label>
                        </div>
                    </div>

                    <!-- 3. Nombre de Tours -->
                    <div class="custom-select-group">
                        <label class="group-label"><i class="fa-solid fa-rotate"></i> Nombre de tours</label>
                        <div class="chip-selector">
                            <?php for ($i = 2; $i <= 5; $i++): ?>
                                <label class="chip-option">
                                    <input type="radio" name="nb_tours" value="<?= $i ?>" <?= $i === 3 ? 'checked' : '' ?>>
                                    <span><?= $i ?> Tours</span>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- 4. Fréquence des votes -->
                    <div class="custom-select-group">
                        <label class="group-label"><i class="fa-solid fa-check-to-slot"></i> Fréquence des votes</label>
                        <div class="chip-selector">
                            <label class="chip-option">
                                <input type="radio" name="tours_vote" value="1" checked>
                                <span>Chaque tour</span>
                            </label>
                            <label class="chip-option">
                                <input type="radio" name="tours_vote" value="2">
                                <span>Tous les 2 tours</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary btn-create-submit">
                        <i class="fa-solid fa-fire"></i> Créer le salon
                    </button>
                </form>

                <hr style="margin: 25px 0; border:0; border-top:1px solid #ddd; width:100%;">

                <!-- FORMULAIRE REJOINDRE -->
                <form action="../php/undercover_actions.php" method="POST" style="width: 100%;">
                    <input type="hidden" name="action" value="rejoindre">
                    <input type="hidden" name="csrf_token" value="<?= $token_csrf ?>">

                    <div class="input-group">
                        <label class="group-label"><i class="fa-solid fa-key"></i> Code de salon</label>
                        <input type="text" name="code_room" placeholder="Ex: 4B9Z1A" maxlength="6" style="text-transform:uppercase; text-align:center; font-weight:bold; letter-spacing:2px;" required>
                    </div>
                    <button type="submit" class="btn-secondary" style="width:100%; margin-top:10px;">
                        <i class="fa-solid fa-right-to-bracket"></i> Rejoindre la room
                    </button>
                </form>
            </div>

        <?php else: ?>
            
            <div class="game-header">
                <h2>
                    <i class="fa-solid <?= $partie['statut'] === 'en_cours' ? 'fa-mask' : 'fa-users' ?>"></i>
                    <?= $partie['statut'] === 'en_cours' ? 'Tour ' . $tour_courant . '/' .$partie['nb_tours'] : 'Salon d\'attente' ?>
                </h2>
                <div class="room-code"><i class="fa-solid fa-hashtag"></i> <?= htmlspecialchars($partie['code_room'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>

            <!-- AFFICHAGE CARTE KYARA DU PERSONNAGE SECRET -->
            <?php if ($partie['statut'] === 'en_cours'): ?>
                <div class="kyara-card-wrapper">
                    <div class="kyara-card">
                        <div class="kyara-card-header">
                            <span class="kyara-card-title">Carte Personnage</span>
                            <i class="fa-solid fa-shield-halved" style="color: var(--primary-orange);"></i>
                        </div>

                        <div class="kyara-card-img-frame">
                            <img src="../../assets/images_personnages/<?= htmlspecialchars($mon_image, ENT_QUOTES, 'UTF-8') ?>" alt="Personnage Secret">
                        </div>

                        <div class="kyara-card-footer">
                            <h2 class="kyara-character-name"><?= htmlspecialchars($mon_mot, ENT_QUOTES, 'UTF-8') ?></h2>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Grille des Joueurs -->
            <div class="players-grid">
                <?php foreach ($joueurs as$j): ?>
                    <?php $est_actif = ($j['partie_joueur_id'] ==$partie['joueur_actuel_id']); ?>
                    <div class="player-card <?= $est_actif && $partie['statut'] === 'en_cours' ? 'active-turn' : '' ?> <?= !$j['vivant'] ? 'dead' : '' ?>">
                        <img src="../../assets/logo_avatar/<?= htmlspecialchars($j['avatar'] ?? 'avatar1.jpg', ENT_QUOTES, 'UTF-8') ?>" class="avatar">
                        <div class="name"><?= htmlspecialchars($j['pseudo'], ENT_QUOTES, 'UTF-8') ?></div>
                        
                        <?php if ($partie['statut'] !== 'attente'): ?>
                            <small style="color: <?= $j['vivant'] ? 'green' : 'red' ?>; font-weight:bold; font-size:10px;">
                                <?= $j['vivant'] ? '🟢 En vie' : '🔴 Éliminé' ?>
                            </small>
                        <?php endif; ?>

                        <?php if (!empty($indices_par_joueur[$j['partie_joueur_id']])): ?>
                            <div style="margin-top:8px; border-top:1px solid #eee; padding-top:5px; width:100%;">
                                <?php foreach ($indices_par_joueur[$j['partie_joueur_id']] as$ind): ?>
                                    <span style="display:block; font-size:11px; background:#f5f5f5; padding:3px; border-radius:4px; margin-top:2px;">💬 <?= htmlspecialchars($ind, ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Saisie de l'indice -->
            <?php if ($partie['statut'] === 'en_cours' &&$mon_jeu_vivant): ?>
                <?php if ($est_mon_tour): ?>
                    <form action="../php/undercover_actions.php" method="POST" style="background:#fff3cd; padding:15px; border-radius:12px; display:flex; gap:10px; margin:20px 0;">
                        <input type="hidden" name="action" value="indice">
                        <input type="hidden" name="code_room" value="<?= htmlspecialchars($partie['code_room'], ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="csrf_token" value="<?= $token_csrf ?>">
                        <input type="text" name="contenu" placeholder="Écris ton indice..." required style="flex:1; padding:10px; border-radius:8px; border:1px solid #ccc;">
                        <button type="submit" class="btn-primary" style="padding:10px 20px;">Publier</button>
                    </form>
                <?php else: ?>
                    <p style="text-align:center; color:#666; font-style:italic;">⏳ Tour de <strong><?= htmlspecialchars($pseudo_joueur_actuel, ENT_QUOTES, 'UTF-8') ?></strong>...</p>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Zone de vote -->
            <?php if ($partie['statut'] === 'en_cours' && $mon_jeu_vivant &&$est_heure_du_vote): ?>
                <form action="../php/undercover_actions.php" method="POST" style="background:#fff; border:2px solid var(--primary-orange); padding:15px; border-radius:12px; text-align:center; margin:20px 0;">
                    <input type="hidden" name="action" value="voter">
                    <input type="hidden" name="code_room" value="<?= htmlspecialchars($partie['code_room'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="csrf_token" value="<?= $token_csrf ?>">

                    <h3 style="margin-bottom:10px;">Vote d'élimination</h3>
                    <select name="id_cible" required style="padding:8px; border-radius:8px; margin-bottom:10px;">
                        <option value="" disabled selected>-- Choisir un suspect --</option>
                        <?php foreach ($joueurs as$j): ?>
                            <?php if ($j['vivant'] && $j['id'] !=$user_id): ?>
                                <option value="<?= $j['partie_joueur_id'] ?>"><?= htmlspecialchars($j['pseudo'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-primary" style="display:block; margin:0 auto;">Valider le vote</button>
                </form>
            <?php endif; ?>

            <!-- Bouton Lancer (Hôte seulement) -->
            <?php if ($partie['statut'] === 'attente' &&$est_hote): ?>
                <form action="../php/undercover_actions.php" method="POST" style="text-align:center; margin-top:20px;">
                    <input type="hidden" name="action" value="lancer">
                    <input type="hidden" name="code_room" value="<?= htmlspecialchars($partie['code_room'], ENT_QUOTES, 'UTF-8') ?>">
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

<?php if ($partie): ?>
<div id="game-status-data" 
     data-code="<?= htmlspecialchars($partie['code_room'], ENT_QUOTES, 'UTF-8') ?>"
     data-statut="<?= htmlspecialchars($partie['statut'], ENT_QUOTES, 'UTF-8') ?>"
     data-joueur="<?= htmlspecialchars((string)$partie['joueur_actuel_id'], ENT_QUOTES, 'UTF-8') ?>"
     data-tour="<?= htmlspecialchars((string)$partie['tour_actuel'], ENT_QUOTES, 'UTF-8') ?>">
</div>

<script>
const gameData = document.getElementById('game-status-data').dataset;

setInterval(() => {
    fetch(`undercover.php?code=${encodeURIComponent(gameData.code)}&action=check_statut`)
        .then(res => res.json())
        .then(data => {
            // Comparaison stricte convertie en chaîne pour éviter tout bug de type null/empty
            const statutChange = String(data.statut) !== String(gameData.statut);
            const joueurChange = String(data.joueur_actuel_id ?? '') !== String(gameData.joueur ?? '');
            const tourChange   = String(data.tour_actuel ?? '') !== String(gameData.tour ?? '');

            if (statutChange || joueurChange || tourChange) {
                window.location.reload();
            }
        })
        .catch(err => console.error("Erreur polling:", err));
}, 2500);
</script>
<?php endif; ?>

<?php 
// Inclusion du footer global
require_once __DIR__ . '/../../includes/footer.php'; 
?>
</body>
</html>