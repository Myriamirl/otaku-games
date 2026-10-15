(function () {
  "use strict";
  const { setText } = window.OtakuGames;

  // DÉMO : en phase 2, cette valeur viendra d'un endpoint PHP
  // (ex: GET api/online_count.php) interrogé toutes les ~10s.
  const el = document.getElementById("online-count");
  setText(el, Math.floor(Math.random() * 9));
})();
