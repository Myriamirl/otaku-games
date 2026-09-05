(function () {
  "use strict";
  const OG = window.OtakuGames;

  /* SÉCURITÉ (rappel pour la phase PHP) :
     Cette page ne doit être accessible QUE si la session serveur
     confirme que l'utilisateur a le rôle "hokage" pour CETTE room.
     Le contrôle doit se faire en PHP au chargement de la page
     (redirection si non autorisé), jamais uniquement en JS —
     un contrôle uniquement côté client peut être contourné. */

  const players = [
    { pseudo: "Saya", role: "ninja", word: "Ramen", alive: true, turn: false },
    { pseudo: "Renji", role: "espion", word: "Dango", alive: true, turn: true },
    { pseudo: "Aoi", role: "ninja", word: "Ramen", alive: true, turn: false },
    { pseudo: "Kenta", role: "ninja", word: "Ramen", alive: false, turn: false },
    { pseudo: "Mika", role: "ninja", word: "Ramen", alive: true, turn: false },
  ];

  const tbody = document.getElementById("admin-table-body");

  function buildRow(player) {
    const tr = document.createElement("tr");
    if (!player.alive) tr.style.opacity = "0.45";

    const tdName = document.createElement("td");
    OG.setText(tdName, player.pseudo);

    const tdRole = document.createElement("td");
    const rolePill = document.createElement("span");
    rolePill.className = player.role === "espion" ? "pill pill-deep" : "pill";
    OG.setText(rolePill, player.role === "espion" ? "Espion" : "Ninja");
    tdRole.appendChild(rolePill);

    const tdWord = document.createElement("td");
    tdWord.className = "word-reveal";
    OG.setText(tdWord, player.word);

    const tdStatus = document.createElement("td");
    OG.setText(tdStatus, player.alive ? "En vie" : "Éliminé");

    const tdTurn = document.createElement("td");
    OG.setText(tdTurn, player.turn ? "En cours" : "—");

    [tdName, tdRole, tdWord, tdStatus, tdTurn].forEach((td) => tr.appendChild(td));
    return tr;
  }

  function render() {
    tbody.replaceChildren();
    players.forEach((p) => tbody.appendChild(buildRow(p)));
    const aliveCount = players.filter((p) => p.alive).length;
    OG.setText(document.getElementById("state-alive"), `${aliveCount} / ${players.length}`);
  }

  render();

  /* ---------- Contrôles de phase (démo) ---------- */

  const phaseEl = document.getElementById("state-phase");

  function setPhase(label, endpoint) {
    OG.setText(phaseEl, label);
    OG.showToast(`Phase : ${label}`);
    // PHASE 2 : await OG.apiRequest(endpoint, { room_code });
    // puis les clients joueurs récupèrent ce changement via leur polling.
  }

  document.getElementById("btn-start-clues").addEventListener("click", () => setPhase("Indices", "start_clues"));
  document.getElementById("btn-start-vote").addEventListener("click", () => setPhase("Vote", "start_vote"));
  document.getElementById("btn-next-round").addEventListener("click", () => setPhase("Nouvelle manche", "next_round"));

  document.getElementById("btn-end-game").addEventListener("click", () => {
    const confirmed = window.confirm("Terminer la partie pour tout le monde ?");
    if (!confirmed) return;
    // PHASE 2 : OG.apiRequest("end_game", { room_code })
    OG.showToast("Partie terminée.");
  });
})();
