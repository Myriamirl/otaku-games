<?php
session_start();
$est_connecte = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otak's Games</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style/style.css">
</head>

<body>

    <!-- ENTÊTE / NAVBAR -->
    <header class="navbar">
        <div class="logo-container">
            <svg class="konoha-logo" viewBox="0 0 100 100">
                <path d="M50 10 C 25 10, 10 25, 10 50 C 10 75, 25 90, 50 90 C 75 90, 90 75, 90 50 C 90 35, 80 25, 70 20 C 60 15, 45 20, 40 30 C 35 40, 40 55, 50 60 C 60 65, 70 58, 68 48 C 66 38, 52 38, 50 48" fill="none" stroke="#e05328" stroke-width="8" stroke-linecap="round" />
                <path d="M75 15 L 90 5" stroke="#e05328" stroke-width="8" stroke-linecap="round" />
            </svg>
            <span class="logo-text">Otak’s Games</span>
        </div>

        <nav class="nav-links">
            <a href="index.php" class="nav-item active">Accueil</a>
            <a href="#" class="nav-item"><i class="fa-solid fa-crown"></i> Leaderboard</a>
            <?php if ($est_connecte): ?>
                <a href="pages/profil.php" class="nav-item user-profile-link">
                    <img src="asset/logo_avatar/<?= htmlspecialchars($_SESSION['avatar'] ?? 'avatar1.jpg') ?>" alt="Avatar" class="nav-avatar">
                    <span><?= htmlspecialchars($_SESSION['pseudo']) ?></span>
                </a>
            <?php else: ?>
                <a href="#" class="nav-item trigger-auth">
                    <i class="fa-solid fa-user"></i> Mon compte
                </a>
            <?php endif; ?>
        </nav>

        <div class="nav-actions">
            <button class="search-btn" aria-label="Rechercher"><i class="fa-solid fa-magnifying-glass"></i></button>
            <?php if ($est_connecte): ?>
                <a href="php/deconnexion.php" class="btn-connexion"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
            <?php else: ?>
                <button class="btn-connexion" id="openAuthBtn"><i class="fa-solid fa-right-to-bracket"></i> Connexion</button>
            <?php endif; ?>
        </div>
    </header>

    <!-- BANNIÈRE HERO DE KONOHA -->
    <section class="hero-banner">
        <svg class="wave-svg" viewBox="0 0 1200 480" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <clipPath id="waveClip">
                    <path d="M0,8 C120,22 240,2 360,15 C480,28 600,3 720,12 C840,21 960,5 1080,16 C1140,22 1170,10 1200,8 L1200,472 C1170,465 1140,475 1080,468 C960,460 840,474 720,465 C600,456 480,470 360,462 C240,454 120,466 0,472 Z" />
                </clipPath>
            </defs>
            <image href="asset/logo_avatar/banniere_naruto.jpg" width="1200" height="480" preserveAspectRatio="xMidYMid slice" clip-path="url(#waveClip)" />
        </svg>
    </section>

    <!-- SECTION NOS JEUX -->
    <main class="games-section">
        <div class="section-title-container">
            <svg class="konoha-icon" viewBox="0 0 100 100">
                <path d="M50 10 C 25 10, 10 25, 10 50 C 10 75, 25 90, 50 90 C 75 90, 90 75, 90 50 C 90 35, 80 25, 70 20 C 60 15, 45 20, 40 30 C 35 40, 40 55, 50 60 C 60 65, 70 58, 68 48 C 66 38, 52 38, 50 48" fill="none" stroke="#e05328" stroke-width="8" stroke-linecap="round" />
            </svg>
            <h2>NOS JEUX</h2>
        </div>

        <div class="games-grid">

            <!-- Carte 1 : UNDERCOVER -->
            <div class="game-card">
                <div class="card-img-wrapper">
                    <img src="asset/logo_avatar/logo_undercover.jpg" alt="Undercover" class="game-img">
                    <div class="card-badge" style="background-color: #e05328;">
                        <i class="fa-solid fa-mask"></i>
                    </div>
                </div>
                <div class="card-content">
                    <h3>UNDERCOVER</h3>
                    <p>Sauras-tu démasquer le traître?</p>
                    <?php if ($est_connecte): ?>
                        <a href="pages/undercover.php" class="btn-card">Jouer <i class="fa-solid fa-arrow-right"></i></a>
                    <?php else: ?>
                        <button class="btn-card trigger-auth">Jouer <i class="fa-solid fa-arrow-right"></i></button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Carte 2 : ENCHÈRES -->
            <div class="game-card">
                <div class="card-img-wrapper">
                    <img src="asset/logo_avatar/logo_enchere.jpg" alt="Enchères" class="game-img">
                    <div class="card-badge" style="background-color: #2c7a4a;">
                        <i class="fa-solid fa-gavel"></i>
                    </div>
                </div>
                <div class="card-content">
                    <h3>ENCHÈRES</h3>
                    <p>Mise sur tes objets préférés et fais la meilleure offre !</p>
                    <?php if ($est_connecte): ?>
                        <a href="pages/encheres.php" class="btn-card">Jouer <i class="fa-solid fa-arrow-right"></i></a>
                    <?php else: ?>
                        <button class="btn-card trigger-auth">Jouer <i class="fa-solid fa-arrow-right"></i></button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Carte 3 : COMBAT / ARÈNE -->
            <div class="game-card">
                <div class="card-img-wrapper">
                    <img src="asset/logo_avatar/logo_combat.jpg" alt="Combat / Arène" class="game-img">
                    <div class="card-badge" style="background-color: #b84a2c;">
                        <i class="fa-solid fa-crosshairs"></i>
                    </div>
                </div>
                <div class="card-content">
                    <h3>COMBAT / ARÈNE</h3>
                    <p>Affronte d'autres joueurs et grimpe dans le classement !</p>
                    <?php if ($est_connecte): ?>
                        <a href="pages/combat.php" class="btn-card">Jouer <i class="fa-solid fa-arrow-right"></i></a>
                    <?php else: ?>
                        <button class="btn-card trigger-auth">Jouer <i class="fa-solid fa-arrow-right"></i></button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Carte 4 : RPG / AVENTURE -->
            <div class="game-card">
                <div class="card-img-wrapper">
                    <img src="asset/logo_avatar/logo_rpg_aventure.jpg" alt="RPG / Aventure" class="game-img">
                    <div class="card-badge" style="background-color: #2a6b8c;">
                        <i class="fa-solid fa-compass"></i>
                    </div>
                </div>
                <div class="card-content">
                    <h3>RPG / AVENTURE</h3>
                    <p>Explore Konoha et découvre de nouvelles quêtes !</p>
                    <?php if ($est_connecte): ?>
                        <a href="pages/rpg.php" class="btn-card">Découvrir <i class="fa-solid fa-arrow-right"></i></a>
                    <?php else: ?>
                        <button class="btn-card trigger-auth">Découvrir <i class="fa-solid fa-arrow-right"></i></button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Carte 5 : COLLECTION -->
            <div class="game-card">
                <div class="card-img-wrapper">
                    <img src="asset/logo_avatar/logo_collection.jpg" alt="Collection" class="game-img">
                    <div class="card-badge" style="background-color: #8a6a2c;">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                </div>
                <div class="card-content">
                    <h3>COLLECTION</h3>
                    <p>Collectionne tes personnages, objets et raretés !</p>
                    <?php if ($est_connecte): ?>
                        <a href="pages/collection.php" class="btn-card">Voir la collection <i class="fa-solid fa-arrow-right"></i></a>
                    <?php else: ?>
                        <button class="btn-card trigger-auth">Voir la collection <i class="fa-solid fa-arrow-right"></i></button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Carte 6 : MINI-JEUX -->
            <div class="game-card">
                <div class="card-img-wrapper">
                    <img src="asset/logo_avatar/logo_minijeux.jpg" alt="Mini-Jeux" class="game-img">
                    <div class="card-badge" style="background-color: #6a3a8a;">
                        <i class="fa-solid fa-gamepad"></i>
                    </div>
                </div>
                <div class="card-content">
                    <h3>MINI-JEUX</h3>
                    <p>Des jeux rapides pour gagner des récompenses !</p>
                    <?php if ($est_connecte): ?>
                        <a href="pages/minijeux.php" class="btn-card">Jouer <i class="fa-solid fa-arrow-right"></i></a>
                    <?php else: ?>
                        <button class="btn-card trigger-auth">Jouer <i class="fa-solid fa-arrow-right"></i></button>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

<!-- ===== MODALE CONNEXION / INSCRIPTION ===== -->
    <div id="authModal" class="modal-overlay <?= (!empty($_GET['erreur']) || !empty($_GET['succes'])) ? 'active' : '' ?>">
        <div class="modal-card">
            <button class="close-modal" id="closeAuthBtn">&times;</button>

            <?php if (isset($_GET['erreur'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($_GET['erreur']) ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['succes'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_GET['succes']) ?></div>
            <?php endif; ?>

            <!-- Formulaire de Connexion -->
            <div id="formConnexion">
                <h3>Connexion</h3>
                <form action="php/auth.php" method="POST">
                    <input type="hidden" name="action" value="connexion">
                    <div class="input-group">
                        <label>Pseudo</label>
                        <input type="text" name="pseudo" required>
                    </div>
                    <div class="input-group">
                        <label>Mot de passe</label>
                        <input type="password" name="mot_de_passe" required>
                    </div>
                    <button type="submit" class="btn-submit">Se connecter</button>
                </form>
                <p class="switch-text">Pas de compte ? <a href="#" id="showInscription">S'inscrire</a></p>
            </div>

            <!-- Formulaire d'Inscription -->
            <div id="formInscription" style="display: none;">
                <h3>Inscription</h3>
                <form action="php/auth.php" method="POST">
                    <input type="hidden" name="action" value="inscription">
                    <div class="input-group">
                        <label>Pseudo</label>
                        <input type="text" name="pseudo" required>
                    </div>
                    <div class="input-group">
                        <label>Mot de passe</label>
                        <input type="password" name="mot_de_passe" required>
                    </div>
                    <div class="input-group">
                        <label>Confirmer le mot de passe</label>
                        <input type="password" name="confirmation" required>
                    </div>

                    <!-- Choix de l'avatar dynamique -->
                    <div class="input-group">
                        <label>Choisis ton avatar</label>
                        <div class="avatar-selector">
                            <?php
                            $dossier = 'asset/logo_avatar/';
                            $avatars = glob($dossier . 'avatar*.{jpg,jpeg,png,webp}', GLOB_BRACE);
                            if ($avatars):
                                foreach ($avatars as $index => $chemin):
                                    $nom = basename($chemin);
                                    $checked = ($index === 0) ? 'checked' : '';
                            ?>
                                    <label>
                                        <input type="radio" name="avatar" value="<?= htmlspecialchars($nom) ?>" <?= $checked ?>>
                                        <img src="<?= htmlspecialchars($chemin) ?>" alt="Avatar">
                                    </label>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">Créer mon compte</button>
                </form>
                <p class="switch-text">Déjà un compte ? <a href="#" id="showConnexion">Se connecter</a></p>
            </div>
        </div>
    </div>

        <!-- PIED DE PAGE -->
        <footer class="footer">
            <div class="footer-bottom">
                <p>&copy; 2026 Otak's Games. Tous droits réservés. | Conçu avec <i class="fa-solid fa-heart" style="color: #e05328;"></i> pour la communauté Ehpad No Sekai.</p>
            </div>
        </footer>

        <script src="js/script.js"></script>
</body>

</html>