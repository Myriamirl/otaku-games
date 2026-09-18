<?php
session_start();
require_once __DIR__ . '/../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$stmt = $pdo->prepare("SELECT pseudo, avatar FROM UTILISATEUR WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Otak's Games</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
</head>

<body>

    <header class="navbar">
        <div class="logo-container">
            <svg class="konoha-logo" viewBox="0 0 100 100">
                <path d="M50 10 C 25 10, 10 25, 10 50 C 10 75, 25 90, 50 90 C 75 90, 90 75, 90 50 C 90 35, 80 25, 70 20 C 60 15, 45 20, 40 30 C 35 40, 40 55, 50 60 C 60 65, 70 58, 68 48 C 66 38, 52 38, 50 48" fill="none" stroke="#e05328" stroke-width="8" stroke-linecap="round" />
                <path d="M75 15 L 90 5" stroke="#e05328" stroke-width="8" stroke-linecap="round" />
            </svg>
            <span class="logo-text">Otak’s Games</span>
        </div>

        <nav class="nav-links">
            <a href="../index.php" class="nav-item">Accueil</a>
            <a href="profil.php" class="nav-item active"><i class="fa-solid fa-user"></i> Mon compte</a>
        </nav>

        <div class="nav-actions">
            <a href="../php/deconnexion.php" class="btn-connexion"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
        </div>
    </header>

    <main class="games-section">
        <div class="profile-card">
            <h2>Mon Profil</h2>

            <?php if (isset($_GET['erreur'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($_GET['erreur']) ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['succes'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_GET['succes']) ?></div>
            <?php endif; ?>

            <form action="../php/update_profile.php" method="POST">
                <!-- Aperçu de l'avatar actuel -->
                <div class="current-avatar-preview">
                    <img src="../asset/logo_avatar/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar actuel">
                </div>

                <!-- Sélection dynamique de TOUS les avatars -->
                <div class="input-group">
                    <label>Changer d'avatar</label>
                    <div class="avatar-selector">
                        <?php
                        $dossier = '../asset/logo_avatar/';
                        // Récupère toutes les images (.jpg, .jpeg, .png, .webp) du dossier
                        $fichiers = glob($dossier . 'avatar*.{jpg,jpeg,png,webp}', GLOB_BRACE);

                        if ($fichiers):
                            foreach ($fichiers as $chemin):
                                $nom = basename($chemin);
                                // On coche l'avatar actuellement attribué au joueur
                                $checked = ($user['avatar'] === $nom) ? 'checked' : '';
                        ?>
                                <label>
                                    <input type="radio" name="avatar" value="<?= htmlspecialchars($nom) ?>" <?= $checked ?>>
                                    <img src="<?= htmlspecialchars($chemin) ?>" alt="Avatar">
                                </label>
                            <?php
                            endforeach;
                        else:
                            ?>
                            <p style="color: var(--text-muted); font-size: 12px;">Aucun avatar trouvé dans le dossier.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Modification du pseudo -->
                <div class="input-group">
                    <label for="pseudo">Changer de Pseudo</label>
                    <input type="text" id="pseudo" name="pseudo" value="<?= htmlspecialchars($user['pseudo']) ?>" required>
                </div>

                <hr style="border: 0; border-top: 1px solid #dcd0be; margin: 20px 0;">

                <!-- Modification du mot de passe -->
                <div class="input-group">
                    <label for="nouveau_mdp">Nouveau mot de passe (laisser vide pour conserver l'actuel)</label>
                    <input type="password" id="nouveau_mdp" name="nouveau_mdp">
                </div>

                <div class="input-group">
                    <label for="confirm_mdp">Confirmer le nouveau mot de passe</label>
                    <input type="password" id="confirm_mdp" name="confirm_mdp">
                </div>

                <button type="submit" class="btn-submit">Enregistrer les modifications</button>
            </form>

            <div class="input-group">
                <label for="pseudo">Changer de Pseudo</label>
                <input type="text" id="pseudo" name="pseudo" value="<?= htmlspecialchars($user['pseudo']) ?>" required>
            </div>

            <hr style="border: 0; border-top: 1px solid #dcd0be; margin: 20px 0;">

            <div class="input-group">
                <label for="nouveau_mdp">Nouveau mot de passe (laisser vide pour conserver l'actuel)</label>
                <input type="password" id="nouveau_mdp" name="nouveau_mdp">
            </div>

            <div class="input-group">
                <label for="confirm_mdp">Confirmer le nouveau mot de passe</label>
                <input type="password" id="confirm_mdp" name="confirm_mdp">
            </div>

            <button type="submit" class="btn-submit">Enregistrer les modifications</button>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-bottom">
            <p>&copy; 2026 Otak's Games. Tous droits réservés.</p>
        </div>
    </footer>

</body>

</html>