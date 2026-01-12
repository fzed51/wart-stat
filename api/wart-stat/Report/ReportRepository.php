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
        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS reports (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                country TEXT NOT NULL,
                datetime TEXT NOT NULL,
                content TEXT NOT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ');
    }

    public function create(array $data): array
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO reports (country, datetime, content)
            VALUES (:country, :datetime, :content)
        ');

        $stmt->execute([
            'country' => $data['country'],
            'datetime' => $data['datetime'],
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
}
