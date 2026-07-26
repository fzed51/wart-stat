# Wart-Stat: Instructions Copilot

## Vue d'ensemble du projet

**Wart-Stat** est une application full-stack de gestion et d'analyse de rapports d'états de jeu (War Thunder). Elle se compose d'un frontend React/TypeScript et d'un backend PHP/Slim découplés.

## Architecture

```
Frontend (React + TypeScript + Vite)      Backend (PHP + Slim Framework)
        └── ./app/                                └── ./api/
            ├── main.tsx                              ├── bootstrap.php
            ├── App.tsx                               ├── container.php
            ├── routes.tsx                            ├── router.php
            ├── components/                           └── wart-stat/
            │   └── layouts/                              ├── Base/
            ├── pages/                                    │   ├── Controller.php
            ├── hooks/                                    │   └── Database.php
            └── stores/                                   ├── Report/
                                                          └── [Domaines]/
```

### Stack Frontend

- **React** 19.2.0 avec composants fonctionnels uniquement
- **TypeScript** 5.9.3 en mode strict (pas de `any`)
- **Vite** 7.2.4 avec HMR et React Compiler activé
- **React Router** v7.11.0 pour le routage
- **Zustand** v4.5.2 pour la gestion d'état global
- **@fzed51/green-terminal** — bibliothèque de composants UI du projet (importer depuis ce package, ne pas recréer de composants locaux)

### Stack Backend

- **Slim Framework** v4.15 (micro-framework PHP)
- **PHP-DI** v3.4 via Slim Bridge — injection de dépendances avec autowiring
- **Monolog** — logging centralisé (injecté via `Psr\Log\LoggerInterface`)
- **SQLite + PDO** — base de données relationnelle locale (`data/database.sqlite`)
- **PSR-4** — autoloading avec namespace `WartStat\`

---

## Conventions Backend (`./api`)

> Voir [`.github/instructions/api.instructions.md`](.github/instructions/api.instructions.md) pour les détails complets.

### Organisation par ressource/domaine

Chaque ressource suit le pattern: **un dossier = une ressource** dans `api/wart-stat/`.

```
api/wart-stat/[Ressource]/
├── [Ressource]Controller.php   # namespace WartStat\[Ressource]
├── [Ressource]Repository.php
└── [Ressource]Validator.php
```

### Règles essentielles

- `declare(strict_types=1);` en tête de chaque fichier PHP
- Tous les Controllers héritent obligatoirement de `WartStat\Base\Controller`
- PHP-DI résout les dépendances automatiquement via le type-hinting du constructeur
- Les routes sont groupées par ressource dans `api/router.php`
- Logging: utiliser `$this->logger->info('~methode~')` en entrée de méthode

### Pattern Repository

> Voir [`.github/instructions/repository-pattern.instructions.md`](.github/instructions/repository-pattern.instructions.md) pour le template complet.

- Constructeur reçoit `PDO $pdo` et `Logger $logger`
- Appeler `$this->ensureTableExists()` dans le constructeur
- Utiliser `CREATE TABLE IF NOT EXISTS` avec colonne `created_at` ISO-8601
- Toujours utiliser des **requêtes SQL paramétrées** (jamais de concaténation directe)
- CRUD standard: `create()`, `findById()`, `findAll()`, `update()`, `delete()`
- Logs à niveau `debug`: `"Resource created with ID: $id"`

### Ajouter une nouvelle ressource (checklist)

1. Créer `api/wart-stat/[Ressource]/` avec Controller, Repository, Validator
2. Ajouter le groupe de routes dans `api/router.php`
3. Configurer les dépendances complexes dans `api/container.php` si nécessaire

---

## Conventions Frontend (`./app`)

> Voir [`.github/instructions/app.instructions.md`](.github/instructions/app.instructions.md) pour les détails complets.

### Règles essentielles

- Composants fonctionnels uniquement (pas de classes)
- TypeScript strict: pas de `any`, typer tous les paramètres et retours
- Un store Zustand par domaine métier dans `app/stores/`
- Hooks personnalisés dans `app/hooks/` (préfixe `use`)
- Routes définies dans `app/routes.tsx`
- Importer les composants UI depuis `@fzed51/green-terminal`

### Nommage

| Type | Convention | Exemple |
|------|-----------|---------|
| Composant | PascalCase | `ReportCard.tsx` |
| Hook | `use` + camelCase | `useFetchReports.ts` |
| Store | camelCase + `Store` | `reportStore.ts` |
| Page | PascalCase + `Page` | `ReportsPage.tsx` |

### Appels API

Centraliser les appels dans des hooks ou services; utiliser `fetch` native avec gestion des erreurs.

---

## Design System — Thème Hacker

> Voir [`.github/instructions/styling-theme.instructions.md`](.github/instructions/styling-theme.instructions.md) pour la référence complète.

L'application utilise un **thème hacker rétro** (Matrix/terminal): vert néon `#00ff00` sur fond noir `#0a0a0a`.

### Règles de styling

- **Toujours utiliser les variables CSS** — jamais hardcoder les couleurs
- Police **monospace** uniquement (`Fira Code` en premier)
- Effets `text-shadow` (glow) sur les titres
- Bordures ASCII art via `::before` / `::after`
- Effets hover: `border-color: var(--color-border-hover)` + `box-shadow` glow

```css
/* ✅ Correct */
color: var(--color-text-primary);

/* ❌ Interdit */
color: #00ff00;
```

---

## Commandes de développement

### Frontend
```bash
yarn dev          # Démarre Vite (http://localhost:5173)
yarn build        # Build de production
yarn lint         # Validation ESLint
yarn preview      # Prévisualisation du build
```

### Backend
```bash
docker-compose up backend   # Démarre le backend (http://localhost:8080/api)
```

### Démarrage complet
```bash
docker-compose up           # Lance frontend + backend
```

---

## Données et rapports

- Rapports stockés dans `./report/` (fichiers `.txt`)
- Base de données SQLite dans `data/database.sqlite` (créée automatiquement)
- Scripts PowerShell: `Create-Report.ps1`, `Get-Report.ps1`, `Analyze-Reports.ps1`
- Données d'analyse exportées dans `analysis-data.json`

---

## Documentation de référence

| Fichier | Contenu |
|--------|---------|
| [`.github/instructions/api.instructions.md`](.github/instructions/api.instructions.md) | Backend PHP/Slim, contrôleurs, routes, DI |
| [`.github/instructions/app.instructions.md`](.github/instructions/app.instructions.md) | Frontend React/TypeScript, composants, état |
| [`.github/instructions/styling-theme.instructions.md`](.github/instructions/styling-theme.instructions.md) | Design system, couleurs, effets visuels |
| [`.github/instructions/repository-pattern.instructions.md`](.github/instructions/repository-pattern.instructions.md) | Pattern Repository, template SQLite/PDO |
| [`AI_CONTEXT.md`](../AI_CONTEXT.md) | Contexte technique complet, exemples de code |
