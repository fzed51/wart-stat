<?php

declare(strict_types=1);

namespace WartStat\Report;

use Monolog\Logger;
use PDO;

class MissionRepository
{
    public function __construct(private PDO $pdo, private Logger $logger)
    {
        $this->ensureTableExists();
    }

    private function ensureTableExists(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS missions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                report_id INTEGER,
                mission_type TEXT,
                location TEXT,
                result TEXT,
                mission_duration_sec INTEGER DEFAULT 0,
                session_id TEXT,
                total_sl INTEGER DEFAULT 0,
                total_crp INTEGER DEFAULT 0,
                total_rp INTEGER DEFAULT 0,
                activity_pct INTEGER DEFAULT 0,
                repair_cost INTEGER DEFAULT 0,
                ammo_crew_cost INTEGER DEFAULT 0,
                victory_reward INTEGER DEFAULT 0,
                participation_reward INTEGER DEFAULT 0,
                earned_final INTEGER DEFAULT 0,
                created_at TEXT default (replace(CURRENT_TIMESTAMP, ' ', 'T') || 'Z'),
                FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE SET NULL
            )
        ");
    }

    public function create(array $data): array
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO missions (
                report_id, mission_type, location, result, mission_duration_sec, session_id,
                total_sl, total_crp, total_rp, activity_pct, repair_cost,
                ammo_crew_cost, victory_reward, participation_reward, earned_final
            )
            VALUES (
                :report_id, :mission_type, :location, :result, :mission_duration_sec, :session_id,
                :total_sl, :total_crp, :total_rp, :activity_pct, :repair_cost,
                :ammo_crew_cost, :victory_reward, :participation_reward, :earned_final
            )
        ');

        $stmt->execute([
            'report_id' => $data['report_id'] ?? null,
            'mission_type' => $data['mission_type'] ?? null,
            'location' => $data['location'] ?? null,
            'result' => $data['result'] ?? null,
            'mission_duration_sec' => $data['mission_duration_sec'] ?? 0,
            'session_id' => $data['session_id'] ?? null,
            'total_sl' => $data['total_sl'] ?? 0,
            'total_crp' => $data['total_crp'] ?? 0,
            'total_rp' => $data['total_rp'] ?? 0,
            'activity_pct' => $data['activity_pct'] ?? 0,
            'repair_cost' => $data['repair_cost'] ?? 0,
            'ammo_crew_cost' => $data['ammo_crew_cost'] ?? 0,
            'victory_reward' => $data['victory_reward'] ?? 0,
            'participation_reward' => $data['participation_reward'] ?? 0,
            'earned_final' => $data['earned_final'] ?? 0,
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $this->logger->debug("Mission created with ID: $id");
        return $this->findById($id);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM missions WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findBySessionId(string $sessionId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM missions WHERE session_id = :session_id');
        $stmt->execute(['session_id' => $sessionId]);
        return $stmt->fetch() ?: null;
    }

    public function findByReportId(int $reportId, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM missions 
            WHERE report_id = :report_id 
            ORDER BY created_at DESC 
            LIMIT :limit
        ');
        $stmt->bindValue(':report_id', $reportId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findAll(int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM missions 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset
        ');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByResult(string $result, int $limit = 50): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM missions 
            WHERE result = :result 
            ORDER BY created_at DESC 
            LIMIT :limit
        ');
        $stmt->bindValue(':result', $result, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update(int $id, array $data): ?array
    {
        $allowed = ['report_id', 'mission_type', 'location', 'result', 'mission_duration_sec', 'session_id',
                   'total_sl', 'total_crp', 'total_rp', 'activity_pct', 'repair_cost',
                   'ammo_crew_cost', 'victory_reward', 'participation_reward', 'earned_final'];
        $updates = array_intersect_key($data, array_flip($allowed));

        if (empty($updates)) {
            return $this->findById($id);
        }

        $sets = implode(', ', array_map(fn($col) => "$col = :$col", array_keys($updates)));
        $stmt = $this->pdo->prepare("UPDATE missions SET $sets WHERE id = :id");
        $stmt->execute(array_merge($updates, ['id' => $id]));

        $this->logger->debug("Mission updated with ID: $id");
        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM missions WHERE id = :id');
        $result = $stmt->execute(['id' => $id]);
        $this->logger->debug("Mission deleted with ID: $id");
        return $result;
    }

    public function count(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) as count FROM missions');
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }
}
