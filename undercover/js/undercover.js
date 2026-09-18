document.addEventListener('DOMContentLoaded', function() {

    // === CHANGEMENT DE MODE ===
    const modeTags = document.querySelectorAll('.mode-tag');
    modeTags.forEach(tag => {
        tag.addEventListener('click', function() {
            modeTags.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            console.log('Mode sélectionné :', this.textContent.trim());
        });
    });

    // === NOUVELLE PARTIE ===
    const startBtn = document.getElementById('startGame');
    if (startBtn) {
        startBtn.addEventListener('click', function() {
            if (confirm('🔄 Lancer une nouvelle partie ?')) {
                this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Lancement...';
                this.disabled = true;
                setTimeout(() => {
                    this.innerHTML = '<i class="fa-solid fa-play"></i> Nouvelle partie';
                    this.disabled = false;
                    alert('🎮 Nouvelle partie lancée !');
                }, 1500);
            }
        });
    }

    // === INVITER UN AMI ===
    const inviteBtn = document.getElementById('inviteFriend');
    if (inviteBtn) {
        inviteBtn.addEventListener('click', function() {
            const roomCode = document.querySelector('.room-code')?.textContent?.trim() || '263BQH';
            
            // Utiliser l'API Clipboard si disponible
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText('Rejoins ma room Undercover sur Otak\'s Games ! Code : ' + roomCode)
                    .then(() => alert('📋 Code copié dans le presse-papier !'))
                    .catch(() => fallbackCopy(roomCode));
            } else {
                fallbackCopy(roomCode);
            }
        });
    }

    // Fallback pour copier le texte
    function fallbackCopy(roomCode) {
        const dummy = document.createElement('textarea');
        dummy.value = 'Rejoins ma room Undercover sur Otak\'s Games ! Code : ' + roomCode;
        document.body.appendChild(dummy);
        dummy.select();
        try {
            document.execCommand('copy');
            alert('📋 Code copié dans le presse-papier !');
        } catch (e) {
            alert('📋 Code : ' + roomCode + '\nCopie manuelle nécessaire.');
        }
        document.body.removeChild(dummy);
    }

    // === QUITTER LA ROOM ===
    const leaveBtn = document.getElementById('leaveRoom');
    if (leaveBtn) {
        leaveBtn.addEventListener('click', function() {
            if (confirm('⚠️ Voulez-vous vraiment quitter cette room ?')) {
                window.location.href = 'index.php';
            }
        });
    }

    // === EFFET SUR LES CARTES JOUEURS ===
    const playerCards = document.querySelectorAll('.player-card');
    playerCards.forEach(card => {
        card.addEventListener('click', function() {
            const name = this.querySelector('.name')?.textContent?.trim() || 'Joueur';
            if (!this.classList.contains('impostor')) {
                alert('👤 ' + name + ' est un civil.');
            } else {
                alert('🕵️ ' + name + ' est l\'IMPOSTEUR !');
            }
        });
    });

    console.log('🍥 Undercover · Room ' + (document.querySelector('.room-code')?.textContent?.trim() || 'inconnue'));
});