<?php
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/php/security.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$token_csrf = generate_csrf_token();

$stmt = $pdo->prepare("SELECT pseudo, email, nom, prenom, date_naissance, avatar FROM utilisateur WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

//Configuration Header
$page_title = "Mon Profil · Otak's Games";

//Import du Header
require_once __DIR__ . '/includes/header.php';
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
    <link rel="stylesheet" href="style/style.css">
</head>

<body>
    <main class="games-section">
        <div class="profile-card">
            <h2>Mon Profil</h2>

            <?php if (isset($_GET['erreur'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($_GET['erreur']) ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['succes'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_GET['succes']) ?></div>
            <?php endif; ?>

            <form action="php/update_profile.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $token_csrf ?>">
                
                <!-- Aperçu de l'avatar actuel -->
                <div class="current-avatar-preview">
                    <img src="assets/logo_avatar/<?= htmlspecialchars($user['avatar'] ?? 'avatar1.jpg') ?>" alt="Avatar actuel">
                </div>

                <!-- Sélection dynamique des avatars -->
                <div class="input-group">
                    <label>Changer d'avatar <span style="color: var(--primary-orange)">*</span></label>
                    <div class="avatar-selector">
                        <?php
                        $dossier = 'assets/logo_avatar/';
                        $fichiers = glob($dossier . 'avatar*.{jpg,jpeg,png,webp}', GLOB_BRACE);

                        if ($fichiers):
                            foreach ($fichiers as $chemin):
                                $nom = basename($chemin);
                                $checked = ($user['avatar'] === $nom) ? 'checked' : '';
                        ?>
                                <label>
                                    <input type="radio" name="avatar" value="<?= htmlspecialchars($nom) ?>" <?= $checked ?> required>
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

                <!-- Informations personnelles -->
                <div class="input-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
                </div>

                <div class="input-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                </div>

                <div class="input-group">
                    <label for="email">Adresse Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="input-group">
                    <label for="date_naissance">Date de naissance</label>
                    <input type="date" id="date_naissance" name="date_naissance" value="<?= htmlspecialchars($user['date_naissance']) ?>" required>
                </div>

                <div class="input-group">
                    <label for="pseudo">Changer de Pseudo</label>
                    <input type="text" id="pseudo" name="pseudo" value="<?= htmlspecialchars($user['pseudo']) ?>" required>
                </div>

                <hr style="border: 0; border-top: 1px solid #dcd0be; margin: 20px 0;">

                <!-- Modification du mot de passe (optionnel) -->
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
            <p>&copy; 2026 Otak's Games. Tous droits réservés. | Conçu avec <i class="fa-solid fa-heart" style="color: #e05328;"></i> pour la communauté Ehpad No Sekai.</p>
        </div>
    </footer>

</body>

</html>