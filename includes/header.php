<?php
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../php/security.php';

$est_connecte = isset($_SESSION['user_id']);
$token_csrf   = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') : "Otak's Games" ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Global -->
    <link rel="stylesheet" href="/style/style.css">
    
    <!-- CSS Spécifique à la page si défini -->
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($page_css, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
</head>
<body class="<?= isset($body_class) ? htmlspecialchars($body_class, ENT_QUOTES, 'UTF-8') : '' ?>">

<header class="navbar">
    <div class="logo-container">
        <svg class="konoha-logo" viewBox="0 0 100 100">
            <path d="M50 10 C 25 10, 10 25, 10 50 C 10 75, 25 90, 50 90 C 75 90, 90 75, 90 50 C 90 35, 80 25, 70 20 C 60 15, 45 20, 40 30 C 35 40, 40 55, 50 60 C 60 65, 70 58, 68 48 C 66 38, 52 38, 50 48" fill="none" stroke="#e05328" stroke-width="8" stroke-linecap="round" />
            <path d="M75 15 L 90 5" stroke="#e05328" stroke-width="8" stroke-linecap="round" />
        </svg>
        <span class="logo-text">Otak’s Games</span>
    </div>

    <nav class="nav-links">
        <a href="/index.php" class="nav-item">Accueil</a>
        <a href="#" class="nav-item"><i class="fa-solid fa-crown"></i> Leaderboard</a>
        <?php if ($est_connecte): ?>
            <a href="/profil.php" class="nav-item user-profile-link">
                <img src="/assets/logo_avatar/<?= htmlspecialchars($_SESSION['avatar'] ?? 'avatar1.jpg', ENT_QUOTES, 'UTF-8') ?>" alt="Avatar" class="nav-avatar">
                <span><?= htmlspecialchars($_SESSION['pseudo'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        <?php else: ?>
            <a href="#" class="nav-item trigger-auth"><i class="fa-solid fa-user"></i> Mon compte</a>
        <?php endif; ?>
    </nav>

    <div class="nav-actions">
        <?php if ($est_connecte): ?>
            <a href="/php/deconnexion.php" class="btn-connexion"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
        <?php else: ?>
            <button class="btn-connexion" id="openAuthBtn"><i class="fa-solid fa-right-to-bracket"></i> Connexion</button>
        <?php endif; ?>
    </div>
</header>