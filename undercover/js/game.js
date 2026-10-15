(function () {
  "use strict";
  const OG = window.OtakuGames;

  /* ---------- Données de démo (remplacées par le polling PHP en phase 2) ----------
     PHASE 2 : setInterval(async () => {
       const state = await OG.apiRequest("poll_state", { room_code });
       renderPlayers(state.players);
       renderTimer(state.seconds_left);
       ...
     }, 2500);
  ------------------------------------------------------------------------------- */

  const myPseudo = OG.readSession(OG.STORAGE_KEYS.pseudo) || "Toi";

  let players = [
    { id: 1, pseudo: myPseudo, alive: true, isMe: true },
    { id: 2, pseudo: "Saya", alive: true },
    { id: 3, pseudo: "Renji", alive: true },
    { id: 4, pseudo: "Aoi", alive: true },
    { id: 5, pseudo: "Kenta", alive: false },
  ];
  let currentTurnIndex = 0;

  const playersList = document.getElementById("players-list");
  const turnIndicator = document.getElementById("turn-indicator");
  const clueForm = document.getElementById("clue-form");
  const clueInput = document.getElementById("clue-input");
  const clueSubmit = document.getElementById("clue-submit");
  const turnPanel = document.getElementById("turn-panel");
  const votePanel = document.getElementById("vote-panel");
  const voteOptions = document.getElementById("vote-options");
  const voteSubmit = document.getElementById("vote-submit");

  function buildPlayerRow(player, index) {
    const row = document.createElement("div");
    row.className = "player-row";
    if (index === currentTurnIndex && player.alive) row.classList.add("is-current-turn");
    if (!player.alive) row.classList.add("is-eliminated");

    const avatar = document.createElement("div");
    avatar.className = "avatar";
    OG.setText(avatar, OG.initialsOf(player.pseudo));

    const info = document.createElement("div");
    info.className = "player-info";

    const name = document.createElement("div");
    name.className = "player-name";
    OG.setText(name, player.pseudo + (player.isMe ? " (toi)" : ""));

    const meta = document.createElement("div");
    meta.className = "player-meta";
    OG.setText(meta, !player.alive ? "Éliminé" : (index === currentTurnIndex ? "En train de parler" : "En attente"));

    info.appendChild(name);
    info.appendChild(meta);
    row.appendChild(avatar);
    row.appendChild(info);
    return row;
  }

  function renderPlayers() {
    playersList.replaceChildren();
    players.forEach((p, i) => playersList.appendChild(buildPlayerRow(p, i)));
  }

  function currentPlayer() {
    return players[currentTurnIndex];
  }

  function renderTurn() {
    const player = currentPlayer();
    const isMyTurn = player && player.isMe;
    OG.setText(turnIndicator, isMyTurn ? "Ton tour" : `Tour de ${player ? player.pseudo : "?"}`);
    clueInput.disabled = !isMyTurn;
    clueSubmit.disabled = !isMyTurn;
    OG.setText(clueSubmit, isMyTurn ? "Valider mon indice" : "En attente de ton tour");
  }

  function nextTurn() {
    let attempts = 0;
    do {
      currentTurnIndex = (currentTurnIndex + 1) % players.length;
      attempts += 1;
    } while (!players[currentTurnIndex].alive && attempts <= players.length);

    if (attempts > players.length) {
      startVote();
      return;
    }

    if (currentTurnIndex === 0) {
      // Un tour complet est passé : on ouvre le vote (démo simplifiée)
      startVote();
      return;
    }

    renderPlayers();
    renderTurn();
  }

  clueForm.addEventListener("submit", (event) => {
    event.preventDefault();
    if (clueSubmit.disabled) return;
    const value = clueInput.value.trim();
    if (!value) {
      OG.showToast("Écris un indice avant de valider.", "error");
      return;
    }
    // PHASE 2 : OG.apiRequest("submit_clue", { room_code, clue: value })
    clueInput.value = "";
    OG.showToast("Indice envoyé.");
    nextTurn();
  });

  /* ---------- Vote ---------- */

  let selectedVoteId = null;

  function startVote() {
    turnPanel.style.display = "none";
    votePanel.style.display = "block";
    renderVoteOptions();
  }

  function renderVoteOptions() {
    voteOptions.replaceChildren();
    players
      .filter((p) => p.alive && !p.isMe)
      .forEach((p) => {
        const option = document.createElement("button");
        option.type = "button";
        option.className = "toggle-option";
        option.setAttribute("aria-pressed", "false");
        option.dataset.playerId = String(p.id);

        const strong = document.createElement("strong");
        OG.setText(strong, p.pseudo);
        const span = document.createElement("span");
        OG.setText(span, "Désigner comme suspect");

        option.appendChild(strong);
        option.appendChild(span);

        option.addEventListener("click", () => {
          voteOptions.querySelectorAll(".toggle-option").forEach((o) => o.setAttribute("aria-pressed", "false"));
          option.setAttribute("aria-pressed", "true");
          selectedVoteId = p.id;
          voteSubmit.disabled = false;
        });

        voteOptions.appendChild(option);
      });
  }

  voteSubmit.addEventListener("click", () => {
    if (!selectedVoteId) return;
    // PHASE 2 : OG.apiRequest("submit_vote", { room_code, target_id: selectedVoteId })
    OG.showToast("Vote enregistré. En attente des autres joueurs…");
    voteSubmit.disabled = true;
  });

  /* ---------- Mot ---------- */

  const wordCard = document.getElementById("word-card");
  const wordValue = document.getElementById("word-value");

  function toggleWord() {
    const hidden = wordValue.classList.toggle("is-hidden");
    wordCard.setAttribute("aria-pressed", String(!hidden));
  }

  wordCard.addEventListener("click", toggleWord);
  wordCard.addEventListener("keydown", (event) => {
    if (event.key === "Enter" || event.key === " ") {
      event.preventDefault();
      toggleWord();
    }
  });

  /* ---------- Timer (démo) ---------- */

  const timerRing = document.getElementById("timer-ring");
  let secondsLeft = 30;

  const timerHandle = setInterval(() => {
    secondsLeft -= 1;
    if (secondsLeft <= 0) {
      clearInterval(timerHandle);
      OG.setText(timerRing, "0");
      return;
    }
    OG.setText(timerRing, secondsLeft);
    timerRing.classList.toggle("is-low", secondsLeft <= 10);
  }, 1000);

  renderPlayers();
  renderTurn();
})();
