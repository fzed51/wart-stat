# War Thunder Reports Database Schema

## Overview

SQLite database schema for storing and analyzing War Thunder battle reports. The schema is organized into interconnected tables following normalization principles for queryability and integrity.

**Database**: `./data/wart_stat.db`  
**Schema Definition**: `./data/schema.sql`  
**Initialization Script**: `./Initialize-Database.ps1`

---

## Database Architecture

### Core Tables

#### `missions` - Main battle records
Each row represents one battle/mission.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INTEGER | PK, AI | Unique mission identifier |
| session_id | TEXT | NOT NULL | War Thunder session token (for grouping) |
| mission_type_id | INTEGER | FK → mission_types | Type of mission (Conquest, Domination, etc.) |
| location | TEXT | NOT NULL | Battle location (e.g., "Ardennes (hiver)") |
| result | TEXT | NOT NULL, CHECK | "Victoire" or "Défaite" |
| total_sl | INTEGER | NOT NULL | Total Silver Lions earned this mission |
| total_crp | INTEGER | NOT NULL | Convertible Research Points |
| total_rp | INTEGER | NOT NULL | Research Points |
| activity_pct | INTEGER | Default 0 | Player activity percentage (0-100) |
| repair_cost | INTEGER | Default 0 | Vehicle repair cost (negative) |
| ammo_crew_cost | INTEGER | Default 0 | Ammunition & crew recovery cost (negative) |
| victory_reward | INTEGER | Nullable | Victory bonus (NULL if defeat) |
| participation_reward | INTEGER | Nullable | Participation bonus (NULL if victory) |
| earned_final | INTEGER | NOT NULL | Net earnings after all costs |
| damaged_vehicle_list | TEXT | Nullable | JSON array of damaged vehicles |
| rescue_used | TEXT | Nullable | JSON array of rescue vehicles deployed |
| created_at | TIMESTAMP | Default CURRENT | Insertion timestamp |
| updated_at | TIMESTAMP | Default CURRENT | Last update timestamp |

#### `actions` - Individual combat actions
Each row represents one kill, assist, damage event, spot, takeoff, landing, etc.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INTEGER | PK, AI | Unique action identifier |
| mission_id | INTEGER | FK → missions | Associated mission |
| type_action | TEXT | NOT NULL | Action type (see Action Types list below) |
| timestamp_sec | INTEGER | NOT NULL | Timestamp in seconds (M:SS format converted) |
| vehicle_id | INTEGER | FK → vehicles | Vehicle performing the action |
| weapon_used | TEXT | Nullable | Weapon/ammo type (NULL for takeoffs/landings) |
| target_name | TEXT | Nullable | Target vehicle name (NULL for takeoffs/landings) |
| point_score | INTEGER | Nullable | Points earned (NULL for takeoffs/landings) |
| sl_awarded | INTEGER | Not NULL, Default 0 | Silver Lions earned (can be 0 for ally-sourced kills) |
| rp_awarded | INTEGER | NOT NULL, Default 0 | Research Points earned (can be 0) |
| created_at | TIMESTAMP | Default CURRENT | Insertion timestamp |

**Action Types** (valid values for `type_action`):
- `destruction_terrestre` - Ground target destruction
- `destruction_avions` - Aircraft destruction
- `destruction_munitions` - Ammunition/bomb destruction
- `assist` - Assisted destruction
- `critical_hit` - Critical hit on enemy
- `damage` - Damage inflicted on enemy
- `damage_important` - High damage inflicted (variant)
- `spot` - Enemy spotted/detected
- `decollage` - Takeoff
- `atterrissage` - Landing
- `capture_zone` - Zone capture
- `ally_sourced_kill` - Kill by ally (no personal reward)

### Bonus Tables

#### `mission_bonuses` - Individual achievement bonuses
Stores Prix (individual bonuses like "Vengeur", "Best Squad", etc.)

| Column | Type | Constraints |
|--------|------|-------------|
| id | INTEGER | PK, AI |
| mission_id | INTEGER | FK → missions |
| bonus_type_id | INTEGER | FK → bonus_types |
| timestamp_sec | INTEGER | NOT NULL |
| sl_awarded | INTEGER | NOT NULL, Default 0 |
| rp_awarded | INTEGER | Nullable |

#### `skill_bonuses` - Crew skill bonuses per vehicle
Bonus de Compétence per vehicle and skill level.

| Column | Type | Constraints |
|--------|------|-------------|
| id | INTEGER | PK, AI |
| mission_id | INTEGER | FK → missions |
| vehicle_id | INTEGER | FK → vehicles |
| skill_level | TEXT | NOT NULL (e.g., "I", "II", "III") |
| rp_awarded | INTEGER | NOT NULL |
| UNIQUE | (mission_id, vehicle_id) | One per vehicle per mission |

### Time/Activity Tables

#### `activity_time` - Activity bonus per vehicle
Temps d'activité section rewards.

| Column | Type | Constraints |
|--------|------|-------------|
| id | INTEGER | PK, AI |
| mission_id | INTEGER | FK → missions |
| vehicle_id | INTEGER | FK → vehicles |
| sl_awarded | INTEGER | NOT NULL, Default 0 |
| rp_awarded | INTEGER | NOT NULL, Default 0 |
| UNIQUE | (mission_id, vehicle_id) | One per vehicle per mission |

#### `play_time` - Play time percentage and RP bonus
Temps Joué section with percentage and duration.

| Column | Type | Constraints |
|--------|------|-------------|
| id | INTEGER | PK, AI |
| mission_id | INTEGER | FK → missions |
| vehicle_id | INTEGER | FK → vehicles |
| percentage | INTEGER | NOT NULL (0-100) |
| duration_sec | INTEGER | NOT NULL |
| rp_awarded | INTEGER | NOT NULL, Default 0 |
| UNIQUE | (mission_id, vehicle_id) | One per vehicle per mission |

### Research Tables

#### `research_target` - Current research target
Unité recherchée - the vehicle/module being researched during this mission.

| Column | Type | Constraints |
|--------|------|-------------|
| id | INTEGER | PK, AI |
| mission_id | INTEGER | UNIQUE FK → missions |
| target_name | TEXT | NOT NULL |
| total_rp_earned | INTEGER | NOT NULL, Default 0 |

#### `research_progress` - Detailed research contribution
Progrès de recherche - how much each vehicle contributed to research.

| Column | Type | Constraints |
|--------|------|-------------|
| id | INTEGER | PK, AI |
| mission_id | INTEGER | FK → missions |
| contributing_vehicle_id | INTEGER | FK → vehicles |
| research_target_type | TEXT | NOT NULL, CHECK ('vehicle' or 'module') |
| research_target_name | TEXT | NOT NULL |
| rp_contribution | INTEGER | NOT NULL, Default 0 |

### Booster Tables

#### `active_boosters` - Active SL/RP boosters
Objets utilisés - active boosters during the mission.

| Column | Type | Constraints |
|--------|------|-------------|
| id | INTEGER | PK, AI |
| mission_id | INTEGER | FK → missions |
| booster_type | TEXT | NOT NULL, CHECK ('SL' or 'RP') |
| booster_rarity | TEXT | Nullable (e.g., "Commun") |
| total_percentage | INTEGER | NOT NULL, Default 0 |
| details | TEXT | JSON string with breakdown |

### Lookup Tables

#### `mission_types`
All possible mission types (14 unique types identified).

#### `vehicles`
All vehicles encountered in reports.

#### `weapons`
All weapons/ammo types encountered.

#### `bonus_types`
All bonus types encountered (Prix categories).

---

## Initialization

### Prerequisites
- SQLite 3 (download from https://www.sqlite.org/download.html)
- PowerShell 5.0+

### Setup

1. **Initialize the database** (creates schema and seeds lookup tables):
```powershell
.\Initialize-Database.ps1 -SeedData
```

2. **Reset database** (backup existing, create fresh):
```powershell
.\Initialize-Database.ps1 -Force
```

3. **Verify initialization**:
```powershell
sqlite3 .\data\wart_stat.db ".schema"
```

---

## Usage Examples

### Insert a Mission
```sql
INSERT INTO missions (
    session_id, mission_type_id, location, result, 
    total_sl, total_crp, total_rp, activity_pct, 
    repair_cost, ammo_crew_cost, earned_final
) VALUES (
    '5dac257003b3ac2', 1, 'Ardennes (hiver)', 'Victoire',
    23920, 3768, 4791, 85,
    -7541, -900, 15479
);
```

### Insert an Action
```sql
INSERT INTO actions (
    mission_id, type_action, timestamp_sec, vehicle_id,
    weapon_used, target_name, point_score, sl_awarded, rp_awarded
) VALUES (
    1, 'destruction_terrestre', 220, 1,
    'M358 shot', 'Objet 140', 200, 2610, 255
);
```

### Query Total Earnings by Result
```sql
SELECT 
    result,
    COUNT(*) as mission_count,
    SUM(total_sl) as total_sl,
    AVG(total_sl) as avg_sl,
    SUM(total_rp) as total_rp
FROM missions
GROUP BY result;
```

### Find Most Common Action Types
```sql
SELECT 
    type_action,
    COUNT(*) as count,
    SUM(sl_awarded) as total_sl,
    SUM(rp_awarded) as total_rp
FROM actions
GROUP BY type_action
ORDER BY count DESC;
```

### Activities by Vehicle
```sql
SELECT 
    v.vehicle_name,
    COUNT(a.id) as action_count,
    SUM(a.sl_awarded) as total_sl,
    SUM(a.rp_awarded) as total_rp
FROM actions a
JOIN vehicles v ON a.vehicle_id = v.id
GROUP BY a.vehicle_id
ORDER BY total_rp DESC;
```

---

## Constraints & Integrity

- **Foreign Keys**: Strict mode enabled. All references must exist in lookup tables.
- **Result Values**: Only 'Victoire' or 'Défaite' allowed.
- **Booster Type**: Only 'SL' or 'RP'.
- **Research Target Type**: Only 'vehicle' or 'module'.
- **Cascade Deletes**: Deleting a mission cascades to all related actions, bonuses, research data.
- **Unique Constraints**: 
  - One skill_bonus per (mission, vehicle)
  - One activity_time per (mission, vehicle)
  - One play_time per (mission, vehicle)
  - One research_target per mission

---

## Performance Considerations

### Indexes Created
- `idx_missions_session_id` - For grouping missions by session
- `idx_missions_result` - For filtering by result (Victory/Defeat)
- `idx_actions_mission_id` - Fast mission action lookup
- `idx_actions_vehicle_id` - Fast vehicle action lookup
- `idx_actions_type_action` - Fast action type filtering

### Query Optimization Tips
- Always use indexed columns in WHERE clauses (mission_id, vehicle_id, result)
- Use GROUP BY carefully on large action sets (1M+ rows)
- Pre-filter by mission before aggregating (narrow scope early)

---

## Future Enhancements

- [ ] Add `parsed_from_report_id` column to missions (link to source report file)
- [ ] Add JSON-B columns for semi-structured bonus details
- [ ] Add view layer for complex aggregations (most active vehicles, best mission types, etc.)
- [ ] Implement database versioning/migrations
- [ ] Add full-text search on vehicle/weapon names
