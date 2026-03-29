<?php

declare(strict_types=1);

namespace WartStat\Repository;

/**
 * Repository for Vehicle records
 */
class VehicleRepository extends BaseRepository
{
    protected string $table = 'vehicles';

    /**
     * Create or get a vehicle (upsert)
     *
     * @param string $vehicleName
     * @param int|null $tier
     * @param string|null $nation
     * @return int Vehicle ID
     */
    public function createOrGet(string $vehicleName, ?int $tier = null, ?string $nation = null): int
    {
        // Check if exists
        $existing = $this->findByName($vehicleName);
        if ($existing) {
            return (int) $existing['id'];
        }

        // Create new
        $sql = "INSERT INTO vehicles (vehicle_name, vehicle_tier, nation) VALUES (?, ?, ?)";
        return $this->executeInsert($sql, [$vehicleName, $tier, $nation]);
    }

    /**
     * Find vehicle by name
     *
     * @param string $name
     * @return array|null
     */
    public function findByName(string $name): ?array
    {
        $sql = "SELECT * FROM vehicles WHERE vehicle_name = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$name]);
    }

    /**
     * Get all vehicles with action statistics
     *
     * @return array
     */
    public function getAllWithStats(): array
    {
        $sql = "SELECT
                v.*,
                COUNT(DISTINCT a.id) as action_count,
                SUM(a.sl_awarded) as total_sl,
                SUM(a.rp_awarded) as total_rp,
                COUNT(DISTINCT a.mission_id) as mission_count
            FROM vehicles v
            LEFT JOIN actions a ON v.id = a.vehicle_id
            GROUP BY v.id
            ORDER BY total_rp DESC";

        return $this->db->fetchAll($sql);
    }
}

/**
 * Repository for Mission Type records (Lookup table)
 */
class MissionTypeRepository extends BaseRepository
{
    protected string $table = 'mission_types';

    /**
     * Find mission type by name
     *
     * @param string $typeName
     * @return array|null
     */
    public function findByName(string $typeName): ?array
    {
        $sql = "SELECT * FROM mission_types WHERE mission_type = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$typeName]);
    }

    /**
     * Create or get mission type
     *
     * @param string $typeName
     * @return int Type ID
     */
    public function createOrGet(string $typeName): int
    {
        $existing = $this->findByName($typeName);
        if ($existing) {
            return (int) $existing['id'];
        }

        $sql = "INSERT INTO mission_types (mission_type) VALUES (?)";
        return $this->executeInsert($sql, [$typeName]);
    }
}

/**
 * Repository for Bonus Type records (Lookup table)
 */
class BonusTypeRepository extends BaseRepository
{
    protected string $table = 'bonus_types';

    /**
     * Find bonus type by name
     *
     * @param string $bonusName
     * @return array|null
     */
    public function findByName(string $bonusName): ?array
    {
        $sql = "SELECT * FROM bonus_types WHERE bonus_name = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$bonusName]);
    }

    /**
     * Create or get bonus type
     *
     * @param string $bonusName
     * @return int Bonus type ID
     */
    public function createOrGet(string $bonusName): int
    {
        $existing = $this->findByName($bonusName);
        if ($existing) {
            return (int) $existing['id'];
        }

        $sql = "INSERT INTO bonus_types (bonus_name) VALUES (?)";
        return $this->executeInsert($sql, [$bonusName]);
    }
}

/**
 * Repository for Weapon records (Lookup table)
 */
class WeaponRepository extends BaseRepository
{
    protected string $table = 'weapons';

    /**
     * Find weapon by name
     *
     * @param string $weaponName
     * @return array|null
     */
    public function findByName(string $weaponName): ?array
    {
        $sql = "SELECT * FROM weapons WHERE weapon_name = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$weaponName]);
    }

    /**
     * Create or get weapon
     *
     * @param string $weaponName
     * @return int Weapon ID
     */
    public function createOrGet(string $weaponName): int
    {
        $existing = $this->findByName($weaponName);
        if ($existing) {
            return (int) $existing['id'];
        }

        $sql = "INSERT INTO weapons (weapon_name) VALUES (?)";
        return $this->executeInsert($sql, [$weaponName]);
    }
}
