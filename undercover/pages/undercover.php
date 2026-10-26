<?php
require_once __DIR__ . '/../../db_connect.php';
require_once __DIR__ . '/../../php/security.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

$user_id    = $_SESSION['user_id'];
$code_room  = $_GET['code'] ?? '';
$token_csrf = generate_csrf_token();

// -------------------------- Récupération de la partie ------------------------------------------------------
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

// Variables de configuration du template
$page_title = "Undercover · Otak's Games";
$page_css   = "/undercover/style/undercover.css";
$body_class = "game-page-bg";

// Inclusion du header global (HTML)
require_once __DIR__ . '/../../includes/header.php';

// --------------------------- Données du formulaire & du joueur ----------------------------------------------
$univers_list = $pdo->query("SELECT id, nom FROM univers")->fetchAll(PDO::FETCH_ASSOC);

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

// -------------------------------- Liste des joueurs ----------------------------------------------------------
$joueurs = [];
$hote_id = 0;
$mon_partie_joueur_id = null;
$pseudo_joueur_actuel = 'Inconnu';

if (!empty($partie) && is_array($partie)) {
    $stmtJoueurs = $pdo->prepare("
        SELECT u.id, u.pseudo, u.avatar, pj.id AS partie_joueur_id, pj.vivant
        FROM partie_joueur pj
        JOIN utilisateur u ON pj.utilisateur_id = u.id
        WHERE pj.partie_id = ?
        ORDER BY pj.id ASC
    ");
    $stmtJoueurs->execute([$partie['id']]);
    $resJoueurs = $stmtJoueurs->fetchAll(PDO::FETCH_ASSOC);

    if (is_array($resJoueurs)) {
        $joueurs = $resJoueurs;
    }

    if (!empty($joueurs) && is_array($joueurs[0])) {
        $hote_id = $joueurs[0]['id'] ?? 0;

        foreach ($joueurs as $j) {
            if (is_array($j)) {
                if (isset($j['id']) && $j['id'] == $user_id) {
                    $mon_partie_joueur_id = $j['partie_joueur_id'] ?? null;
                }

                // VÉRIFICATION SÉCURISÉE : Vérifie que $partie est un tableau et possède la clé 'joueur_actuel_id'
                if (
                    isset($j['partie_joueur_id']) &&
                    !empty($partie['joueur_actuel_id']) &&
                    $j['partie_joueur_id'] == $partie['joueur_actuel_id']
                ) {
                    $pseudo_joueur_actuel = $j['pseudo'] ?? 'Inconnu';
                }
            }
        }
    }
}

$est_hote     = ($hote_id == $user_id);
$est_mon_tour = ($mon_partie_joueur_id !== null && !empty($partie['joueur_actuel_id']) && $mon_partie_joueur_id == $partie['joueur_actuel_id']);

// --------------------------- Historique des indices ----------------------------------------------------------
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

// --------------------------- Logique du tour & Déclenchement du Vote ----------------------------------------
// --------------------------- Logique du tour & Déclenchement du Vote ----------------------------------------
$frequence_vote = 1;
$tour_courant   = 1;
$est_heure_du_vote = false;

if ($partie && is_array($partie)) {
    $frequence_vote = intval($partie['tours_vote'] ?? 1);
    $tour_courant   = intval($partie['tour_actuel'] ?? 1);

    // Nombre de joueurs encore en vie
    $nb_joueurs_vivants = 0;
    foreach ($joueurs as $j) {
        if (!empty($j['vivant'])) {
            $nb_joueurs_vivants++;
        }
    }

    // Nombre d'indices postés uniquement pendant ce tour précis
    $stmtIndicesTour = $pdo->prepare("
        SELECT COUNT(i.id) 
        FROM indice i 
        JOIN partie_joueur pj ON i.partie_joueur_id = pj.id 
        WHERE pj.partie_id = ? AND i.tour_numero = ?
    ");
    $stmtIndicesTour->execute([$partie['id'], $tour_courant]);
    $indices_ce_tour = (int)$stmtIndicesTour->fetchColumn();

    // Condition : 3 indices par joueur vivant (ex: 3 joueurs vivants = 9 indices pour finir le tour)
    $indices_requis_par_tour = $nb_joueurs_vivants * 3;
    $tour_actuel_termine     = ($nb_joueurs_vivants > 0) && ($indices_ce_tour >= $indices_requis_par_tour);

    // On active le vote seulement si le tour BDD courant est complet et aligné sur la fréquence
    $est_heure_du_vote = $tour_actuel_termine && ($tour_courant % $frequence_vote === 0);
}
?>

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

                <!-- FORMULAIRE DE CRÉATION ROOM -->
                <form action="../php/undercover_actions.php" method="POST" class="create-room-form">
                    <input type="hidden" name="action" value="creer">
                    <input type="hidden" name="csrf_token" value="<?= $token_csrf ?>">

                    <!-- Choix du Thème -->
                    <div class="custom-select-group">
                        <label class="group-label"><i class="fa-solid fa-layer-group"></i> Thème / Univers</label>
                        <div class="theme-grid">
                            <?php if (!empty($univers_list) && is_array($univers_list)): ?>
                                <?php foreach ($univers_list as $index => $u): ?>
                                    <?php
                                    $u_id  = is_array($u) && isset($u['id']) ? (int)$u['id'] : 0;
                                    $u_nom = is_array($u) && isset($u['nom']) ? $u['nom'] : '';
                                    ?>
                                    <?php if ($u_id > 0 && !empty($u_nom)): ?>
                                        <label class="theme-card">
                                            <input type="radio" name="univers_id" value="<?= $u_id ?>" <?= $index === 0 ? 'checked' : '' ?>>
                                            <div class="theme-card-content">
                                                <span class="theme-name"><?= htmlspecialchars($u_nom, ENT_QUOTES, 'UTF-8') ?></span>
                                            </div>
                                        </label>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="font-size:12px; color:#888;">Aucun univers disponible en base de données.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Nombre d'Undercovers -->
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

                    <!-- Nombre de Tours -->
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
                        <!-- Petite ligne explicative discrète -->
                        <small style="display: block; margin-top: 6px; color: #888; font-size: 11px; font-style: italic;">
                            ℹ️ Note : 1 tour correspond à 3 indices donnés par joueur avant la phase de vote.
                        </small>
                    </div>

                    <!-- Fréquence des votes -->
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
                            <label class="chip-option">
                                <input type="radio" name="tours_vote" value="3">
                                <span>Tous les 3 tours</span>
                            </label>
                            <label class="chip-option">
                                <input type="radio" name="tours_vote" value="4">
                                <span>Tous les 4 tours</span>
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
                    <?= $partie['statut'] === 'en_cours' ? 'Tour ' . $tour_courant . '/' . $partie['nb_tours'] : 'Salon d\'attente' ?>
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
                <?php foreach ($joueurs as $j): ?>
                    <?php
                    $joueur_actuel_id = $partie['joueur_actuel_id'] ?? null;
                    $j_partie_id      = $j['partie_joueur_id'] ?? null;
                    $est_actif        = ($joueur_actuel_id !== null && $j_partie_id !== null && $j_partie_id == $joueur_actuel_id);
                    ?>
                    <div class="player-card <?= $est_actif && ($partie['statut'] ?? '') === 'en_cours' ? 'active-turn' : '' ?> <?= !($j['vivant'] ?? true) ? 'dead' : '' ?>">
                        <img src="../../assets/logo_avatar/<?= htmlspecialchars($j['avatar'] ?? 'avatar1.jpg', ENT_QUOTES, 'UTF-8') ?>" class="avatar">
                        <div class="name"><?= htmlspecialchars($j['pseudo'] ?? 'Joueur', ENT_QUOTES, 'UTF-8') ?></div>

                        <?php if (($partie['statut'] ?? '') !== 'attente'): ?>
                            <small style="color: <?= !empty($j['vivant']) ? 'green' : 'red' ?>; font-weight:bold; font-size:10px;">
                                <?= !empty($j['vivant']) ? '🟢 En vie' : '🔴 Éliminé' ?>
                            </small>
                        <?php endif; ?>

                        <?php if (!empty($j['partie_joueur_id']) && !empty($indices_par_joueur[$j['partie_joueur_id']])): ?>
                            <div style="margin-top:8px; border-top:1px solid #eee; padding-top:5px; width:100%;">
                                <?php foreach ($indices_par_joueur[$j['partie_joueur_id']] as $ind): ?>
                                    <span style="display:block; font-size:11px; background:#f5f5f5; padding:3px; border-radius:4px; margin-top:2px;">💬 <?= htmlspecialchars($ind, ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Saisie de l'indice (Uniquement si CE N'EST PAS l'heure du vote) -->
            <?php if ($partie['statut'] === 'en_cours' && $mon_jeu_vivant && !$est_heure_du_vote): ?>
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

                <!-- Zone de vote (Prend la place du formulaire d'indice) -->
            <?php elseif ($partie['statut'] === 'en_cours' && $mon_jeu_vivant && $est_heure_du_vote): ?>
                <form action="../php/undercover_actions.php" method="POST" style="background:#fff; border:2px solid var(--primary-orange); padding:15px; border-radius:12px; text-align:center; margin:20px 0;">
                    <input type="hidden" name="action" value="voter">
                    <input type="hidden" name="code_room" value="<?= htmlspecialchars($partie['code_room'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="csrf_token" value="<?= $token_csrf ?>">

                    <h3 style="margin-bottom:10px;">🗳️ C'est le moment de voter !</h3>
                    <p style="font-size:13px; color:#666; margin-bottom:15px;">Désignez la personne que vous suspectez d'être l'Undercover.</p>

                    <select name="id_cible" required style="padding:8px; border-radius:8px; margin-bottom:10px;">
                        <option value="" disabled selected>-- Choisir un suspect --</option>
                        <?php foreach ($joueurs as $j): ?>
                            <!-- Un joueur vivant ne peut pas voter contre lui-même -->
                            <?php if ($j['vivant'] && $j['partie_joueur_id'] != $mon_partie_joueur_id): ?>
                                <option value="<?= $j['partie_joueur_id'] ?>"><?= htmlspecialchars($j['pseudo'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-primary" style="display:block; margin:10px auto 0;">Valider mon vote</button>
                </form>
            <?php endif; ?>

            <!-- Bouton Lancer (Hôte seulement) -->
            <?php if ($partie['statut'] === 'attente' && $est_hote): ?>
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
    <?php
    // Calcul des indices, votes et vivants pour le dataset initial
    $stmtCountInd = $pdo->prepare("SELECT COUNT(i.id) FROM indice i JOIN partie_joueur pj ON i.partie_joueur_id = pj.id WHERE pj.partie_id = ?");
    $stmtCountInd->execute([$partie['id']]);
    $total_indices_actuel = (int)$stmtCountInd->fetchColumn();

    $stmtCountVotes = $pdo->prepare("SELECT COUNT(*) FROM vote WHERE partie_id = ?");
    $stmtCountVotes->execute([$partie['id']]);
    $total_votes_actuel = (int)$stmtCountVotes->fetchColumn();

    $stmtCountVivants = $pdo->prepare("SELECT COUNT(*) FROM partie_joueur WHERE partie_id = ? AND vivant = 1");
    $stmtCountVivants->execute([$partie['id']]);
    $total_vivants_actuel = (int)$stmtCountVivants->fetchColumn();
    ?>
    <div id="game-status-data"
        data-code="<?= htmlspecialchars($partie['code_room'], ENT_QUOTES, 'UTF-8') ?>"
        data-statut="<?= htmlspecialchars((string)$partie['statut'], ENT_QUOTES, 'UTF-8') ?>"
        data-joueur="<?= htmlspecialchars((string)($partie['joueur_actuel_id'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>"
        data-tour="<?= htmlspecialchars((string)($partie['tour_actuel'] ?? '1'), ENT_QUOTES, 'UTF-8') ?>"
        data-nbjoueurs="<?= count($joueurs) ?>"
        data-nbvivants="<?= $total_vivants_actuel ?>"
        data-nbindices="<?= $total_indices_actuel ?>"
        data-nbvotes="<?= $total_votes_actuel ?>">
    </div>

    <!-- Inclusion conforme CSP 'self' sinon le script ne fonctionne pas correctement en esperant que ca marche comme ca -->
    <script src="../js/undercover_polling.js"></script>
<?php endif; ?>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>