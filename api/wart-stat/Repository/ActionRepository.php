<?php

declare(strict_types=1);

namespace WartStat\Repository;

/**
 * Repository for Action records
 */
class ActionRepository extends BaseRepository
{
    protected string $table = 'actions';

    /**
     * Create a new action
     *
     * @param array $data Action data
     * @return int Action ID
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO actions (
            mission_id, type_action, timestamp_sec, vehicle_id,
            weapon_used, target_name, point_score, sl_awarded, rp_awarded
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['mission_id'],
            $data['type_action'],
            $data['timestamp_sec'],
            $data['vehicle_id'],
            $data['weapon_used'] ?? null,
            $data['target_name'] ?? null,
            $data['point_score'] ?? null,
            $data['sl_awarded'] ?? 0,
            $data['rp_awarded'] ?? 0,
        ];

        return $this->executeInsert($sql, $params);
    }

    /**
     * Find actions by mission
     *
     * @param int $missionId
     * @return array
     */
    public function findByMission(int $missionId): array
    {
        $sql = "SELECT a.*, v.vehicle_name FROM actions a
                LEFT JOIN vehicles v ON a.vehicle_id = v.id
                WHERE a.mission_id = ?
                ORDER BY a.timestamp_sec ASC";

        return $this->db->fetchAll($sql, [$missionId]);
    }

    /**
     * Find actions by type
     *
     * @param string $type Action type
     * @param int|null $limit
     * @return array
     */
    public function findByType(string $type, ?int $limit = null): array
    {
        $sql = "SELECT a.*, v.vehicle_name, m.id as mission_id FROM actions a
                LEFT JOIN vehicles v ON a.vehicle_id = v.id
                LEFT JOIN missions m ON a.mission_id = m.id
                WHERE a.type_action = ?
                ORDER BY a.created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
        }

        return $this->db->fetchAll($sql, [$type]);
    }

    /**
     * Find actions by vehicle
     *
     * @param int $vehicleId
     * @return array
     */
    public function findByVehicle(int $vehicleId): array
    {
        $sql = "SELECT a.*, m.session_id, m.result FROM actions a
                LEFT JOIN missions m ON a.mission_id = m.id
                WHERE a.vehicle_id = ?
                ORDER BY a.created_at DESC";

        return $this->db->fetchAll($sql, [$vehicleId]);
    }

    /**
     * Get action statistics by type
     *
     * @return array
     */
    public function getStatisticsByType(): array
    {
        $sql = "SELECT
                type_action,
                COUNT(*) as count,
                SUM(sl_awarded) as total_sl,
                SUM(rp_awarded) as total_rp,
                AVG(sl_awarded) as avg_sl,
                AVG(rp_awarded) as avg_rp
            FROM actions
            GROUP BY type_action
            ORDER BY count DESC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Get vehicle action statistics
     *
     * @return array
     */
    public function getVehicleStatistics(): array
    {
        $sql = "SELECT
                v.id,
                v.vehicle_name,
                COUNT(a.id) as action_count,
                SUM(a.sl_awarded) as total_sl,
                SUM(a.rp_awarded) as total_rp,
                ROUND(AVG(a.sl_awarded), 2) as avg_sl_per_action,
                ROUND(AVG(a.rp_awarded), 2) as avg_rp_per_action
            FROM actions a
            JOIN vehicles v ON a.vehicle_id = v.id
            GROUP BY a.vehicle_id
            ORDER BY total_rp DESC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Count actions for mission
     *
     * @param int $missionId
     * @return int
     */
    public function countByMission(int $missionId): int
    {
        $sql = "SELECT COUNT(*) as count FROM actions WHERE mission_id = ?";
        return $this->db->count($sql, [$missionId]);
    }

    /**
     * Get total earnings from actions for mission
     *
     * @param int $missionId
     * @return array
     */
    public function getEarningsByMission(int $missionId): array
    {
        $sql = "SELECT
                SUM(sl_awarded) as total_sl,
                SUM(rp_awarded) as total_rp,
                COUNT(*) as action_count
            FROM actions
            WHERE mission_id = ?";

        return $this->db->fetchOne($sql, [$missionId]) ?? ['total_sl' => 0, 'total_rp' => 0, 'action_count' => 0];
    }

    /**
     * Get most profitable action types
     *
     * @param int $limit
     * @return array
     */
    public function getMostProfitableActions(int $limit = 10): array
    {
        $sql = "SELECT
                type_action,
                COUNT(*) as count,
                SUM(sl_awarded) as total_sl,
                SUM(rp_awarded) as total_rp,
                ROUND(AVG(sl_awarded), 2) as avg_sl,
                ROUND(AVG(rp_awarded), 2) as avg_rp
            FROM actions
            GROUP BY type_action
            ORDER BY total_rp DESC
            LIMIT ?";

        return $this->db->fetchAll($sql, [$limit]);
    }
}
