(function () {
  "use strict";
  const OG = window.OtakuGames;

  const roomCodeEl = document.getElementById("room-code-display");
  const copyBtn = document.getElementById("copy-code-btn");
  const listEl = document.getElementById("player-list");
  const countPill = document.getElementById("player-count-pill");
  const startBtn = document.getElementById("start-btn");
  const hostControls = document.getElementById("host-controls");
  const waitingHint = document.getElementById("waiting-hint");
  const leaveBtn = document.getElementById("leave-btn");

  const myRole = OG.readSession(OG.STORAGE_KEYS.role) || "joueur";
  const myPseudo = OG.readSession(OG.STORAGE_KEYS.pseudo) || "Toi";
  const isHost = myRole === "hokage";
  const maxPlayers = 6;


  let players = [{ pseudo: myPseudo, host: isHost }];
  const demoNames = ["Saya", "Renji", "Aoi", "Kenta", "Mika"];

  function buildPlayerRow(player) {
    const row = document.createElement("div");
    row.className = "player-row";

    const avatar = document.createElement("div");
    avatar.className = "avatar";
    OG.setText(avatar, OG.initialsOf(player.pseudo));

    const info = document.createElement("div");
    info.className = "player-info";

    const name = document.createElement("div");
    name.className = "player-name";
    OG.setText(name, player.pseudo);

    const meta = document.createElement("div");
    meta.className = "player-meta";
    OG.setText(meta, player.host ? "Hokage · gère la partie" : "Ninja");

    info.appendChild(name);
    info.appendChild(meta);
    row.appendChild(avatar);
    row.appendChild(info);
    return row;
  }

  function render() {
    listEl.replaceChildren();
    players.forEach((p) => listEl.appendChild(buildPlayerRow(p)));
    OG.setText(countPill, `${players.length} / ${maxPlayers}`);

    const canStart = players.length >= 3;
    OG.setText(waitingHint, canStart
      ? "Assez de joueurs pour lancer la partie."
      : "En attente d'autres joueurs (3 minimum)…");

    hostControls.style.display = isHost ? "block" : "none";
    startBtn.disabled = !canStart;
    OG.setText(startBtn, canStart ? "Lancer la partie" : "Lancer la partie (3 joueurs minimum)");
  }

  render();

  // Room code de démo (sera fourni par le serveur PHP à la création réelle)
  OG.setText(roomCodeEl, OG.readSession(OG.STORAGE_KEYS.roomCode) || "K3NJA9");

  copyBtn.addEventListener("click", async () => {
    try {
      await navigator.clipboard.writeText(roomCodeEl.textContent);
      OG.showToast("Code copié.");
    } catch (err) {
      OG.showToast("Copie impossible, sélectionne le code manuellement.", "error");
    }
  });

  // Simulation de connexions de joueurs (à retirer en phase 2)
  let demoIndex = 0;
  const demoInterval = setInterval(() => {
    if (players.length >= maxPlayers || demoIndex >= demoNames.length) {
      clearInterval(demoInterval);
      return;
    }
    players.push({ pseudo: demoNames[demoIndex], host: false });
    demoIndex += 1;
    render();
  }, 2200);

  startBtn.addEventListener("click", () => {
    if (startBtn.disabled) return;
    // PHASE 2 : OG.apiRequest("start_game", { room_code }) puis redirection
    window.location.href = "game.html";
  });

  leaveBtn.addEventListener("click", () => {
    // PHASE 2 : OG.apiRequest("leave_room", { room_code }) avant de rediriger
    window.location.href = "index.html";
  });
})();
