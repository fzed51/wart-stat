---
name: api-instructions
description: "Use when: working on backend code in ./api/ directory, implementing PHP/Slim endpoints, adding business logic, or modifying database operations"
applyTo: "api/**"
---

# Backend (PHP/Slim): Instructions Techniques

## Aperçu technologique

Le backend est un **micro-framework web Slim Framework v4.15** avec:
- **Injection de dépendances**: PHP-DI via Slim Bridge
- **Logging**: Monolog pour la journalisation centralisée
- **Architecture**: PSR-4 avec namespace `WartStat\`
- **Routage**: Routes définies dans `router.php`
- **Configuration**: Bootstrap et container dans `bootstrap.php` et `container.php`

## Structure du projet

```
./api/
├── bootstrap.php          # Point d'entrée, initialise l'application
├── container.php          # Configuration du conteneur DI (PHP-DI)
├── router.php             # Définition des routes
└── wart-stat/
    ├── Base/              # Classes de base (héritage, abstractions)
    └── Report/            # Logique métier pour les rapports
```

## Principes de développement

### 1. Injection de dépendances
- Toutes les dépendances doivent être configurées dans `container.php`
- Utilise l'injecteur de Slim Bridge pour accéder aux services
- Les services doivent impléenter des interfaces clairement définies

### 2. Architecture en couches
- **Contrôleurs** ou **Handlers**: Traitent les requêtes HTTP
- **Services**: Logique métier
- **Modèles**: Représentation des données (ex: `Report`, `Base`)
- **Repositories**: Accès aux données (si applicable)

### 3. Gestion des rapports
- Les rapports sont gérés via la classe `WartStat\Report\*`
- Les données brutes sont stockées dans `./report/` en fichiers `.txt`
- Les opérations principales: créer, lire, mettre à jour, supprimer des rapports

### 4. Logging
- Utilise Monolog pour tous les logs
- Configuration dans les dépendances
- Récupérer le logger: `$container->get(Psr\Log\LoggerInterface::class)`

## Conventions de code

### Namespace et structure
```php
namespace WartStat\Report;

class ReportService {
    // ...
}
```

### Typage strict
```php
<?php
declare(strict_types=1);

namespace WartStat\Report;

/**
 * Description claire de la classe
 */
class ReportService
{
    public function getReport(int $id): array
    {
        // ...
    }
}
```

### Gestion des erreurs
- Lève des exceptions appropriées (`InvalidArgumentException`, `RuntimeException`)
- Utilise le middleware Slim pour gérer les exceptions globalement
- Retourne des réponses JSON cohérentes avec code d'erreur

## Routes et endpoints

Les routes sont définies dans `router.php`. Exemple de structure:

```php
$app->get('/api/reports/{id}', ReportController::class . ':get');
$app->post('/api/reports', ReportController::class . ':create');
$app->put('/api/reports/{id}', ReportController::class . ':update');
$app->delete('/api/reports/{id}', ReportController::class . ':delete');
```

## Exécution et test

### Démarrage en développement
```bash
docker-compose up backend  # Démarre le service backend
```

### Tests
- Actuellement pas de suite de tests formelle
- Testz les endpoints via le client du frontend ou Postman

## Dépannage courant

| Problème | Solution |
|----------|----------|
| Classe non trouvée | Vérifie le namespace et le chemin PSR-4 dans `composer.json` |
| Service non résolu | Ajoute la configuration dans `container.php` |
| Erreur CORS | Configure les headers CORS dans le middleware Slim |
| Erreur PDO | Vérifie la configuration de la base de données et les permissions |

## Points d'entrée clés

- **`bootstrap.php`**: Initialise l'application Slim
- **`container.php`**: Définit tous les services et dépendances
- **`router.php`**: Enregistre les routes API
- **`wart-stat/Report/*`**: Logique métier des rapports

## Ressources utiles

- [Slim Framework Documentation](https://www.slimframework.com/)
- [PHP-DI Documentation](https://php-di.org/)
- [Monolog Documentation](https://seldaek.github.io/monolog/)
- [PSR-4 Autoloading](https://www.php-fig.org/psr/psr-4/)
