<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

try {

    // Setup PDO
    $dbPath = __DIR__ . '/data/database.sqlite';
    if (!file_exists($dbPath)) {
        throw new RuntimeException('Database file not found: ' . $dbPath);
    }

    $pdo = new \PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

    // Fetch all reports
    $stmt = $pdo->query('SELECT * FROM reports');
    $reports = $stmt->fetchAll();

    if (empty($reports)) {
        echo "\n❌ Aucun rapport trouvé dans la base de données.\n";
        exit(1);
    }

    echo "\n════════════════════════════════════════════════════════════════════════════════\n";
    echo "📊 LISTE DES RAPPORTS\n";
    echo "════════════════════════════════════════════════════════════════════════════════\n\n";
    printf("Total: %d rapport(s)\n\n", count($reports));

    // Display header
    echo str_pad("ID", 6) .
        str_pad("Pays", 6) .
        str_pad("Date / Heure", 20) .
        str_pad("Véhicules utilisés", 50) .
        "\n";
    echo str_repeat("─", 90) . "\n";

    // Process each report
    foreach ($reports as $report) {
        $reportId = $report['id'];
        $country = $report['country'];
        $datetime = $report['datetime'];
        $sessionId = $report['session_id'];

        // Collect all unique vehicles for this report
        $vehicles = [];

        // Fetch actions for this mission
        $stmt = $pdo->prepare('SELECT DISTINCT mission_actions.vehicle_name FROM mission_actions INNER JOIN missions ON missions.id = mission_actions.mission_id WHERE missions.session_id = :session_id');
        $stmt->execute(['session_id' => $sessionId]);
        $vehicles = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Format vehicles list
        $vehiclesList = !empty($vehicles) ? implode(', ', $vehicles) : 'Aucun';

        // Truncate vehicles list if too long for display
        if (strlen($vehiclesList) > 47) {
            $vehiclesList = substr($vehiclesList, 0, 44) . '...';
        }

        // Display report row
        printf(
            "%s %s %s %s\n",
            str_pad((string)$reportId, 6),
            str_pad($country, 6),
            str_pad($datetime, 20),
            str_pad($vehiclesList, 50)
        );
    }

    echo "\n" . str_repeat("─", 90) . "\n";
    echo "\n✅ Listing completed successfully!\n";
    echo "════════════════════════════════════════════════════════════════════════════════\n\n";
} catch (\Exception $e) {
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
