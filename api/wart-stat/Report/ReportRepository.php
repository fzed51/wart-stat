<?php

namespace WartStat\Report;

use Monolog\Logger;
use PDO;

class ReportRepository
{
    public function __construct(private PDO $pdo, private Logger $logger)
    {
        $this->pdo = $pdo;
        $this->ensureTableExists();
    }

    private function ensureTableExists(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS reports (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                country TEXT NOT NULL,
                datetime TEXT NOT NULL,
                session_id TEXT UNIQUE,
                content TEXT NOT NULL,
                created_at TEXT default (replace(CURRENT_TIMESTAMP, ' ', 'T') || 'Z')
            )
        ");
        // Add session_id column if it doesn't exist (for backwards compatibility)
        try {
            $this->pdo->exec('ALTER TABLE reports ADD COLUMN session_id TEXT UNIQUE');
        } catch (\PDOException $e) {
            // Column already exists, ignore
        }
    }

    public function create(array $data): array
    {

        // Extract session_id from report content (quick extraction without full parse)
        $sessionId = null;
        if (preg_match('/^Session:\s*([a-f0-9]+)\s*$/im', $data['content'], $matches)) {
            $sessionId = $matches[1];
        }
        if (!$sessionId) {
            throw new \InvalidArgumentException("Session ID not found in report content");
        }

        $stmt = $this->pdo->prepare('
            INSERT INTO reports (country, datetime, session_id, content)
            VALUES (:country, :datetime, :session_id, :content)
        ');

        $stmt->execute([
            'country' => $data['country'],
            'datetime' => $data['datetime'],
            'session_id' => $sessionId,
            'content' => $data['content'],
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $this->logger->debug("Report created with ID: $id");
        return $this->findById($id);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM reports WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM reports ORDER BY datetime DESC');

        return $stmt->fetchAll();
    }

    public function findByDateTime(string $datetime): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM reports WHERE datetime = :datetime');
        $stmt->execute(['datetime' => $datetime]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function findByDateTimeAndCountry(string $datetime, string $country): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM reports WHERE datetime = :datetime AND country = :country');
        $stmt->execute(['datetime' => $datetime, 'country' => $country]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function exists(string $datetime, string $country): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM reports WHERE datetime = :datetime AND country = :country LIMIT 1');
        $stmt->execute(['datetime' => $datetime, 'country' => $country]);
        return $stmt->fetch() !== false;
    }

    public function findBySessionId(string $sessionId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM reports WHERE session_id = :session_id');
        $stmt->execute(['session_id' => $sessionId]);
        return $stmt->fetch() ?: null;
    }

    public function existsBySessionId(string $sessionId): bool
    {
        if (empty($sessionId)) {
            return false;
        }
        $stmt = $this->pdo->prepare('SELECT 1 FROM reports WHERE session_id = :session_id LIMIT 1');
        $stmt->execute(['session_id' => $sessionId]);
        return $stmt->fetch() !== false;
    }

    public function update(int $id, array $data): ?array
    {
        // Only allow updating country and datetime
        $allowedFields = ['country', 'datetime'];
        $updateFields = [];
        $params = ['id' => $id];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (empty($updateFields)) {
            return $this->findById($id);
        }

        $sql = 'UPDATE reports SET ' . implode(', ', $updateFields) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $this->logger->debug("Report updated with ID: $id");
        return $this->findById($id);
    }
}
