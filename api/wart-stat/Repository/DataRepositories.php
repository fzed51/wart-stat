<?php

declare(strict_types=1);

namespace WartStat\Repository;

/**
 * Repository for Mission Bonus records
 */
class MissionBonusRepository extends BaseRepository
{
    protected string $table = 'mission_bonuses';

    /**
     * Create a new mission bonus
     *
     * @param array $data Bonus data
     * @return int Bonus ID
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO mission_bonuses (
            mission_id, bonus_type_id, timestamp_sec, sl_awarded, rp_awarded
        ) VALUES (?, ?, ?, ?, ?)";

        $params = [
            $data['mission_id'],
            $data['bonus_type_id'],
            $data['timestamp_sec'],
            $data['sl_awarded'] ?? 0,
            $data['rp_awarded'] ?? null,
        ];

        return $this->executeInsert($sql, $params);
    }

    /**
     * Find bonuses by mission
     *
     * @param int $missionId
     * @return array
     */
    public function findByMission(int $missionId): array
    {
        $sql = "SELECT mb.*, bt.bonus_name FROM mission_bonuses mb
                LEFT JOIN bonus_types bt ON mb.bonus_type_id = bt.id
                WHERE mb.mission_id = ?
                ORDER BY mb.timestamp_sec ASC";

        return $this->db->fetchAll($sql, [$missionId]);
    }

    /**
     * Get total bonuses for mission
     *
     * @param int $missionId
     * @return array
     */
    public function getTotalByMission(int $missionId): array
    {
        $sql = "SELECT
                SUM(sl_awarded) as total_sl,
                COALESCE(SUM(rp_awarded), 0) as total_rp,
                COUNT(*) as bonus_count
            FROM mission_bonuses
            WHERE mission_id = ?";

        return $this->db->fetchOne($sql, [$missionId]) ?? ['total_sl' => 0, 'total_rp' => 0, 'bonus_count' => 0];
    }
}

/**
 * Repository for Skill Bonus records
 */
class SkillBonusRepository extends BaseRepository
{
    protected string $table = 'skill_bonuses';

    /**
     * Create a skill bonus
     *
     * @param array $data
     * @return int ID
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO skill_bonuses (
            mission_id, vehicle_id, skill_level, rp_awarded
        ) VALUES (?, ?, ?, ?)";

        return $this->executeInsert($sql, [
            $data['mission_id'],
            $data['vehicle_id'],
            $data['skill_level'],
            $data['rp_awarded'],
        ]);
    }

    /**
     * Find skill bonuses by mission
     *
     * @param int $missionId
     * @return array
     */
    public function findByMission(int $missionId): array
    {
        $sql = "SELECT sb.*, v.vehicle_name FROM skill_bonuses sb
                LEFT JOIN vehicles v ON sb.vehicle_id = v.id
                WHERE sb.mission_id = ?
                ORDER BY sb.skill_level DESC";

        return $this->db->fetchAll($sql, [$missionId]);
    }

    /**
     * Get total RP from skill bonuses for mission
     *
     * @param int $missionId
     * @return int
     */
    public function getTotalRpByMission(int $missionId): int
    {
        $sql = "SELECT COALESCE(SUM(rp_awarded), 0) as total FROM skill_bonuses WHERE mission_id = ?";
        $result = $this->db->fetchOne($sql, [$missionId]);
        return $result['total'] ?? 0;
    }
}

/**
 * Repository for Activity Time records
 */
class ActivityTimeRepository extends BaseRepository
{
    protected string $table = 'activity_time';

    /**
     * Create activity time record
     *
     * @param array $data
     * @return int ID
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO activity_time (
            mission_id, vehicle_id, sl_awarded, rp_awarded
        ) VALUES (?, ?, ?, ?)";

        return $this->executeInsert($sql, [
            $data['mission_id'],
            $data['vehicle_id'],
            $data['sl_awarded'] ?? 0,
            $data['rp_awarded'] ?? 0,
        ]);
    }

    /**
     * Find activity times by mission
     *
     * @param int $missionId
     * @return array
     */
    public function findByMission(int $missionId): array
    {
        $sql = "SELECT at.*, v.vehicle_name FROM activity_time at
                LEFT JOIN vehicles v ON at.vehicle_id = v.id
                WHERE at.mission_id = ?";

        return $this->db->fetchAll($sql, [$missionId]);
    }
}

/**
 * Repository for Play Time records
 */
class PlayTimeRepository extends BaseRepository
{
    protected string $table = 'play_time';

    /**
     * Create play time record
     *
     * @param array $data
     * @return int ID
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO play_time (
            mission_id, vehicle_id, percentage, duration_sec, rp_awarded
        ) VALUES (?, ?, ?, ?, ?)";

        return $this->executeInsert($sql, [
            $data['mission_id'],
            $data['vehicle_id'],
            $data['percentage'],
            $data['duration_sec'],
            $data['rp_awarded'] ?? 0,
        ]);
    }

    /**
     * Find play times by mission
     *
     * @param int $missionId
     * @return array
     */
    public function findByMission(int $missionId): array
    {
        $sql = "SELECT pt.*, v.vehicle_name FROM play_time pt
                LEFT JOIN vehicles v ON pt.vehicle_id = v.id
                WHERE pt.mission_id = ?
                ORDER BY pt.percentage DESC";

        return $this->db->fetchAll($sql, [$missionId]);
    }
}

/**
 * Repository for Research Target records
 */
class ResearchTargetRepository extends BaseRepository
{
    protected string $table = 'research_target';

    /**
     * Create research target
     *
     * @param array $data
     * @return int ID
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO research_target (
            mission_id, target_name, total_rp_earned
        ) VALUES (?, ?, ?)";

        return $this->executeInsert($sql, [
            $data['mission_id'],
            $data['target_name'],
            $data['total_rp_earned'] ?? 0,
        ]);
    }

    /**
     * Find research target by mission
     *
     * @param int $missionId
     * @return array|null
     */
    public function findByMission(int $missionId): ?array
    {
        $sql = "SELECT * FROM research_target WHERE mission_id = ?";
        return $this->db->fetchOne($sql, [$missionId]);
    }
}

/**
 * Repository for Research Progress records
 */
class ResearchProgressRepository extends BaseRepository
{
    protected string $table = 'research_progress';

    /**
     * Create research progress record
     *
     * @param array $data
     * @return int ID
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO research_progress (
            mission_id, contributing_vehicle_id, research_target_type,
            research_target_name, rp_contribution
        ) VALUES (?, ?, ?, ?, ?)";

        return $this->executeInsert($sql, [
            $data['mission_id'],
            $data['contributing_vehicle_id'],
            $data['research_target_type'],
            $data['research_target_name'],
            $data['rp_contribution'] ?? 0,
        ]);
    }

    /**
     * Find research progress by mission
     *
     * @param int $missionId
     * @return array
     */
    public function findByMission(int $missionId): array
    {
        $sql = "SELECT rp.*, v.vehicle_name FROM research_progress rp
                LEFT JOIN vehicles v ON rp.contributing_vehicle_id = v.id
                WHERE rp.mission_id = ?
                ORDER BY rp.rp_contribution DESC";

        return $this->db->fetchAll($sql, [$missionId]);
    }

    /**
     * Get total research RP by mission
     *
     * @param int $missionId
     * @return int
     */
    public function getTotalByMission(int $missionId): int
    {
        $sql = "SELECT COALESCE(SUM(rp_contribution), 0) as total FROM research_progress WHERE mission_id = ?";
        $result = $this->db->fetchOne($sql, [$missionId]);
        return $result['total'] ?? 0;
    }
}

/**
 * Repository for Active Boosters records
 */
class ActiveBoosterRepository extends BaseRepository
{
    protected string $table = 'active_boosters';

    /**
     * Create booster record
     *
     * @param array $data
     * @return int ID
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO active_boosters (
            mission_id, booster_type, booster_rarity, total_percentage, details
        ) VALUES (?, ?, ?, ?, ?)";

        return $this->executeInsert($sql, [
            $data['mission_id'],
            $data['booster_type'],
            $data['booster_rarity'] ?? null,
            $data['total_percentage'] ?? 0,
            $data['details'] ?? null,
        ]);
    }

    /**
     * Find boosters by mission
     *
     * @param int $missionId
     * @return array
     */
    public function findByMission(int $missionId): array
    {
        $sql = "SELECT * FROM active_boosters WHERE mission_id = ? ORDER BY booster_type";
        return $this->db->fetchAll($sql, [$missionId]);
    }
}
