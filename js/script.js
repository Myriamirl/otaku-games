document.addEventListener('DOMContentLoaded', () => {
    // ----------------------------------------------------
    // GESTION DE LA MODALE CONNEXION / INSCRIPTION
    // ----------------------------------------------------
    const authModal = document.getElementById('authModal');
    const openAuthBtn = document.getElementById('openAuthBtn');
    const closeAuthBtn = document.getElementById('closeAuthBtn');
    const triggerBtns = document.querySelectorAll('.trigger-auth');

    const formConnexion = document.getElementById('formConnexion');
    const formInscription = document.getElementById('formInscription');
    const showInscription = document.getElementById('showInscription');
    const showConnexion = document.getElementById('showConnexion');

    // Ouvrir la modale (au clic sur Connexion ou sur un bouton Jouer de carte si non connecté)
    if (openAuthBtn) {
        openAuthBtn.addEventListener('click', () => authModal.classList.add('active'));
    }
    
    triggerBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            authModal.classList.add('active');
        });
    });

    // Fermer la modale au clic sur la croix ou à l'extérieur
    if (closeAuthBtn) {
        closeAuthBtn.addEventListener('click', () => authModal.classList.remove('active'));
    }
    
    window.addEventListener('click', (e) => {
        if (e.target === authModal) {
            authModal.classList.remove('active');
        }
    });

    // Basculer entre Connexion et Inscription
    if (showInscription && showConnexion) {
        showInscription.addEventListener('click', (e) => {
            e.preventDefault();
            formConnexion.style.display = 'none';
            formInscription.style.display = 'block';
        });

        showConnexion.addEventListener('click', (e) => {
            e.preventDefault();
            formInscription.style.display = 'none';
            formConnexion.style.display = 'block';
        });
    }

    // ----------------------------------------------------
    // INTERACTIONS CARTES DE JEUX
    // ----------------------------------------------------
    const gameCards = document.querySelectorAll('.game-card');

    gameCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
        });
    });
});

// Nettoie l'URL des paramètres d'erreur/succès après le chargement
if (window.location.search.includes('erreur=') || window.location.search.includes('succes=')) {
    window.history.replaceState({}, document.title, window.location.pathname);
}