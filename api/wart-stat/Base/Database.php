<?php

declare(strict_types=1);

namespace WartStat\Base;

use PDO;
use PDOException;
use RuntimeException;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Database Connection Manager for SQLite
 * 
 * The database file is automatically located at ./data/wart_stat.db
 * Only LoggerInterface is required for dependency injection autowiring.
 */
class Database
{
    private PDO $pdo;
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger PSR-3 logger
     * @throws RuntimeException If database cannot be opened
     */
    public function __construct(string $dbPath, LoggerInterface $logger)
    {
        $this->logger = $logger;

        try {
            // Ensure database file exists or create it
            if (!file_exists($dbPath)) {
                $this->logger->warning("Database file not found: {$dbPath}. Creating new database.");
                touch($dbPath);
            }

            // Connect to SQLite database
            $dsn = 'sqlite:' . realpath($dbPath);
            $this->pdo = new PDO($dsn, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // Enable foreign keys
            $this->pdo->exec('PRAGMA foreign_keys = ON');

            $this->logger->info("Database connection established: {$dsn}");
        } catch (PDOException $e) {
            $this->logger->error("Database connection failed: {$e->getMessage()}");
            throw new RuntimeException("Cannot connect to database: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Get PDO connection instance
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Execute a query with parameters
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return bool Success flag
     */
    public function execute(string $sql, array $params = []): bool
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->logger->error("Query execution failed: {$e->getMessage()}", ['sql' => $sql]);
            throw new RuntimeException("Query execution failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Fetch a single row
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array|null
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            $this->logger->error("Fetch one failed: {$e->getMessage()}", ['sql' => $sql]);
            throw new RuntimeException("Fetch one failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Fetch multiple rows
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logger->error("Fetch all failed: {$e->getMessage()}", ['sql' => $sql]);
            throw new RuntimeException("Fetch all failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Insert a record and return last insert ID
     *
     * @param string $sql INSERT statement
     * @param array $params Query parameters
     * @return int Last inserted ID
     */
    public function insert(string $sql, array $params = []): int
    {
        try {
            $this->execute($sql, $params);
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error("Insert failed: {$e->getMessage()}", ['sql' => $sql]);
            throw new RuntimeException("Insert failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Count records
     *
     * @param string $sql COUNT query
     * @param array $params Query parameters
     * @return int
     */
    public function count(string $sql, array $params = []): int
    {
        try {
            $result = $this->fetchOne($sql, $params);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            $this->logger->error("Count failed: {$e->getMessage()}", ['sql' => $sql]);
            throw new RuntimeException("Count failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit(): void
    {
        $this->pdo->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    /**
     * Check if database schema exists
     *
     * @return bool
     */
    public function schemaExists(): bool
    {
        try {
            $count = $this->count("SELECT COUNT(*) as count FROM sqlite_master WHERE type='table' AND name='missions'");
            return $count > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}
