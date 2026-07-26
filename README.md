# Wart-Stat

Application full-stack de gestion et d'analyse de rapports de sessions War Thunder. Elle permet de collecter, stocker, visualiser et analyser les statistiques de jeu par session.

## Architecture

```
┌─────────────────────────────────────────────────────┐
│  Frontend  React 19 + TypeScript + Vite              │
│  ./app/    Thème hacker rétro (vert néon/noir)       │
└────────────────────┬────────────────────────────────┘
                     │ HTTP (fetch)
┌────────────────────▼────────────────────────────────┐
│  Backend   PHP + Slim Framework v4                   │
│  ./api/    REST API · SQLite · PHP-DI                │
└─────────────────────────────────────────────────────┘
```

### Frontend (`./app`)

| Technologie | Version | Rôle |
|-------------|---------|------|
| React | 19.2.0 | Framework UI |
| TypeScript | 5.9.3 | Typage statique |
| Vite | 7.2.4 | Build tool + HMR |
| React Router | 7.11.0 | Routage SPA |
| Zustand | 4.5.2 | Gestion d'état |
| @fzed51/green-terminal | 1.0.1 | Composants UI |

### Backend (`./api`)

| Technologie | Version | Rôle |
|-------------|---------|------|
| Slim Framework | 4.15 | Micro-framework REST |
| PHP-DI | 3.4 | Injection de dépendances |
| Monolog | — | Logging centralisé |
| SQLite + PDO | — | Base de données |

---

## Démarrage rapide

### Prérequis

- [Docker](https://www.docker.com/) et Docker Compose
- [Node.js](https://nodejs.org/) + [Yarn](https://yarnpkg.com/) (pour le frontend en local)

### Lancer l'application complète

```bash
docker-compose up
```

- **Frontend**: http://localhost:5173
- **API Backend**: http://localhost:8080/api

### Développement frontend seul

```bash
yarn install
yarn dev
```

### Commandes disponibles

```bash
# Frontend
yarn dev          # Serveur de développement (HMR)
yarn build        # Build de production
yarn lint         # Validation ESLint
yarn preview      # Prévisualisation du build

# Backend (via Docker)
docker-compose up backend
```

---

## Structure du projet

```
wart-stat/
├── app/                    # Frontend React/TypeScript
│   ├── main.tsx
│   ├── App.tsx
│   ├── routes.tsx
│   ├── components/         # Composants réutilisables
│   │   └── layouts/        # Layouts de l'application
│   ├── pages/              # Pages (une par route)
│   ├── hooks/              # Hooks personnalisés
│   └── stores/             # Stores Zustand
├── api/                    # Backend PHP/Slim
│   ├── bootstrap.php       # Point d'entrée application
│   ├── container.php       # Configuration DI
│   ├── router.php          # Définition des routes
│   └── wart-stat/          # Code métier (namespace WartStat\)
│       ├── Base/           # Classes abstraites
│       └── Report/         # Ressource rapports
├── data/                   # Base de données SQLite
├── report/                 # Fichiers de rapports bruts (.txt)
├── public/                 # Fichiers statiques servis
├── docker-compose.yaml
├── Dockerfile.backend
├── Dockerfile.frontend
└── analysis-data.json      # Données d'analyse exportées
```

---

## Gestion des rapports

Les rapports de session sont gérés via des scripts PowerShell:

```powershell
# Créer un nouveau rapport
.\Create-Report.ps1

# Récupérer un rapport existant
.\Get-Report.ps1

# Analyser les rapports
.\Analyze-Reports.ps1

# Exporter les données d'analyse
.\Export-AnalysisData.ps1
```

Les fichiers de rapport sont stockés dans `./report/` au format texte et importés en base SQLite via l'API.

---

## Design

L'application utilise un **thème hacker rétro** inspiré de l'esthétique Matrix:

- **Palette**: Vert néon (`#00ff00`) sur fond noir (`#0a0a0a`)
- **Typographie**: Police monospace (`Fira Code`)
- **Effets**: Glow sur les titres, scanlines CRT, bordures ASCII art

---

## Documentation

| Fichier | Description |
|--------|-------------|
| [`AI_CONTEXT.md`](AI_CONTEXT.md) | Contexte technique complet pour les agents IA |
| [`.github/copilot-instructions.md`](.github/copilot-instructions.md) | Instructions pour GitHub Copilot |
| [`.github/instructions/api.instructions.md`](.github/instructions/api.instructions.md) | Conventions backend PHP/Slim |
| [`.github/instructions/app.instructions.md`](.github/instructions/app.instructions.md) | Conventions frontend React/TypeScript |
| [`.github/instructions/styling-theme.instructions.md`](.github/instructions/styling-theme.instructions.md) | Design system et conventions CSS |
| [`.github/instructions/repository-pattern.instructions.md`](.github/instructions/repository-pattern.instructions.md) | Pattern Repository SQLite/PDO |
