<?php

declare(strict_types=1);

namespace WartStat\Report;

use Monolog\Logger;
use PDO;

class MissionBonusRepository
{
    public function __construct(private PDO $pdo, private Logger $logger)
    {
        $this->ensureTableExists();
    }

    private function ensureTableExists(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS mission_bonuses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                mission_id INTEGER NOT NULL,
                bonus_name TEXT NOT NULL,
                timestamp_sec INTEGER DEFAULT 0,
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
            INSERT INTO mission_bonuses (
                mission_id, bonus_name, timestamp_sec, sl_awarded, rp_awarded
            )
            VALUES (
                :mission_id, :bonus_name, :timestamp_sec, :sl_awarded, :rp_awarded
            )
        ');

        $stmt->execute([
            'mission_id' => $data['mission_id'],
            'bonus_name' => $data['bonus_name'] ?? 'Unknown Bonus',
            'timestamp_sec' => $data['timestamp_sec'] ?? 0,
            'sl_awarded' => $data['sl_awarded'] ?? 0,
            'rp_awarded' => $data['rp_awarded'] ?? 0,
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $this->logger->debug("MissionBonus created with ID: $id");
        return $this->findById($id);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM mission_bonuses WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByMissionId(int $missionId, int $limit = 1000): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM mission_bonuses 
            WHERE mission_id = :mission_id 
            ORDER BY timestamp_sec ASC 
            LIMIT :limit
        ');
        $stmt->bindValue(':mission_id', $missionId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByBonusName(string $bonusName, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM mission_bonuses 
            WHERE bonus_name = :bonus_name 
            ORDER BY created_at DESC 
            LIMIT :limit
        ');
        $stmt->bindValue(':bonus_name', $bonusName, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findAll(int $limit = 1000, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM mission_bonuses 
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
        $allowed = ['bonus_name', 'timestamp_sec', 'sl_awarded', 'rp_awarded'];
        $updates = array_intersect_key($data, array_flip($allowed));

        if (empty($updates)) {
            return $this->findById($id);
        }

        $sets = implode(', ', array_map(fn($col) => "$col = :$col", array_keys($updates)));
        $stmt = $this->pdo->prepare("UPDATE mission_bonuses SET $sets WHERE id = :id");
        $stmt->execute(array_merge($updates, ['id' => $id]));

        $this->logger->debug("MissionBonus updated with ID: $id");
        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM mission_bonuses WHERE id = :id');
        $result = $stmt->execute(['id' => $id]);
        $this->logger->debug("MissionBonus deleted with ID: $id");
        return $result;
    }

    public function deleteByMissionId(int $missionId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM mission_bonuses WHERE mission_id = :mission_id');
        $result = $stmt->execute(['mission_id' => $missionId]);
        $this->logger->debug("MissionBonuses deleted for mission ID: $missionId");
        return $result;
    }

    public function countByMissionId(int $missionId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM mission_bonuses WHERE mission_id = :mission_id');
        $stmt->execute(['mission_id' => $missionId]);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }

    public function sumSLByMissionId(int $missionId): int
    {
        $stmt = $this->pdo->prepare('SELECT SUM(sl_awarded) as total FROM mission_bonuses WHERE mission_id = :mission_id');
        $stmt->execute(['mission_id' => $missionId]);
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    public function sumRPByMissionId(int $missionId): int
    {
        $stmt = $this->pdo->prepare('SELECT SUM(rp_awarded) as total FROM mission_bonuses WHERE mission_id = :mission_id');
        $stmt->execute(['mission_id' => $missionId]);
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }
}
