document.addEventListener('DOMContentLoaded', () => {
    const gameStatusEl = document.getElementById('game-status-data');
    if (!gameStatusEl) return;

    const gameData = gameStatusEl.dataset;

    setInterval(() => {
        fetch(`../php/check_statut.php?code=${encodeURIComponent(gameData.code)}`, {
            credentials: 'same-origin'
        })
        .then(res => {
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (!data || data.statut === 'erreur') return;

            const statutChange    = String(data.statut) !== String(gameData.statut);
            const joueurChange    = String(data.joueur_actuel_id) !== String(gameData.joueur);
            const tourChange      = String(data.tour_actuel) !== String(gameData.tour);
            const nbJoueursChange = Number(data.nb_joueurs) !== Number(gameData.nbjoueurs);
            const nbVivantsChange = Number(data.nb_vivants) !== Number(gameData.nbvivants);
            const nbIndicesChange = Number(data.nb_indices) !== Number(gameData.nbindices);
            const nbVotesChange   = Number(data.nb_votes) !== Number(gameData.nbvotes);

            // Dès qu'une modification survient en BDD -> rechargement automatique
            if (statutChange || joueurChange || tourChange || nbJoueursChange || nbVivantsChange || nbIndicesChange || nbVotesChange) {
                window.location.reload();
            }
        })
        .catch(err => console.error("Polling Error:", err));
    }, 2000);
});