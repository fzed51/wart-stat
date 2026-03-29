# War Thunder Reports - Repository Layer

## Overview

Complete PHP Repository layer for managing War Thunder battle report data in SQLite database. Implements:
- **Repository Pattern** for data access abstraction
- **Dependency Injection** with PHP-DI
- **PSR-3 Logging** integration
- **Service Layer** for business logic
- **REST API Handlers** for HTTP endpoints

---

## Directory Structure

```
api/wart-stat/
├── Database/
│   └── Database.php                    # SQLite connection manager
├── Repository/
│   ├── BaseRepository.php              # Abstract base class
│   ├── MissionRepository.php           # Mission CRUD + queries
│   ├── ActionRepository.php            # Action CRUD + queries
│   ├── LookupRepositories.php          # Vehicle, MissionType, BonusType, Weapon
│   └── DataRepositories.php            # Bonus, Time, Research, Booster repositories
├── Service/
│   └── MissionDataService.php          # Business logic facade
├── Config/
│   └── services.php                    # DI container configuration
└── Routes/
    └── mission-routes.php              # API route definitions
```

---

## Core Classes

### Database.php
SQLite connection manager with transaction support.

**Key Methods:**
```php
$db = new Database('./data/wart_stat.db', $logger);

$db->execute($sql, $params);           // Execute INSERT/UPDATE/DELETE
$db->fetchOne($sql, $params);          // Fetch single row
$db->fetchAll($sql, $params);          // Fetch multiple rows
$db->insert($sql, $params);            // Insert and get ID
$db->count($sql, $params);             // COUNT queries

$db->beginTransaction();
$db->commit();
$db->rollback();
```

### BaseRepository.php
Abstract base class providing common CRUD operations to all repositories.

**Standard Methods (all repositories inherit):**
```php
$repo->findById($id);                  // Get by ID
$repo->findAll($limit, $offset);       // List all with pagination
$repo->count();                        // Count total records
$repo->delete($id);                    // Delete by ID
```

### Mission/Action Repositories

#### MissionRepository
```php
$missionRepo = $container->get(MissionRepository::class);

// CRUD
$id = $missionRepo->create($data);
$mission = $missionRepo->findById($id);
$missionRepo->update($id, $data);
$missionRepo->delete($id);

// Custom queries
$missions = $missionRepo->findByResult('Victoire');
$missions = $missionRepo->findBySessionId($sessionId);
$missions = $missionRepo->findByMissionType($typeId);
$stats = $missionRepo->getStatistics();
$recent = $missionRepo->getRecentWithDetails(20);
```

#### ActionRepository
```php
$actionRepo = $container->get(ActionRepository::class);

// CRUD
$id = $actionRepo->create($data);
$action = $actionRepo->findById($id);

// Custom queries
$actions = $actionRepo->findByMission($missionId);
$actions = $actionRepo->findByType('destruction_terrestre');
$actions = $actionRepo->findByVehicle($vehicleId);
$stats = $actionRepo->getStatisticsByType();
$stats = $actionRepo->getVehicleStatistics();
$earnings = $actionRepo->getEarningsByMission($missionId);
```

### Lookup Repositories

#### VehicleRepository
```php
$vehicleRepo = $container->get(VehicleRepository::class);

$id = $vehicleRepo->createOrGet('M103', 9, 'USA');  // Create or get ID
$vehicle = $vehicleRepo->findByName('M103');
$vehicles = $vehicleRepo->getAllWithStats();
```

#### MissionTypeRepository, BonusTypeRepository, WeaponRepository
```php
$typeRepo = $container->get(MissionTypeRepository::class);

$id = $typeRepo->createOrGet('Conquest #2');
$type = $typeRepo->findByName('Conquest #2');
```

### Data Repositories

#### MissionBonusRepository
```php
$bonusRepo = $container->get(MissionBonusRepository::class);

$id = $bonusRepo->create($data);
$bonuses = $bonusRepo->findByMission($missionId);
$totals = $bonusRepo->getTotalByMission($missionId);
```

#### SkillBonusRepository, ActivityTimeRepository, PlayTimeRepository
```php
$skillRepo = $container->get(SkillBonusRepository::class);
$bonuses = $skillRepo->findByMission($missionId);
$totalRp = $skillRepo->getTotalRpByMission($missionId);

$activityRepo = $container->get(ActivityTimeRepository::class);
$times = $activityRepo->findByMission($missionId);

$playTimeRepo = $container->get(PlayTimeRepository::class);
$times = $playTimeRepo->findByMission($missionId);
```

#### ResearchPages
```php
$researchRepo = $container->get(ResearchProgressRepository::class);
$progress = $researchRepo->findByMission($missionId);
$totalRp = $researchRepo->getTotalByMission($missionId);
```

---

## Service Layer

### MissionDataService
Facade that aggregates repositories for business logic.

```php
$service = $container->get(MissionDataService::class);

// Get complete mission with all relations
$full = $service->getMissionComplete($missionId);
// Returns: {mission, actions, bonuses, skillBonuses, times, research, ...}

// Get dashboard stats
$stats = $service->getDashboardStats();
// Returns: {missions, actionsByType, vehicleStats, recentMissions}

// Get performance data
$performance = $service->getVehiclePerformance();
$performance = $service->getActionPerformance(15);

// Check database
if ($service->schemaExists()) { ... }

// Access repositories directly
$repos = $service->getRepositories();
$missionRepo = $repos['mission'];
```

---

## DI Container Configuration

### services.php
Register all services in the container:

```php
// In your container.php or bootstrap:
$definitions = include(__DIR__ . '/Config/services.php');

// Or register individually:
$container->set(Database::class, function() {
    return new Database('./data/wart_stat.db', $logger);
});
```

---

## Usage Examples

### Creating a Mission with Actions
```php
$missionRepo = $container->get(MissionRepository::class);
$vehicleRepo = $container->get(VehicleRepository::class);
$actionRepo = $container->get(ActionRepository::class);

// Create mission
$missionId = $missionRepo->create([
    'session_id' => '5dac257003b3ac2',
    'mission_type_id' => 8,
    'location' => 'Ardennes (hiver)',
    'result' => 'Victoire',
    'total_sl' => 23920,
    'total_rp' => 4791,
    ...
]);

// Get/create vehicle
$vehicleId = $vehicleRepo->createOrGet('M103', 9, 'USA');

// Add action
$actionId = $actionRepo->create([
    'mission_id' => $missionId,
    'type_action' => 'destruction_terrestre',
    'timestamp_sec' => 220,
    'vehicle_id' => $vehicleId,
    'weapon_used' => 'M358 shot',
    'target_name' => 'Objet 140',
    'point_score' => 200,
    'sl_awarded' => 2610,
    'rp_awarded' => 255,
]);
```

### Querying Data
```php
$missionRepo = $container->get(MissionRepository::class);

// Get recent victories
$victories = $missionRepo->findByResult('Victoire', 20);

// Get session missions
$missions = $missionRepo->findBySessionId('5dac257003b3ac2');

// Get statistics
$stats = $missionRepo->getStatistics();
// {total_missions, victories, defeats, avg_sl, total_sl, total_rp, avg_activity}
```

### API Endpoint Usage
```bash
# Health check
curl http://localhost:8080/api/health

# Get single mission
curl http://localhost:8080/api/missions/1

# List missions
curl "http://localhost:8080/api/missions?result=Victoire&limit=50"

# Get dashboard stats
curl http://localhost:8080/api/dashboard/stats

# Get vehicle performance
curl http://localhost:8080/api/vehicles/performance

# Get action performance
curl "http://localhost:8080/api/actions/performance?limit=15"
```

---

## Error Handling

All repositories and services use exceptions:
```php
try {
    $mission = $missionRepo->findById(999);
} catch (RuntimeException $e) {
    // Database error
    $logger->error($e->getMessage());
}

try {
    $mission = $service->getMissionComplete(999);
} catch (RuntimeException $e) {
    // Mission not found (service throws)
}
```

---

## Performance Optimization

### Indexes
All repositories use indexed columns:
- `mission_id` for JOIN queries
- `vehicle_id` for vehicle stats
- `type_action` for filtering actions
- `result` for mission filtering
- `session_id` for session grouping

### Pagination
```php
$missions = $missionRepo->findAll(50, 100);  // LIMIT 50 OFFSET 100
$missions = $missionRepo->findByResult('Victoire', 20, 0);
```

---

## Transactions

For multi-step operations:
```php
$db = $container->get(Database::class);

try {
    $db->beginTransaction();
    
    $missionId = $missionRepo->create($missionData);
    $actionRepo->create(['mission_id' => $missionId, ...]);
    $actionRepo->create(['mission_id' => $missionId, ...]);
    
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    throw $e;
}
```
