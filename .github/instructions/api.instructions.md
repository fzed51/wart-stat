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
├── router.php             # Définition des routes (groupées par ressource/domaine)
└── wart-stat/
    ├── Base/              # Classes de base (héritage, abstractions)
    │   ├── Controller.php     # Classe abstraite pour les controllers HTTP
    │   └── Database.php       # Utilitaires de base de données
    ├── Report/            # Ressource: Rapports
    │   ├── ReportController.php
    │   ├── ReportRepository.php
    │   ├── ReportValidator.php
    │   └── ReportParser.php
    ├── Repository/        # Classes de repository génériques
    └── Service/           # Services métier réutilisables
```

### Pattern d'organisation par ressource/domaine

Chaque ressource ou domaine métier (**Report**, **User**, **Analytics**, etc.) suit ce pattern:

1. **Dossier dédié**: Une ressource = Un dossier dans `wart-stat/`
2. **Controller**: Dans le dossier de la ressource, hérite de `WartStat\Base\Controller`
3. **Dépendances métier**: Repository, Validator, Service au même endroit
4. **Route groupée**: Définie dans `router.php` avec un groupe `/ressource`

## Principes de développement

### 1. Architecture par ressource/domaine
- **Une ressource = Un dossier**: Ex: `Report/`, `User/`, `Analytics/`
- **Un Controller par ressource**: Hérite de `WartStat\Base\Controller`
- **Dépendances colocalisées**: Repository, Validator, Service dans le même dossier
- **Namespace unifié**: Tous les fichiers d'une ressource sont dans `WartStat\ResourceName`

**Exemple - Ressource Report**:
```
wart-stat/Report/
├── ReportController.php (namespace WartStat\Report)
├── ReportRepository.php (namespace WartStat\Report)
├── ReportValidator.php (namespace WartStat\Report)
└── ReportParser.php (namespace WartStat\Report)
```

### 2. Injection de dépendances (PHP-DI avec autowiring)
- **PHP-DI utilise l'autowiring**: Les dépendances sont automatiquement résolues sans configuration explicite
- **Type-hinting requis**: Les paramètres du constructeur doivent être correctement typés pour que PHP-DI resolve automatiquement
- **Injection par constructeur**: Les dépendances sont déclarées dans le constructeur avec leur type (ex: `private ReportRepository $repository`)
- **Configuration optionnelle**: Pour les cas complexes, configure explicitement les services dans `container.php` avec `DI\autowire()`
- **constructeur recevant un paramètre ne pouvant pas être autowired**: Si un paramètre ne peut pas être autowired (ex: une configuration spécifique, un `string`), il doit être configuré explicitement dans `container.php`
- **Slim Bridge**: Intègre PHP-DI pour l'accès centralisé aux services
- **Interfaces recommandées**: Pour une meilleure maintenabilité, les services doivent implémenter des interfaces clairement définies

### 3. Contrôleurs HTTP
- **Classe**: Hérite obligatoirement de `WartStat\Base\Controller`
- **Responsabilités**:
  - Parser la requête HTTP
  - Valider les données (via Validator)
  - Déléguer la logique métier (Repository, Service, Handler)
  - Formater la réponse JSON
- **Méthodes héritées**: `makeJsonResponse()`, `parseRequestBody()`

### 4. Couches métier
- **Controllers**: Traitent les requêtes HTTP
- **Repositories**: Accès et persistance des données
- **Validators**: Validation des données métier
- **Services**: Logique métier complexe (optionnel selon les besoins)
- **Handlers**: Gestion des événements ou des actions spécifiques
- **Models**: Représentation des données

### 5. Gestion des rapports (ressource Report)
- Les rapports sont gérés via la classe `WartStat\Report\ReportController`
- Données stockées en SQLite (table `reports`) maintenue par `ReportRepository`
- Fichiers bruts optionnels dans `./report/` en `.txt` (importation/export)
- CRUD complet: créer, lire, mettre à jour, supprimer des rapports
- Validation des pays et des données dans `ReportValidator`

### 6. Logging
- Utilise Monolog pour tous les logs
- Configuration dans les dépendances du `container.php`
- Récupérer le logger: `$container->get(Psr\Log\LoggerInterface::class)`
- Les Controllers reçoivent le logger par injection

## Conventions de code

### Structure et namespace
Toutes les classes d'une ressource utilisent le namespace `WartStat\ResourceName`:
```php
namespace WartStat\Report;

class ReportController {
    // ...
}
```

### Typage strict
Chaque fichier doit déclarer le mode strict et tous les types:
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
        // Implémentation
    }
}
```

### Injection de dépendances au constructeur
PHP-DI **résout automatiquement** les dépendances grâce à l'autowiring. Il suffit de typer les paramètres du constructeur:

```php
<?php
declare(strict_types=1);

namespace WartStat\Report;

use Monolog\Logger;
use WartStat\Base\Controller;

class ReportController extends Controller
{
    // PHP-DI autowire automatiquement: cherche une classe ReportRepository,
    // ReportValidator et Logger enregistrée, puis les injecte
    public function __construct(
        private ReportRepository $repository,    // Typé → autowired
        private ReportValidator $validator,      // Typé → autowired
        private Logger $logger                   // Typé → autowired
    ) {
    }
}
```

**Sans configuration explicite dans `container.php`** - PHP-DI cherche:
1. Une classe avec ce nom exact
2. Une interface implémentée par une classe enregistrée
3. Si non trouvé → Erreur de résolution

Pour **forcer une configuration explicite** (ex: instance spécifique):
```php
// Dans container.php
return [
    ReportRepository::class => DI\autowire(),  // Créer une instance automatiquement
    Logger::class => function() { return new Logger('app'); }, // Configuration personnalisée
];
```

### Gestion des erreurs et exceptions
- Lève des exceptions appropriées (`InvalidArgumentException`, `RuntimeException`)
- Utilise le middleware Slim pour gérer les exceptions globalement
- Retourne des réponses JSON cohérentes avec code d'erreur (400, 401, 404, 500)

### Logging
```php
// Log d'entrée de méthode
$this->logger->info('~list~');

// Log d'erreur
$this->logger->error('Erreur lors de la création du rapport', ['data' => $data]);
```

## Routes et endpoints

Les routes sont définies dans `router.php` **organisées par groupe de ressource**.

### Structure des routes

Chaque ressource/domaine a un **groupe de routes** correspondant:

```php
use Slim\App;
use Slim\Routing\RouteCollectorProxy as Group;
use WartStat\Report\ReportController;

return function (App $app) {  
    // Groupe de routes pour la ressource "Reports"
    $app->group('/reports', function (Group $group) {
        // Liste tous les rapports
        $group->get('', [ReportController::class, 'list']);
        
        // Récupère un rapport par ID
        $group->get('/{id}', [ReportController::class, 'getById']);
        
        // Crée un nouveau rapport
        $group->post('', [ReportController::class, 'create']);
        
        // Met à jour un rapport
        $group->put('/{id}', [ReportController::class, 'update']);
        
        // Supprime un rapport
        $group->delete('/{id}', [ReportController::class, 'delete']);
    });
};
```

### Conventions de nommage
- **Chemin**: `/ressource` (ex: `/reports`, `/users`, `/analytics`)
- **Classe Controller**: `ResourceController` (ex: `ReportController`)
- **Méthode GET (liste)**: `list(Response $response)`
- **Méthode GET (item)**: `getById(Request $request, Response $response)`
- **Méthode POST**: `create(Request $request, Response $response)`
- **Méthode PUT**: `update(Request $request, Response $response)`
- **Méthode DELETE**: `delete(Request $request, Response $response)`

## Exécution et test

### Démarrage en développement
```bash
docker-compose up backend  # Démarre le service backend
```

### Tests
- Actuellement pas de suite de tests formelle
- Testez les endpoints via le client du frontend ou Postman

## Implémenter une nouvelle ressource

### Étapes

1. **Créer le dossier de la ressource**
   ```
   wart-stat/NewResource/
   ```

2. **Créer le Controller** (`NewResourceController.php`)
   ```php
   <?php
   declare(strict_types=1);
   
   namespace WartStat\NewResource;
   
   use Psr\Http\Message\ResponseInterface as Response;
   use Psr\Http\Message\ServerRequestInterface as Request;
   use WartStat\Base\Controller;
   
   class NewResourceController extends Controller
   {
       public function __construct(
           private NewResourceRepository $repository,
           private NewResourceValidator $validator,
           private Logger $logger
       ) {
       }
   
       public function list(Response $response): Response
       {
           $this->logger->info('~list~');
           $items = $this->repository->findAll();
           return $this->makeJsonResponse($response, 200, $items);
       }
   
       public function create(Request $request, Response $response): Response
       {
           $this->logger->info('~create~');
           $data = $this->parseRequestBody($request);
           
           // Validation
           if (!$this->validator->safeValidate($data)) {
               return $this->makeJsonResponse($response, 400, ['errors' => $this->validator->getErrors()]);
           }
           
           $item = $this->repository->create($data);
           return $this->makeJsonResponse($response, 201, $item);
       }
   }
   ```

3. **Créer le Repository** (`NewResourceRepository.php`)
   ```php
   <?php
   declare(strict_types=1);
   
   namespace WartStat\NewResource;
   
   use Monolog\Logger;
   use PDO;
   
   class NewResourceRepository
   {
       public function __construct(private PDO $pdo, private Logger $logger)
       {
           $this->ensureTableExists();
       }
   
       public function findAll(): array
       {
           // Implémentation
       }
   
       public function create(array $data): array
       {
           // Implémentation
       }
   }
   ```

4. **Créer le Validator** (`NewResourceValidator.php`)
   ```php
   <?php
   declare(strict_types=1);
   
   namespace WartStat\NewResource;
   
   class NewResourceValidator
   {
       private array $errors = [];
   
       public function safeValidate(?array $data): bool
       {
           $this->errors = [];
           // Implémentation des règles de validation
           return true;
       }
   
       public function getErrors(): array
       {
           return $this->errors;
       }
   }
   ```

5. **Enregistrer les dépendances dans `container.php`**
   ```php
   use WartStat\NewResource\NewResourceController;
   use WartStat\NewResource\NewResourceRepository;
   use WartStat\NewResource\NewResourceValidator;
   
   return [
       NewResourceRepository::class => DI\autowire(),
       NewResourceValidator::class => DI\autowire(),
       NewResourceController::class => DI\autowire(),
   ];
   ```

6. **Ajouter les routes dans `router.php`**
   ```php
   use WartStat\NewResource\NewResourceController;
   
   $app->group('/newresources', function (Group $group) {
       $group->get('', [NewResourceController::class, 'list']);
       $group->post('', [NewResourceController::class, 'create']);
   });
   ```

## Dépannage courant

| Problème | Cause probable | Solution |
|----------|----------|----------|
| "Classe non trouvée" | Namespace ou PSR-4 incorrect | Vérifie le namespace et le chemin PSR-4 dans `composer.json`. Les classes doivent être dans `wart-stat/ResourceName/*.php`. |
| Service non résolu | Dépendance non enregistrée | Ajoute la configuration du service dans `container.php` avec `DI\autowire()`. |
| Erreur injection de dépendance | Controller n'hérite pas de `Controller` ou paramètre mal typé | Vérifie que le controller hérite de `WartStat\Base\Controller` et que les dépendances sont typées. |
| Route non trouvée (404) | Route non définie dans `router.php` | Ajoute la route dans le groupe correspondant, exemple: `$group->get('/{id}', [MyController::class, 'getById']);` |
| Réponse vide ou erreur JSON | `makeJsonResponse()` non appelée correctement | Vérifie que la méthode du controller retourne le résultat de `makeJsonResponse()` avec les bons paramètres (response, code HTTP, données). |
| Erreur PDO | Configuration BD ou permissions | Vérifie la configuration de la base de données, les permissions du fichier SQLite, et que `ensureTableExists()` est appelé. |

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
