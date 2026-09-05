# Otaku Games — Front (phase 1)

Toutes les interfaces sont en HTML/CSS/JS pur, sans framework, avec des
données de démonstration en JavaScript (pas encore branchées à PHP/MariaDB).
Le but de cette phase : valider le design et les interactions avant de coder
le backend.

## Comment prévisualiser

**Option simple (aucune installation) :** double-clique sur `index.html`,
il s'ouvre dans ton navigateur. Navigue via les boutons (les pages sont
reliées entre elles).

**Option recommandée (identique à l'environnement final) :**
1. Copie le dossier `otaku-games` dans `C:\wamp64\www\`
2. Lance WampServer
3. Ouvre `http://localhost/otaku-games/`

C'est la deuxième option qu'il faudra utiliser dès qu'on branchera PHP,
puisque `api/*.php` ne peut pas fonctionner en ouvrant juste le fichier.

## Pages incluses

| Fichier | Rôle |
|---|---|
| `index.html` | Accueil |
| `create-room.html` | Création de partie (réglages complets) |
| `join-room.html` | Rejoindre une partie via code |
| `lobby.html` | Salon d'attente |
| `game.html` | Interface de jeu (joueur) |
| `admin.html` | Interface Hokage (vue d'ensemble de la partie) |

## Ce qui est simulé (à remplacer en phase 2 - PHP)

Chaque fichier dans `assets/js/pages/` contient des commentaires
`PHASE 2 (backend)` qui indiquent précisément où brancher les appels à
`OG.apiRequest(...)` vers les futurs scripts PHP (`api/*.php`).

Exemples : liste des joueurs dans le lobby, mots/rôles distribués,
déroulement des tours, résultat des votes, compteur de joueurs en ligne.

## Sécurité déjà en place côté front

- **Aucun `innerHTML`** utilisé avec du contenu venant d'un joueur (pseudo,
  indice...) — tout passe par `textContent` (fonction `setText`) pour
  empêcher l'injection de scripts (XSS).
- **Aucun `onclick=""` inline** dans le HTML — tous les événements sont
  attachés en JS via `addEventListener`.
- **Validation de saisie** (pseudo, code de room) côté client pour l'UX,
  avec un rappel explicite dans le code : *la vraie validation doit être
  refaite côté PHP*, un contrôle uniquement en JS peut être contourné.
- **Pas de données sensibles en `localStorage`** (qui persiste) — seul
  `sessionStorage` est utilisé, pour des données non sensibles (pseudo,
  code de room), effacées à la fermeture de l'onglet.
- **En-tête `Content-Security-Policy`** sur chaque page : bloque le
  chargement de scripts venant d'un autre domaine que le site lui-même.
- **Jeton anti-CSRF prévu** (`<input type="hidden" name="csrf_token">`) :
  la valeur `GENERATED_BY_PHP` est un espace réservé, à remplacer par un
  vrai jeton généré et vérifié par PHP en session dès que le backend
  existe.
- **La page `admin.html` n'est pas protégée pour l'instant** (c'est
  seulement du front) — en phase 2, l'accès devra être vérifié côté PHP
  (session confirmant le rôle "hokage" sur cette room précise), pas
  seulement masqué en JS.

## Prochaine étape

Créer la base MariaDB (`sql/schema.sql`) et les endpoints PHP dans `api/`,
en remplaçant progressivement chaque bloc `PHASE 2` par un vrai appel
serveur avec PDO en requêtes préparées.
