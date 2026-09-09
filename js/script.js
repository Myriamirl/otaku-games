document.addEventListener('DOMContentLoaded', () => {
    // Gestion du changement d'onglet actif
    const navItems = document.querySelectorAll('.nav-item');

    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            navItems.forEach(nav => nav.classList.remove('active'));
            item.classList.add('active');
        });
    });

    // Effet au clic sur les boutons "Jouer"
    const playButtons = document.querySelectorAll('.btn-card');
    playButtons.forEach(button => {
        button.addEventListener('click', () => {
            const gameTitle = button.parentElement.querySelector('h3').innerText;
            console.log(`Lancement du jeu : ${gameTitle}`);
        });
    });
});