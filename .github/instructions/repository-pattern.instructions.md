---
name: repository-Pattern
description: "Pattern standardisé pour créer des Repository classes dans le projet wart-stat. Chaque ressource/domaine dispose d'un Repository responsable de la persistance des données en SQLite via PDO."
applyTo: "api/**"
---
# Repository Pattern - wart-stat

## Structure standard

### Template de classe

```php
<?php
declare(strict_types=1);

namespace WartStat\ResourceName;

use Monolog\Logger;
use PDO;

class ResourceNameRepository
{
    public function __construct(private PDO $pdo, private Logger $logger)
    {
        $this->ensureTableExists();
    }

    private function ensureTableExists(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS resource_table (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                /* Colonnes métier */
                created_at TEXT default (replace(CURRENT_TIMESTAMP, ' ', 'T') || 'Z')
            )
        ");
    }

    public function create(array $data): array
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO resource_table (/* colonnes */)
            VALUES (/* placeholders */)
        ');
        
        $stmt->execute([
            /* 'colonne' => $data['champ'] */
        ]);
        
        $id = (int) $this->pdo->lastInsertId();
        $this->logger->debug("Resource created with ID: $id");
        return $this->findById($id);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM resource_table WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM resource_table ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public function update(int $id, array $data): ?array
    {
        $stmt = $this->pdo->prepare('
            UPDATE resource_table 
            SET /* colonnes = :attributs */
            WHERE id = :id
        ');
        
        $stmt->execute(array_merge($data, ['id' => $id]));
        $this->logger->debug("Resource updated with ID: $id");
        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM resource_table WHERE id = :id');
        $result = $stmt->execute(['id' => $id]);
        $this->logger->debug("Resource deleted with ID: $id");
        return $result;
    }
}
```

---

## Principes clés

### 1. **Namespace et nommage**
- **Namespace**: `WartStat\ResourceName` (ex: `WartStat\User`, `WartStat\Analytics`)
- **Classe**: `ResourceNameRepository` (ex: `UserRepository`, `AnalyticsRepository`)
- **Emplacement**: `wart-stat/ResourceName/ResourceNameRepository.php`

### 2. **Dépendances injectées**
- **PDO**: Instance de base de données SQLite
- **Logger**: Monolog\Logger pour la journalisation
- Injection via constructeur typé (PHP-DI autowire automatiquement)

```php
public function __construct(private PDO $pdo, private Logger $logger)
{
    $this->ensureTableExists();
}
```

### 3. **Typage strict**
- `declare(strict_types=1);` en tête du fichier
- Tous les paramètres et retours doivent être typés
- Retours couramment utilisés: `array`, `?array`, `bool`, `int`

### 4. **Table initialization**
- Méthode `ensureTableExists()` appelée au constructeur
- `CREATE TABLE IF NOT EXISTS` pour garantir idempotence
- Colonne `created_at` par défaut en format ISO-8601

### 5. **Opérations CRUD de base**

#### `create(array $data): array`
- Insère un nouvel enregistrement
- Récupère l'ID généré avec `lastInsertId()`
- Retourne l'enregistrement complet via `findById()`
- Log: `debug("Resource created with ID: $id")`

#### `findById(int $id): ?array`
- Retourne un enregistrement par ID ou `null`
- `?array` indique un retour nullable
- Pattern: `$stmt->fetch() ?: null`

#### `findAll(): array`
- Retourne tous les enregistrements
- Ordre par défaut: `ORDER BY created_at DESC` (le plus récent en premier)
- Retourne un tableau vide si aucun enregistrement

#### `update(int $id, array $data): ?array`
- Met à jour un enregistrement
- Retourne l'enregistrement mis à jour ou `null`
- Log: `debug("Resource updated with ID: $id")`

#### `delete(int $id): bool`
- Supprime un enregistrement
- Retourne `true` si succès
- Log: `debug("Resource deleted with ID: $id")`

### 6. **Requêtes SQL paramétrées**

**Toujours utiliser des paramètres nommés** pour éviter les injections SQL:

```php
// ✅ BON: Requête paramétrée
$stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute(['id' => $id]);

// ❌ MAUVAIS: Concaténation directe
$result = $pdo->query("SELECT * FROM users WHERE id = $id");
```

### 7. **Logging**
- Log à niveau `debug` pour les opérations réussies
- Inclure l'action (created, updated, deleted) et l'ID
- Format: `"Resource [action] with ID: $id"`

```php
$this->logger->debug("Report created with ID: $id");
$this->logger->debug("User updated with ID: $userId");
$this->logger->debug("Setting deleted with ID: $id");
```

---

## Exemple complet: UserRepository

```php
<?php
declare(strict_types=1);

namespace WartStat\User;

use Monolog\Logger;
use PDO;

class UserRepository
{
    public function __construct(private PDO $pdo, private Logger $logger)
    {
        $this->ensureTableExists();
    }

    private function ensureTableExists(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                email TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                role TEXT DEFAULT 'user',
                created_at TEXT default (replace(CURRENT_TIMESTAMP, ' ', 'T') || 'Z')
            )
        ");
    }

    public function create(array $data): array
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO users (username, email, password_hash, role)
            VALUES (:username, :email, :password_hash, :role)
        ');
        
        $stmt->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'role' => $data['role'] ?? 'user',
        ]);
        
        $id = (int) $this->pdo->lastInsertId();
        $this->logger->debug("User created with ID: $id");
        return $this->findById($id);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        return $stmt->fetch() ?: null;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public function update(int $id, array $data): ?array
    {
        $allowed = ['email', 'role'];
        $updates = array_intersect_key($data, array_flip($allowed));
        
        if (empty($updates)) {
            return $this->findById($id);
        }
        
        $sets = implode(', ', array_map(fn($col) => "$col = :$col", array_keys($updates)));
        $stmt = $this->pdo->prepare("UPDATE users SET $sets WHERE id = :id");
        $stmt->execute(array_merge($updates, ['id' => $id]));
        
        $this->logger->debug("User updated with ID: $id");
        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        $result = $stmt->execute(['id' => $id]);
        $this->logger->debug("User deleted with ID: $id");
        return $result;
    }
}
```

---

## Checklist de création

- [ ] Fichier créé dans `wart-stat/ResourceName/ResourceNameRepository.php`
- [ ] Namespace: `WartStat\ResourceName`
- [ ] `declare(strict_types=1);` en tête
- [ ] Dépendances injectées: `PDO`, `Logger`
- [ ] `ensureTableExists()` appelée au constructeur
- [ ] Toutes les méthodes sont typées (paramètres et retours)
- [ ] Requêtes SQL paramétrées
- [ ] CRUD complet implémenté (create, findById, findAll, update, delete)
- [ ] Logging à niveau debug
- [ ] Classe injectée dans le Controller correspondant
