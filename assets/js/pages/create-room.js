(function () {
  "use strict";
  const OG = window.OtakuGames;

  /* ---------- Toggle rôle ---------- */

  const roleButtons = document.querySelectorAll(".toggle-option[data-role]");
  const roleInput = document.getElementById("role-input");

  roleButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      roleButtons.forEach((b) => b.setAttribute("aria-pressed", "false"));
      btn.setAttribute("aria-pressed", "true");
      roleInput.value = btn.dataset.role;
    });
  });

  /* ---------- Steppers (joueurs max, espions, manches, tours) ---------- */

  document.querySelectorAll("[data-stepper]").forEach((stepper) => {
    const min = Number(stepper.dataset.min);
    const max = Number(stepper.dataset.max);
    let value = Number(stepper.dataset.value);
    const valueEl = stepper.querySelector(".stepper-value");

    function render() {
      OG.setText(valueEl, value);
      stepper.dataset.value = String(value);
      stepper.querySelectorAll(".stepper-btn").forEach((btn) => {
        const delta = Number(btn.dataset.step);
        btn.disabled = (delta < 0 && value <= min) || (delta > 0 && value >= max);
      });
      // Contrainte de cohérence : le nombre d'espions ne peut pas dépasser
      // la moitié des joueurs max (règle appliquée aussi côté PHP plus tard).
      if (stepper.dataset.input === "max_players") {
        syncImpostorMax(value);
      }
    }

    stepper.querySelectorAll(".stepper-btn").forEach((btn) => {
      btn.addEventListener("click", () => {
        const delta = Number(btn.dataset.step);
        value = Math.min(max, Math.max(min, value + delta));
        render();
      });
    });

    render();
  });

  function syncImpostorMax(maxPlayers) {
    const impostorStepper = document.querySelector('[data-input="impostor_count"]');
    if (!impostorStepper) return;
    const newMax = Math.max(1, Math.floor(maxPlayers / 3));
    impostorStepper.dataset.max = String(newMax);
    let current = Number(impostorStepper.dataset.value);
    if (current > newMax) {
      current = newMax;
      impostorStepper.dataset.value = String(current);
      OG.setText(impostorStepper.querySelector(".stepper-value"), current);
    }
  }

  /* ---------- Chips (timer tour, timer vote, mode) ---------- */

  document.querySelectorAll("[data-chip-group]").forEach((group) => {
    const chips = group.querySelectorAll(".chip");
    chips.forEach((chip) => {
      chip.addEventListener("click", () => {
        chips.forEach((c) => c.setAttribute("aria-pressed", "false"));
        chip.setAttribute("aria-pressed", "true");

        if (group.dataset.chipGroup === "game_mode") {
          updateModeHint(chip.dataset.value);
        }
      });
    });
  });

  function updateModeHint(mode) {
    const hint = document.getElementById("mode-hint");
    if (mode === "elimination") {
      OG.setText(hint, "Elimination : les ninjas gagnent seulement quand tous les espions sont éliminés.");
    } else {
      OG.setText(hint, "Original : la manche se termine dès qu'un espion ou un ninja tombe.");
    }
  }

  /* ---------- Soumission ---------- */

  const form = document.getElementById("create-form");
  const pseudoInput = document.getElementById("pseudo");
  const fieldPseudo = document.getElementById("field-pseudo");
  const submitBtn = document.getElementById("create-submit");

  OG.sanitizePseudoInput(pseudoInput);
  pseudoInput.value = OG.readSession(OG.STORAGE_KEYS.pseudo) || "";

  function collectSettings() {
    const settings = { role: roleInput.value, theme: document.getElementById("theme-select").value };
    document.querySelectorAll("[data-stepper]").forEach((s) => {
      settings[s.dataset.input] = Number(s.dataset.value);
    });
    document.querySelectorAll("[data-chip-group]").forEach((g) => {
      const active = g.querySelector('.chip[aria-pressed="true"]');
      settings[g.dataset.chipGroup] = active ? active.dataset.value : null;
    });
    return settings;
  }

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const pseudoOk = OG.isValidPseudo(pseudoInput.value);
    fieldPseudo.classList.toggle("has-error", !pseudoOk);

    if (!pseudoOk) {
      OG.showToast("Choisis un pseudo valide avant de continuer.", "error");
      return;
    }

    submitBtn.disabled = true;
    const settings = collectSettings();
    OG.saveSession(OG.STORAGE_KEYS.pseudo, pseudoInput.value.trim());
    OG.saveSession(OG.STORAGE_KEYS.role, settings.role);

    // PHASE 2 (backend) : remplacer par un vrai appel serveur, ex :
    //   const result = await OG.apiRequest("create_room", { pseudo, ...settings });
    //   -> PHP génère un code de room aléatoire (6-8 caractères),
    //      crée les lignes en base via PDO préparé, ouvre la session.
    OG.showToast("Création de la partie…");
    setTimeout(() => {
      window.location.href = "lobby.html";
    }, 500);
  });
})();
