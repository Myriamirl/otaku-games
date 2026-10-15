/* =========================================================
   OTAKU GAMES — app.js
   Utilitaires partagés par toutes les pages.

   Règles de sécurité front respectées ici (rappel — la
   validation définitive doit TOUJOURS être refaite côté PHP,
   le front ne fait que de l'aide à la saisie / UX) :
     - jamais d'innerHTML avec du texte venant d'un joueur
       -> on utilise toujours textContent (voir setText)
     - jamais de gestionnaire onclick="" inline dans le HTML
       -> tout passe par addEventListener
     - pas de données sensibles en localStorage (persistant)
       -> sessionStorage uniquement, et seulement des données
          non sensibles (pseudo, code de room, préférence
          d'affichage du mot)
     - un jeton "CSRF" est prévu (placeholder) pour être injecté
       par PHP dans chaque formulaire une fois le back branché
   ========================================================= */

window.OtakuGames = (function () {
  "use strict";

  /* ---------- Rendu texte sans risque XSS ---------- */

  function setText(el, value) {
    if (!el) return;
    el.textContent = value == null ? "" : String(value);
  }

  /* ---------- Validation de saisie (aide UX, pas une garantie) ---------- */

  const PSEUDO_PATTERN = /^[\p{L}0-9 _-]{2,16}$/u;
  const ROOM_CODE_PATTERN = /^[A-Z0-9]{6,8}$/;

  function isValidPseudo(value) {
    return PSEUDO_PATTERN.test((value || "").trim());
  }

  function isValidRoomCode(value) {
    return ROOM_CODE_PATTERN.test((value || "").trim().toUpperCase());
  }

  function sanitizePseudoInput(el) {
    // Empêche la saisie de caractères hors gabarit dès la frappe,
    // en plus de la vérification isValidPseudo() au submit.
    el.addEventListener("input", () => {
      el.value = el.value.replace(/[^\p{L}0-9 _-]/gu, "").slice(0, 16);
    });
  }

  /* ---------- Stockage local non sensible ---------- */

  const STORAGE_KEYS = {
    pseudo: "og_pseudo",
    roomCode: "og_room_code",
    role: "og_role",
  };

  function saveSession(key, value) {
    try {
      sessionStorage.setItem(key, value);
    } catch (err) {
      // Navigation privée ou storage désactivé : on continue sans bloquer.
      console.warn("Stockage de session indisponible", err);
    }
  }

  function readSession(key) {
    try {
      return sessionStorage.getItem(key);
    } catch (err) {
      return null;
    }
  }

  /* ---------- Toast ---------- */

  let toastTimer = null;

  function showToast(message, type) {
    let toast = document.querySelector(".toast");
    if (!toast) {
      toast = document.createElement("div");
      toast.className = "toast";
      document.body.appendChild(toast);
    }
    setText(toast, message);
    toast.classList.toggle("is-error", type === "error");
    toast.classList.add("is-visible");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove("is-visible"), 3200);
  }

  /* ---------- Empêcher les doubles soumissions (anti-spam simple) ---------- */

  function guardSubmit(button, action) {
    button.addEventListener("click", async (event) => {
      event.preventDefault();
      if (button.disabled) return;
      button.disabled = true;
      try {
        await action(event);
      } finally {
        // Réactivé par la page appelante si besoin (ex: erreur de validation)
        setTimeout(() => {
          button.disabled = false;
        }, 600);
      }
    });
  }

  /* ---------- Appels vers l'API PHP ---------- */

  async function apiRequest(endpoint, payload) {
    // NOTE POUR LA SUITE (backend PHP) :
    // - chaque endpoint devra vérifier la session PHP et un jeton CSRF
    // - toutes les requêtes SQL correspondantes utiliseront PDO préparé
    // - cette fonction centralise fetch() pour éviter de dupliquer
    //   la gestion d'erreur partout dans le code
    const response = await fetch(`api/${endpoint}.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "same-origin",
      body: JSON.stringify(payload || {}),
    });

    if (!response.ok) {
      throw new Error(`Erreur serveur (${response.status})`);
    }
    return response.json();
  }

  /* ---------- Format mm:ss ---------- */

  function formatDuration(totalSeconds) {
    if (totalSeconds == null) return "∞";
    const m = Math.floor(totalSeconds / 60);
    const s = totalSeconds % 60;
    return `${m}:${String(s).padStart(2, "0")}`;
  }

  /* ---------- Initiales pour avatar ---------- */

  function initialsOf(name) {
    return (name || "?")
      .trim()
      .split(/\s+/)
      .slice(0, 2)
      .map((part) => part.charAt(0).toUpperCase())
      .join("");
  }

  return {
    setText,
    isValidPseudo,
    isValidRoomCode,
    sanitizePseudoInput,
    STORAGE_KEYS,
    saveSession,
    readSession,
    showToast,
    guardSubmit,
    apiRequest,
    formatDuration,
    initialsOf,
  };
})();
