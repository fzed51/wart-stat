<?php

declare(strict_types=1);

namespace WartStat\Repository;

use WartStat\Database\Database;
use Psr\Log\LoggerInterface;

/**
 * Base Repository Class
 * Provides common CRUD operations for all repositories
 */
abstract class BaseRepository
{
    protected Database $db;
    protected LoggerInterface $logger;
    protected string $table;

    /**
     * @param Database $db Database connection
     * @param LoggerInterface $logger PSR-3 logger
     */
    public function __construct(Database $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Find by ID
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Find all records
     *
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function findAll(?int $limit = null, ?int $offset = null): array
    {
        $sql = "SELECT * FROM {$this->table}";

        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
            if ($offset !== null) {
                $sql .= " OFFSET {$offset}";
            }
        }

        return $this->db->fetchAll($sql);
    }

    /**
     * Count all records
     *
     * @return int
     */
    public function count(): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        return $this->db->count($sql);
    }

    /**
     * Delete by ID
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Execute custom query with logging
     *
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return array
     */
    protected function executeQuery(string $sql, array $params = []): array
    {
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Execute single row query with logging
     *
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return array|null
     */
    protected function executeQueryOne(string $sql, array $params = []): ?array
    {
        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Get insert ID from last insert
     *
     * @param string $sql INSERT statement
     * @param array $params Parameters
     * @return int
     */
    protected function executeInsert(string $sql, array $params = []): int
    {
        return $this->db->insert($sql, $params);
    }
}
