<?php

declare(strict_types=1);

namespace WartStat\Report;

use Monolog\Logger;
use PDO;

class MissionActionRepository
{
    public function __construct(private PDO $pdo, private Logger $logger)
    {
        $this->ensureTableExists();
    }

    private function ensureTableExists(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS mission_actions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                mission_id INTEGER NOT NULL,
                type_action TEXT NOT NULL,
                timestamp_sec INTEGER DEFAULT 0,
                vehicle_name TEXT NOT NULL,
                weapon_used TEXT,
                target_name TEXT,
                point_score INTEGER DEFAULT 0,
                sl_awarded INTEGER DEFAULT 0,
                rp_awarded INTEGER DEFAULT 0,
                created_at TEXT default (replace(CURRENT_TIMESTAMP, ' ', 'T') || 'Z'),
                FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE
            )
        ");
    }

    public function create(array $data): array
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO mission_actions (
                mission_id, type_action, timestamp_sec, vehicle_name,
                weapon_used, target_name, point_score, sl_awarded, rp_awarded
            )
            VALUES (
                :mission_id, :type_action, :timestamp_sec, :vehicle_name,
                :weapon_used, :target_name, :point_score, :sl_awarded, :rp_awarded
            )
        ');

        $stmt->execute([
            'mission_id' => $data['mission_id'],
            'type_action' => $data['type_action'] ?? 'Unknown',
            'timestamp_sec' => $data['timestamp_sec'] ?? 0,
            'vehicle_name' => $data['vehicle_name'] ?? 'Unknown',
            'weapon_used' => $data['weapon_used'] ?? null,
            'target_name' => $data['target_name'] ?? null,
            'point_score' => $data['point_score'] ?? 0,
            'sl_awarded' => $data['sl_awarded'] ?? 0,
            'rp_awarded' => $data['rp_awarded'] ?? 0,
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $this->logger->debug("MissionAction created with ID: $id");
        return $this->findById($id);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM mission_actions WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByMissionId(int $missionId, int $limit = 1000): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM mission_actions 
            WHERE mission_id = :mission_id 
            ORDER BY timestamp_sec ASC 
            LIMIT :limit
        ');
        $stmt->bindValue(':mission_id', $missionId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByActionType(string $actionType, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM mission_actions 
            WHERE type_action = :type_action 
            ORDER BY created_at DESC 
            LIMIT :limit
        ');
        $stmt->bindValue(':type_action', $actionType, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findAll(int $limit = 1000, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM mission_actions 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset
        ');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update(int $id, array $data): ?array
    {
        $allowed = ['type_action', 'timestamp_sec', 'vehicle_name', 'weapon_used', 
                   'target_name', 'point_score', 'sl_awarded', 'rp_awarded'];
        $updates = array_intersect_key($data, array_flip($allowed));

        if (empty($updates)) {
            return $this->findById($id);
        }

        $sets = implode(', ', array_map(fn($col) => "$col = :$col", array_keys($updates)));
        $stmt = $this->pdo->prepare("UPDATE mission_actions SET $sets WHERE id = :id");
        $stmt->execute(array_merge($updates, ['id' => $id]));

        $this->logger->debug("MissionAction updated with ID: $id");
        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM mission_actions WHERE id = :id');
        $result = $stmt->execute(['id' => $id]);
        $this->logger->debug("MissionAction deleted with ID: $id");
        return $result;
    }

    public function deleteByMissionId(int $missionId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM mission_actions WHERE mission_id = :mission_id');
        $result = $stmt->execute(['mission_id' => $missionId]);
        $this->logger->debug("MissionActions deleted for mission ID: $missionId");
        return $result;
    }

    public function countByMissionId(int $missionId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM mission_actions WHERE mission_id = :mission_id');
        $stmt->execute(['mission_id' => $missionId]);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }
}
