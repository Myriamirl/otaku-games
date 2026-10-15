(function () {
  "use strict";
  const OG = window.OtakuGames;

  const form = document.getElementById("join-form");
  const pseudoInput = document.getElementById("pseudo");
  const codeInput = document.getElementById("room-code");
  const fieldPseudo = document.getElementById("field-pseudo");
  const fieldCode = document.getElementById("field-code");
  const submitBtn = document.getElementById("join-submit");

  // Filtre la saisie du pseudo en direct + normalise le code en majuscules
  OG.sanitizePseudoInput(pseudoInput);
  codeInput.addEventListener("input", () => {
    codeInput.value = codeInput.value.toUpperCase().replace(/[^A-Z0-9]/g, "").slice(0, 8);
  });

  // Pré-remplissage depuis la session (non sensible), si déjà utilisé plus tôt
  pseudoInput.value = OG.readSession(OG.STORAGE_KEYS.pseudo) || "";

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const pseudoOk = OG.isValidPseudo(pseudoInput.value);
    const codeOk = OG.isValidRoomCode(codeInput.value);

    fieldPseudo.classList.toggle("has-error", !pseudoOk);
    fieldCode.classList.toggle("has-error", !codeOk);

    if (!pseudoOk || !codeOk) {
      OG.showToast("Vérifie ton pseudo et le code de la partie.", "error");
      return;
    }

    submitBtn.disabled = true;
    OG.saveSession(OG.STORAGE_KEYS.pseudo, pseudoInput.value.trim());
    OG.saveSession(OG.STORAGE_KEYS.roomCode, codeInput.value.trim());

    // PHASE 2 (backend) : remplacer ce bloc par un vrai appel
    //   const result = await OG.apiRequest("join_room", { pseudo, room_code });
    //   -> le serveur vérifie que la room existe, n'est pas pleine,
    //      n'a pas déjà démarré, puis crée la session joueur.
    OG.showToast("Connexion à la partie…");
    setTimeout(() => {
      window.location.href = "lobby.html";
    }, 500);
  });
})();
