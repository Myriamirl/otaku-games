<?php
// Simuler une session pour le front
session_start();
$is_connected = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? 'Invité';
$page_title = "Undercover · Otak's Games";

// Données fictives pour la room
$room_code = "263BQH";
$word = "Pizza";
$current_player = "Danielson";
$players = [
    ['name' => 'Danielson', 'role' => 'impostor', 'alive' => true, 'turn' => true],
    ['name' => 'Maxime', 'role' => 'civil', 'alive' => true, 'turn' => false],
    ['name' => 'Lisa', 'role' => 'civil', 'alive' => false, 'turn' => false],
    ['name' => 'Sakura', 'role' => 'civil', 'alive' => true, 'turn' => false],
    ['name' => 'Naruto', 'role' => 'civil', 'alive' => true, 'turn' => false],
];
$mode = 'Original'; // Original, Élimination, Arbitre
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/undercover.css">
</head>
<body>

    <!-- ===== HEADER ===== -->
    <header class="navbar">
        <div class="logo-container">
            <svg class="konoha-logo" viewBox="0 0 100 100">
                <path d="M50 10 C 25 10, 10 25, 10 50 C 10 75, 25 90, 50 90 C 75 90, 90 75, 90 50 C 90 35, 80 25, 70 20 C 60 15, 45 20, 40 30 C 35 40, 40 55, 50 60 C 60 65, 70 58, 68 48 C 66 38, 52 38, 50 48" fill="none" stroke="#e05328" stroke-width="8" stroke-linecap="round"/>
                <path d="M75 15 L 90 5" stroke="#e05328" stroke-width="8" stroke-linecap="round"/>
            </svg>
            <span class="logo-text">Otak's Games</span>
        </div>
        <nav class="nav-links">
            <a href="index.php" class="nav-item">Accueil</a>
            <a href="leaderboard.php" class="nav-item"><i class="fa-solid fa-crown"></i> Leaderbord</a>
            <a href="mon-compte.php" class="nav-item"><i class="fa-solid fa-user"></i> Mon compte</a>
        </nav>
        <div class="nav-actions">
            <button class="search-btn" aria-label="Rechercher"><i class="fa-solid fa-magnifying-glass"></i></button>
            <?php if ($is_connected): ?>
                <span style="color: #f5d27a; font-size:0.9rem; margin-right: 10px;">
                    <i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($username); ?>
                </span>
                <a href="deconnexion.php" class="btn-connexion" style="background: #8a7ab8; box-shadow: 0 4px 0 #5b4b7a;">
                    <i class="fa-solid fa-sign-out-alt"></i> Déconnexion
                </a>
            <?php else: ?>
                <button class="btn-connexion"><i class="fa-solid fa-right-to-bracket"></i> Connexion</button>
            <?php endif; ?>
        </div>
    </header>

    <!-- ===== PAGE UNDERCOVER ===== -->
    <main class="undercover-container">

        <div class="game-panel">

            <!-- En-tête -->
            <div class="game-header">
                <h1 class="game-title">
                    <i class="fa-solid fa-mask"></i> UNDERCOVER
                </h1>
                <div class="room-code">
                    <i class="fa-solid fa-hashtag"></i> <?php echo $room_code; ?>
                </div>
            </div>

            <!-- Modes de jeu -->
            <div class="game-modes">
                <span class="mode-tag <?php echo $mode === 'Original' ? 'active' : ''; ?>" data-mode="original">
                    <i class="fa-solid fa-bolt"></i> Original
                </span>
                <span class="mode-tag <?php echo $mode === 'Élimination' ? 'active' : ''; ?>" data-mode="elimination">
                    <i class="fa-solid fa-skull"></i> Élimination
                </span>
                <span class="mode-tag <?php echo $mode === 'Arbitre' ? 'active' : ''; ?>" data-mode="arbitre">
                    <i class="fa-solid fa-gavel"></i> Arbitre
                </span>
            </div>

            <!-- Mot à faire deviner -->
            <div class="word-display">
                <div class="label">🔎 Ton mot</div>
                <div class="word"><?php echo $word; ?></div>
                <div class="sub">👀 Fais deviner sans utiliser le mot</div>
            </div>

            <!-- Liste des joueurs -->
            <div class="players-grid">
                <?php foreach ($players as $player): ?>
                    <div class="player-card 
                        <?php echo $player['role'] === 'impostor' ? 'impostor' : ''; ?>
                        <?php echo $player['turn'] ? 'turn' : ''; ?>
                    ">
                        <div class="avatar" style="<?php echo $player['role'] === 'impostor' ? 'background: #e05328; color: #fff;' : ''; ?>">
                            <?php echo strtoupper(substr($player['name'], 0, 1)); ?>
                        </div>
                        <div class="name">
                            <?php echo htmlspecialchars($player['name']); ?>
                            <?php if ($player['role'] === 'impostor'): ?>
                                <span style="font-size: 10px; color: #e05328;">🕵️</span>
                            <?php endif; ?>
                        </div>
                        <div class="status <?php echo $player['alive'] ? 'alive' : 'dead'; ?>">
                            <i class="fa-solid fa-circle" style="font-size: 8px;"></i>
                            <?php echo $player['alive'] ? 'En vie' : 'Éliminé'; ?>
                        </div>
                        <?php if ($player['turn']): ?>
                            <div class="turn-badge"><i class="fa-solid fa-turn-up"></i> À toi</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Légende -->
            <div class="legend">
                <span class="legend-item">
                    <span class="dot impostor"></span> Imposteur
                </span>
                <span class="legend-item">
                    <span class="dot turn"></span> Tour actuel
                </span>
                <span class="legend-item">
                    <span class="dot dead"></span> Éliminé
                </span>
            </div>

            <!-- Actions -->
            <div class="game-actions">
                <button class="btn-primary" id="startGame">
                    <i class="fa-solid fa-play"></i> Nouvelle partie
                </button>
                <button class="btn-secondary" id="inviteFriend">
                    <i class="fa-solid fa-user-plus"></i> Inviter un ami
                </button>
                <button class="btn-danger" id="leaveRoom">
                    <i class="fa-solid fa-arrow-left"></i> Quitter la room
                </button>
            </div>

            <a href="index.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Retour à l'accueil
            </a>

        </div>

    </main>

    <!-- ===== PIED DE PAGE ===== -->
    <footer class="footer">
        <div class="footer-bottom">
            <p>&copy; 2026 Otak's Games. Tous droits réservés. | Conçu avec <i class="fa-solid fa-heart" style="color: #e05328;"></i> pour la communauté otaku.</p>
        </div>
    </footer>

    <script src="script/script.js"></script>
    <script src="script/undercover.js"></script>
</body>
</html>