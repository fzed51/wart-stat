<?php

declare(strict_types=1);

namespace WartStat\Repository;

/**
 * Repository for Mission records
 */
class MissionRepository extends BaseRepository
{
    protected string $table = 'missions';

    /**
     * Create a new mission
     *
     * @param array $data Mission data
     * @return int Mission ID
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO missions (
            session_id, mission_type_id, location, result,
            total_sl, total_crp, total_rp, activity_pct,
            repair_cost, ammo_crew_cost, earned_final,
            victory_reward, participation_reward,
            damaged_vehicle_list, rescue_used
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['session_id'],
            $data['mission_type_id'],
            $data['location'],
            $data['result'],
            $data['total_sl'],
            $data['total_crp'],
            $data['total_rp'],
            $data['activity_pct'] ?? 0,
            $data['repair_cost'] ?? 0,
            $data['ammo_crew_cost'] ?? 0,
            $data['earned_final'],
            $data['victory_reward'] ?? null,
            $data['participation_reward'] ?? null,
            $data['damaged_vehicle_list'] ?? null,
            $data['rescue_used'] ?? null,
        ];

        return $this->executeInsert($sql, $params);
    }

    /**
     * Update a mission
     *
     * @param int $id Mission ID
     * @param array $data Updated data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $updates = [];
        $params = [];

        foreach ($data as $key => $value) {
            $updates[] = "{$key} = ?";
            $params[] = $value;
        }

        $params[] = $id;
        $sql = "UPDATE missions SET " . implode(', ', $updates) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";

        return $this->db->execute($sql, $params);
    }

    /**
     * Find missions by result (Victory/Defeat)
     *
     * @param string $result 'Victoire' or 'Défaite'
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function findByResult(string $result, ?int $limit = null, ?int $offset = null): array
    {
        $sql = "SELECT m.*, mt.mission_type FROM missions m
                LEFT JOIN mission_types mt ON m.mission_type_id = mt.id
                WHERE m.result = ?";

        $params = [$result];

        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
            if ($offset !== null) {
                $sql .= " OFFSET {$offset}";
            }
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Find missions by session ID
     *
     * @param string $sessionId War Thunder session ID
     * @return array
     */
    public function findBySessionId(string $sessionId): array
    {
        $sql = "SELECT m.*, mt.mission_type FROM missions m
                LEFT JOIN mission_types mt ON m.mission_type_id = mt.id
                WHERE m.session_id = ?
                ORDER BY m.created_at DESC";

        return $this->db->fetchAll($sql, [$sessionId]);
    }

    /**
     * Find missions by mission type
     *
     * @param int $missionTypeId
     * @return array
     */
    public function findByMissionType(int $missionTypeId): array
    {
        $sql = "SELECT m.*, mt.mission_type FROM missions m
                LEFT JOIN mission_types mt ON m.mission_type_id = mt.id
                WHERE m.mission_type_id = ?
                ORDER BY m.created_at DESC";

        return $this->db->fetchAll($sql, [$missionTypeId]);
    }

    /**
     * Get mission statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $sql = "SELECT
                COUNT(*) as total_missions,
                SUM(CASE WHEN result = 'Victoire' THEN 1 ELSE 0 END) as victories,
                SUM(CASE WHEN result = 'Défaite' THEN 1 ELSE 0 END) as defeats,
                AVG(total_sl) as avg_sl,
                SUM(total_sl) as total_sl,
                SUM(total_rp) as total_rp,
                AVG(activity_pct) as avg_activity
            FROM missions";

        return $this->db->fetchOne($sql) ?? [];
    }

    /**
     * Get most recent missions with details
     *
     * @param int $limit
     * @return array
     */
    public function getRecentWithDetails(int $limit = 20): array
    {
        $sql = "SELECT m.*, mt.mission_type,
                COUNT(DISTINCT a.id) as action_count,
                SUM(a.sl_awarded) as action_sl,
                SUM(a.rp_awarded) as action_rp
            FROM missions m
            LEFT JOIN mission_types mt ON m.mission_type_id = mt.id
            LEFT JOIN actions a ON m.id = a.mission_id
            GROUP BY m.id
            ORDER BY m.created_at DESC
            LIMIT ?";

        return $this->db->fetchAll($sql, [$limit]);
    }
}
