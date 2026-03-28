# Wart-Stat: Instructions de Projet

## Vue d'ensemble

**Wart-Stat** est une application full-stack de gestion et d'analyse de rapports d'états (warting). L'application est composée d'un frontend React/TypeScript et d'un backend PHP/Slim.

### Architecture générale

```
Frontend (React + TypeScript + Vite)     Backend (PHP + Slim Framework)
        └── ./app/                               └── ./api/
            ├── components/                          ├── wart-stat/
            ├── pages/                               │   ├── Base/
            ├── hooks/                               │   └── Report/
            ├── stores/                              ├── bootstrap.php
            └── routes.tsx                           ├── container.php
                                                     └── router.php
```

### Architecture générale

**Frontend**: 
- Vue par Vite avec Hot Module Replacement (HMR)
- Routage via React Router v7
- Gestion d'état avec Zustand
- TypeScript pour la sécurité des types

**Backend**:
- Micro-framework web Slim Framework v4.15
- Injection de dépendances via PHP-DI
- Logging centralisé avec Monolog
- Structure PSR-4 pour l'autoloading

## Principes de contribution

### Organisation du code

- **Backend (`./api`)**: La logique métier, les modèles de données, les contrôleurs et la gestion des rapports doivent respecter la structure PSR-4 de l'espace de noms `WartStat\`
- **Frontend (`./app`)**: Les composants React, les hooks personnalisés, les stores Zustand et les pages doivent être organisés selon leur domaine métier

### Conventions de code

- **TypeScript**: Utilise les types strictement, évite `any`. Les types personnalisés doivent être définis dans des fichiers dédiés
- **React**: Utilise des composants fonctionnels avec hooks. Évite les composants classe
- **PHP**: Respecte PSR-12 (style de code). Utilise l'injection de dépendances (PHP-DI)
- **Formatage**: ESLint pour le frontend, les standards PSR-12 pour le backend

### Communication API

Le frontend communique avec le backend via des appels HTTP. Les endpoints doivent être structurés de manière RESTful via les routes définies dans `./api/router.php`.

## Démarrage du développement

### Frontend
```bash
yarn dev          # Démarre le serveur Vite en mode développement
yarn build        # Compile l'application
yarn lint         # Valide le code avec ESLint
```

### Backend
Lancé via Docker (voir `docker-compose.yaml` et `Dockerfile.backend`)

## Données et rapports

- Les rapports sont stockés dans le répertoire `./report/`
- Les scripts PowerShell (`Create-Report.ps1`, `Get-Report.ps1`, etc.) gèrent la génération et l'export des rapports
- Les données d'analyse sont disponibles dans `analysis-data.json`

## Docker & Déploiement

L'application utilise Docker Compose :
- `Dockerfile.backend`: Image PHP/Slim
- `Dockerfile.frontend`: Image Node.js
- `docker-compose.yaml`: Orchestration des services

## Besoin d'aide?

- Consulte la documentation spécifique dans `.github/instructions/` pour le backend (`api.instructions.md`) ou le frontend (`app.instructions.md`)
- Vérifie `AI_CONTEXT.md` pour le contexte technique supplémentaire
