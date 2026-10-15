<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

// Récupération des univers disponibles
$stmtUnivers = $pdo->query("SELECT id, nom FROM univers");
$univers_list = $stmtUnivers->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Undercover · Otak's Games</title>
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

        <!-- Contenu Central -->
        <div class="game-panel">
            <div class="game-header">
                <h1 class="game-title"><i class="fa-solid fa-mask"></i> UNDERCOVER</h1>
            </div>

            <?php if (isset($_GET['erreur'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($_GET['erreur']) ?></div>
            <?php endif; ?>

            <div class="game-actions" style="flex-direction: column; max-width: 400px; margin: 20px auto;">

                <!-- Formulaire : Créer une partie -->
                <form action="../php/creer_partie.php" method="POST" style="width: 100%;">
                    <div class="input-group">
                        <label>Choisir un univers</label>
                        <select name="univers_id" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid #dcd0be;">
                            <?php foreach ($univers_list as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group" style="margin-top: 15px;">
                        <label>Nombre d'Undercovers</label>
                        <select name="nb_undercover" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid #dcd0be;">
                            <option value="" disabled selected>-- Nombre d'imposteurs --</option>
                            <option value="1">1 Undercover</option>
                            <option value="2">2 Undercovers</option>
                            <option value="3">3 Undercovers</option>
                        </select>
                    </div>

                    <div class="input-group" style="margin-top: 15px;">
                        <label>Nombre de tours total</label>
                        <select name="nb_tours" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid #dcd0be;">
                            <option value="" disabled selected>-- Choisir le nombre de tours --</option>
                            <?php for ($i = 2; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> Tours</option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="input-group" style="margin-top: 15px;">
                        <label>Vote d'élimination</label>
                        <select name="tours_vote" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid #dcd0be;">
                            <option value="" disabled selected>-- Fréquence des votes --</option>
                            <option value="1">À chaque tour</option>
                            <option value="2">Tous les 2 tours</option>
                            <option value="3">Tous les 3 tours</option>
                            <option value="4">Tous les 4 tours</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; margin-top: 20px;">
                        <i class="fa-solid fa-plus"></i> Créer un salon
                    </button>
                </form>

                <hr style="border: 0; border-top: 1px solid #dcd0be; margin: 25px 0; width: 100%;">

                <!-- Formulaire : Rejoindre une partie -->
                <form action="../php/rejoindre_partie.php" method="POST" style="width: 100%;">
                    <div class="input-group">
                        <label>Rejoindre avec un code</label>
                        <input type="text" name="code_room" placeholder="Ex: 4B9Z1A" maxlength="6" style="text-transform: uppercase; text-align: center; font-weight: 700; letter-spacing: 2px;" required>
                    </div>
                    <button type="submit" class="btn-secondary" style="width: 100%; margin-top: 10px;">
                        <i class="fa-solid fa-right-to-bracket"></i> Rejoindre la room
                    </button>
                </form>

            </div>

            <a href="../../index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Retour à l'accueil</a>
        </div>


    </main>

    <footer class="footer">
        <div class="footer-bottom">
            <p>&copy; 2026 Otak's Games. Tous droits réservés.| Conçu avec <i class="fa-solid fa-heart" style="color: #e05328;"></i> pour la communauté Ehpad No Sekai.</p>
        </div>
    </footer>
</body>

</html>