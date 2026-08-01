# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Projet

**Wart-Stat** collecte et analyse les rapports d'après-bataille de War Thunder (texte brut copié depuis le jeu, en français). Un rapport est collé dans l'UI, parsé côté PHP, puis éclaté en mission / actions / bonus dans SQLite.

Le code, les commentaires et la documentation du dépôt sont en français — conserver cette langue.

## Commandes

Gestionnaire de paquets : **yarn 4 (Corepack)**. Ne pas utiliser `npm` (un `package-lock.json` obsolète traîne encore à la racine).

```bash
yarn install
yarn dev            # Vite seul sur :5173 — SANS API (voir plus bas)
yarn build          # tsc -b && vite build  -> dist/
yarn build:w        # build en watch, utilisé quand le backend sert dist/
yarn lint

composer install    # dépendances PHP (vendor/)
composer dump-autoload

docker compose up -d --build      # pile complète sur http://localhost
.\Start-Dev.ps1                   # idem + attente du service + ouverture du navigateur
docker compose logs -f
```

Scripts utilitaires PHP (à lancer depuis la racine, hors Docker) :

```bash
php test-parser.php 42        # parse report/report42.txt et affiche le résultat (index aléatoire si omis)
php list-reports.php          # liste les rapports en base
php load-all-reports.php      # batch : charge tout report/*.txt en base (dédoublonnage par datetime)
php fix-missing-countries.php # corrige les pays manquants en base
```

Scripts PowerShell (gestion du corpus `report/`, encodés en UTF-16, sorties en français) : `Get-Report.ps1`, `Create-Report.ps1`, `Remove-Report.ps1`, `Analyze-Reports.ps1`, `Export-AnalysisData.ps1` (recalcule `analysis-data.json`, la régression affine session→date utilisée par `Create-Report.ps1`).

**Aucun framework de test** (ni PHPUnit ni Vitest). La vérification se fait via `yarn lint`, `tsc -b` (inclus dans `yarn build`) et `php test-parser.php <index>` sur des rapports réels.

## Architecture

### Servir l'application : une seule origine, pas de proxy

Le backend est un `php -S 0.0.0.0:80 -t dist` (voir `Dockerfile.backend`). Le dossier `public/` de Vite est copié dans `dist/` au build, donc `dist/api/index.php` existe et inclut `api/bootstrap.php`. Le serveur intégré PHP remonte les répertoires parents à la recherche d'un `index.php` : `/api/reports` tombe donc sur `dist/api/index.php` (Slim a `setBasePath('/api')`), et une route SPA comme `/reports/12` retombe sur `dist/index.html`.

Conséquences :
- `vite.config.ts` n'a **aucun proxy** et le front appelle `/api/...` en relatif (`app/stores/reportStore.ts`) : `yarn dev` seul ne peut pas joindre l'API. Pour développer avec l'API, utiliser Docker Compose (le service `frontend` ne fait que `yarn build` et partage le volume `dist` avec le backend) ou `yarn build:w` + un `php -S ... -t dist` local.
- Toute modification du front doit être rebuildée pour être visible via `http://localhost`.

### Backend (`api/`, namespace PSR-4 `WartStat\`)

`public/api/index.php` → `api/bootstrap.php` (autoload, container, Slim via PHP-DI Bridge, basePath `/api`) → `api/router.php` (routes groupées par ressource) → contrôleurs.

- `api/container.php` ne déclare **que** `PDO` (SQLite `data/database.sqlite`) et `Monolog\Logger` / `LoggerInterface`. Tout le reste est **autowiré** : un nouveau contrôleur, repository ou action ne nécessite aucune entrée dans le container.
- Les contrôleurs étendent `WartStat\Base\Controller` et utilisent ses helpers `makeJsonResponse()` / `parseRequestBody()` (gère JSON et form-urlencoded).
- Organisation par domaine : `api/wart-stat/<Domaine>/` contient `*Controller`, `*Repository`, `*Validator`, `*Parser`… du domaine.

### Persistance : pas de migrations

Chaque repository crée sa table dans son constructeur (`ensureTableExists()` avec `CREATE TABLE IF NOT EXISTS`, plus parfois un `ALTER TABLE` dans un `try/catch` pour la rétrocompatibilité). Il n'y a **pas de système de migration** : un changement de schéma se fait dans le `ensureTableExists()` du repository concerné, en gardant les bases existantes fonctionnelles.

Modèle de données : `reports` (contenu brut + `session_id` UNIQUE) → `missions` (1-1 via `report_id`) → `mission_actions` et `mission_bonuses` (via `mission_id`). La vue `reports_details` agrège tout cela pour la liste et est exposée par `ReportDetailsRepository`.

`data/database.sqlite` est **versionné dans Git** et évolue à chaque usage de l'app : s'attendre à le voir modifié dans `git status`. `report/` et `dist/` sont ignorés.

### Pipeline de création d'un rapport

`POST /api/reports` → `ReportController::create()` enchaîne : validation (`ReportValidator`) → insertion du rapport (le `session_id` est extrait du contenu par regex `^Session:\s*([a-f0-9]+)$` et sert de clé d'unicité) → écriture d'un fichier `report/reportN.txt` (`ReportFileHandler`) → `ReportParser::parse()` → création de la mission, des actions puis des bonus. Cet enchaînement **n'est pas transactionnel** : un échec en cours de route laisse des données partielles.

`ReportParser` interprète le format texte français du jeu (« Destruction de cibles terrestres », « Temps Joué », SL/RP avec bonus `(PA)`/`(Booster)`…). Le corpus `report/*.txt` (~1350 fichiers) sert de jeu d'essai de référence pour toute évolution du parser.

### Frontend (`app/`)

React 19 + **React Compiler** (activé via `babel-plugin-react-compiler` dans `vite.config.ts`), TypeScript strict, Vite. Point d'entrée `app/main.tsx` (le HTML charge `/app/main.tsx`, il n'y a pas de dossier `src/`).

- Routage : `app/routes.tsx` exporte un tableau `RouteObject[]` avec des pages en `lazy()`, consommé par `useRoutes()` dans `App.tsx`, enveloppé dans `ErrorBoundary` + `DefaultLayout`.
- État : stores Zustand dans `app/stores/`, un store par ressource ; les appels `fetch` vers `/api/...` vivent dans le store, pas dans les composants.
- UI : le design system vient du paquet **`@fzed51/green-terminal`** (`<BaseStyle />` monté dans `main.tsx`). `app/components/common/index.ts` est le **point d'import unique** : il réexporte les composants du paquet et les wrappers locaux (`Card`, `Input`, `Select`, `Table`, `Textarea`…). Importer depuis `../components/common`, pas depuis le paquet directement, et créer un wrapper local plutôt que de contourner ce point d'entrée.
- Thème « hacker » vert néon sur fond noir, police Fira Code — variables et effets détaillés dans `.github/instructions/styling-theme.instructions.md`.
- Les pays sont un union type fermé (`US | GER | URRS | UK | JAP | CH | IT | FR | SU | IL`) défini avec ses libellés et drapeaux dans `app/constants/countries.ts` ; la même liste est dupliquée dans `fix-missing-countries.php`.

## Documentation existante

- `.github/copilot-instructions.md` et `.github/instructions/*.md` (`api`, `app`, `repository-pattern`, `styling-theme`) : conventions détaillées, à consulter avant de coder sur la zone concernée.
- `AI_CONTEXT.md` : contexte technique long, partiellement générique et daté (décrit `npm`, un port `:8080` et des exemples fictifs `User`/`Product` qui ne correspondent pas à l'état actuel) — vérifier dans le code avant de s'y fier.
- `README.md` est le template Vite par défaut, sans valeur pour ce projet.
