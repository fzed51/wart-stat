<?php

declare(strict_types=1);

namespace WartStat\Report;

use Monolog\Logger;
use PDO;

class ReportDetailsRepository
{
    public function __construct(private PDO $pdo, private Logger $logger)
    {
        $this->ensureViewExists();
    }

    private function ensureViewExists(): void
    {
        $this->pdo->exec("
            CREATE VIEW IF NOT EXISTS reports_details AS
            SELECT 
                r.id as report_id,
                r.country,
                r.datetime,
                r.session_id,
                COALESCE(m.result, '') as win_lost,
                COALESCE(m.mission_type, '') as mission_type,
                COALESCE(m.location, '') as carte,
                COALESCE(m.mission_duration_sec, 0) as temps_jeux,
                COALESCE(SUM(ma.point_score), 0) as points_totaux,
                COALESCE(m.total_sl, 0) as total_sl,
                COALESCE(m.total_rp, 0) as total_rp
            FROM reports r
            LEFT JOIN missions m ON r.id = m.report_id
            LEFT JOIN mission_actions ma ON m.id = ma.mission_id
            GROUP BY m.id
            ORDER BY r.datetime DESC
        ");
        
        $this->logger->debug("reports_details view ensured");
    }

    public function findAll(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM reports_details 
            ORDER BY datetime DESC 
            LIMIT :limit OFFSET :offset
        ');
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findByReportId(int $reportId): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM reports_details 
            WHERE report_id = :report_id
        ');
        
        $stmt->execute(['report_id' => $reportId]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function findByCountry(string $country, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM reports_details 
            WHERE country = :country 
            ORDER BY datetime DESC 
            LIMIT :limit OFFSET :offset
        ');
        
        $stmt->bindValue(':country', $country, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findBySessionId(string $sessionId): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM reports_details 
            WHERE session_id = :session_id
        ');
        
        $stmt->execute(['session_id' => $sessionId]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function count(?string $country = null): int
    {
        if ($country === null) {
            $stmt = $this->pdo->query('SELECT COUNT(DISTINCT report_id) as total FROM reports_details');
        } else {
            $stmt = $this->pdo->prepare('
                SELECT COUNT(DISTINCT report_id) as total FROM reports_details 
                WHERE country = :country
            ');
            $stmt->execute(['country' => $country]);
        }

        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }
}
