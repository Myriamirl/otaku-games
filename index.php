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
                <path d="M50 10 C 25 10, 10 25, 10 50 C 10 75, 25 90, 50 90 C 75 90, 90 75, 90 50 C 90 35, 80 25, 70 20 C 60 15, 45 20, 40 30 C 35 40, 40 55, 50 60 C 60 65, 70 58, 68 48 C 66 38, 52 38, 50 48" fill="none" stroke="#e05328" stroke-width="8" stroke-linecap="round"/>
                <path d="M75 15 L 90 5" stroke="#e05328" stroke-width="8" stroke-linecap="round"/>
            </svg>
            <span class="logo-text">Otak’s Games</span>
        </div>

        <nav class="nav-links">
            <a href="#" class="nav-item active">Accueil</a>
            <a href="#" class="nav-item"><i class="fa-solid fa-crown"></i> Leaderbord</a>
            <a href="#" class="nav-item"><i class="fa-solid fa-user"></i> Mon compte</a>
        </nav>

        <div class="nav-actions">
            <button class="search-btn" aria-label="Rechercher"><i class="fa-solid fa-magnifying-glass"></i></button>
            <button class="btn-connexion"><i class="fa-solid fa-right-to-bracket"></i> Connexion</button>
        </div>
    </header>

    <!-- BANNIÈRE HERO DE KONOHA -->
<section class="hero-banner">
    <svg class="wave-svg" viewBox="0 0 1200 480" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <!-- Masque avec vagues -->
            <clipPath id="waveClip">
                <path d="
    M0,8 
    C120,22 240,2 360,15 
    C480,28 600,3 720,12 
    C840,21 960,5 1080,16 
    C1140,22 1170,10 1200,8 
    L1200,472
    C1170,465 1140,475 1080,468 
    C960,460 840,474 720,465 
    C600,456 480,470 360,462 
    C240,454 120,466 0,472 
    Z
                "/>
            </clipPath>
        </defs>
        <!-- Image avec le masque -->
        <image href="asset\logo_avatar\banniere_naruto.jpg" width="1200" height="480" preserveAspectRatio="xMidYMid slice" clip-path="url(#waveClip)"/>
    </svg>
</section>

    <!-- SECTION NOS JEUX -->
    <main class="games-section">
        <div class="section-title-container">
            <svg class="konoha-icon" viewBox="0 0 100 100">
                <path d="M50 10 C 25 10, 10 25, 10 50 C 10 75, 25 90, 50 90 C 75 90, 90 75, 90 50 C 90 35, 80 25, 70 20 C 60 15, 45 20, 40 30 C 35 40, 40 55, 50 60 C 60 65, 70 58, 68 48 C 66 38, 52 38, 50 48" fill="none" stroke="#e05328" stroke-width="8" stroke-linecap="round"/>
            </svg>
            <h2>NOS JEUX</h2>
        </div>

        <div class="games-grid">

            <!-- Carte 1 : UNDERCOVER -->
            <div class="game-card">
                <div class="card-img-wrapper">
                    <img src="asset\logo_avatar\logo_undercover.jpg" alt="Undercover" class="game-img">
                    <div class="card-badge" style="background-color: #e05328;">
                        <i class="fa-solid fa-mask"></i>
                    </div>
                </div>
                <div class="card-content">
                    <h3>UNDERCOVER</h3>
                    <p>Sauras-tu démasquer le traître?</p>
                    <a href="undercover.php" class="btn-card">Jouer <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            </div>

            <!-- Carte 2 : ENCHÈRES -->
            <div class="game-card">
                <div class="card-img-wrapper">
                    <img src="asset\logo_avatar\logo_enchere.jpg" alt="Enchères" class="game-img">
                    <div class="card-badge" style="background-color: #2c7a4a;">
                        <i class="fa-solid fa-gavel"></i>
                    </div>
                </div>
                <div class="card-content">
                    <h3>ENCHÈRES</h3>
                    <p>Mise sur tes objets préférés et fais la meilleure offre !</p>
                    <button class="btn-card">Jouer <i class="fa-solid fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- Carte 3 : COMBAT / ARÈNE -->
            <div class="game-card">
                <div class="card-img-wrapper">
                    <img src="asset\logo_avatar\logo_combat.jpg" alt="Combat / Arène" class="game-img">
                    <div class="card-badge" style="background-color: #b84a2c;">
                        <i class="fa-solid fa-crosshairs"></i> 
                    </div>
                </div>
                <div class="card-content">
                    <h3>COMBAT / ARÈNE</h3>
                    <p>Affronte d'autres joueurs et grimpe dans le classement !</p>
                    <button class="btn-card">Jouer <i class="fa-solid fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- Carte 4 : RPG / AVENTURE -->
            <div class="game-card">
                <div class="card-img-wrapper">
                    <img src="asset\logo_avatar\logo_rpg_aventure.jpg" alt="RPG / Aventure" class="game-img">
                    <div class="card-badge" style="background-color: #2a6b8c;">
                        <i class="fa-solid fa-compass"></i>
                    </div>
                </div>
                <div class="card-content">
                    <h3>RPG / AVENTURE</h3>
                    <p>Explore Konoha et découvre de nouvelles quêtes !</p>
                    <button class="btn-card">Découvrir <i class="fa-solid fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- Carte 5 : COLLECTION -->
            <div class="game-card">
                <div class="card-img-wrapper">
                    <img src="asset\logo_avatar\logo_collection.jpg" alt="Collection" class="game-img">
                    <div class="card-badge" style="background-color: #8a6a2c;">
                        <i class="fa-solid fa-layer-group"></i> 
                    </div>
                </div>
                <div class="card-content">
                    <h3>COLLECTION</h3>
                    <p>Collectionne tes personnages, objets et raretés !</p>
                    <button class="btn-card">Voir la collection <i class="fa-solid fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- Carte 6 : MINI-JEUX -->
            <div class="game-card">
                <div class="card-img-wrapper">
                    <img src="asset\logo_avatar\logo_minijeux.jpg" alt="Mini-Jeux" class="game-img">
                    <div class="card-badge" style="background-color: #6a3a8a;">
                        <i class="fa-solid fa-gamepad"></i>
                    </div>
                </div>
                <div class="card-content">
                    <h3>MINI-JEUX</h3>
                    <p>Des jeux rapides pour gagner des récompenses !</p>
                    <button class="btn-card">Jouer <i class="fa-solid fa-arrow-right"></i></button>
                </div>
            </div>

        </div>
    </main>

    <script src="script/script.js"></script>

        <!-- ===== PIED DE PAGE ===== -->
    <footer class="footer">
        <div class="footer-bottom">
            <p>&copy; 2026 Otak's Games. Tous droits réservés. | Conçu avec <i class="fa-solid fa-heart" style="color: #e05328;"></i> pour la communauté Ehpad No Sekai.</p>
        </div>
    </footer>
</body>
</html>