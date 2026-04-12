<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

// ============================================================
// Setup PDO
// ============================================================
$dbPath = __DIR__ . '/data/database.sqlite';
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// ============================================================
// Helper: Get vehicles from a report
// ============================================================
function getReportVehicles(PDO $pdo, int $reportId): array {
    $stmt = $pdo->prepare("
        SELECT DISTINCT ma.vehicle_name
        FROM mission_actions ma
        INNER JOIN missions m ON (
            ma.mission_id = m.id 
            AND ma.type_action <> 'Temps d''activité'
            )
        INNER JOIN reports r ON r.session_id = m.session_id
        WHERE r.id = ?
    ");
    $stmt->execute([$reportId]);
    $rows = $stmt->fetchAll();
    
    return array_map(fn($row) => $row['vehicle_name'], $rows);
}

// ============================================================
// Helper: Detect country from vehicles
// ============================================================
function detectCountryFromVehicles(PDO $pdo, array $vehicles): ?string {
    if (empty($vehicles)) {
        return null;
    }
    
    $placeholders = implode(',', array_fill(0, count($vehicles), '?'));
    $query = "
        SELECT DISTINCT r.country
        FROM mission_actions ma
        INNER JOIN missions m ON m.id = ma.mission_id
        INNER JOIN reports r ON r.id = m.report_id
        WHERE r.country <> '-'
        AND r.country <> ''
        AND ma.vehicle_name IN ($placeholders)
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($vehicles);
    $countryRows = $stmt->fetchAll();
    $countries = array_map(fn($row) => $row['country'], $countryRows);
    
    if (empty($countries)) {
        return null;
    }
    
    $uniqueCountries = array_unique($countries);
    
    // Return country only if exactly one is found
    if (count($uniqueCountries) === 1) {
        return $uniqueCountries[0];
    }
    
    return null; // Ambiguous
}

// ============================================================
// Main Logic
// ============================================================
echo "\n════════════════════════════════════════════════════════════════\n";
echo "🔍 IDENTIFYING MISSING COUNTRIES BY VEHICLES\n";
echo "════════════════════════════════════════════════════════════════\n\n";

try {
    // Step 1: Find all reports with missing country
    $stmt = $pdo->query("SELECT id, session_id FROM reports WHERE country = '-'");
    $missingReports = $stmt->fetchAll();
    
    echo "Found " . count($missingReports) . " reports with missing country\n\n";
    
    $fixed = 0;
    $ambiguous = 0;
    $noMatch = 0;
    $stats = [];
    
    // Step 2: Process each report
    foreach ($missingReports as $report) {
        $reportId = (int)$report['id'];
        $sessionId = $report['session_id'];
        
        echo "Processing Report #$reportId (Session: $sessionId)...\n";
        
        // Get vehicles for this report
        $vehicles = getReportVehicles($pdo, $reportId);
        
        if (empty($vehicles)) {
            echo "  ⚠️  No vehicle actions found for report\n\n";
            $noMatch++;
            $stats['no_vehicles'] = ($stats['no_vehicles'] ?? 0) + 1;
            continue;
        }
        
        // Display vehicles
        echo "  📋 Vehicles found: " . implode(', ', $vehicles) . "\n";
        
        // Step 3: Detect country from vehicles
        $detectedCountry = detectCountryFromVehicles($pdo, $vehicles);
        
        if ($detectedCountry === null) {
            echo "  ❌ No countries found with these vehicles\n\n";
            $noMatch++;
            $stats['no_match'] = ($stats['no_match'] ?? 0) + 1;
            continue;
        }
        
        // Step 4: Update the report
        $updateStmt = $pdo->prepare('UPDATE reports SET country = ? WHERE id = ?');
        $updateStmt->execute([$detectedCountry, $reportId]);
        
        echo "  ✅ Detected country: $detectedCountry\n";
        echo "  ✔️  Report updated successfully\n\n";
        
        $fixed++;
        $stats[$detectedCountry] = ($stats[$detectedCountry] ?? 0) + 1;
    }
    
    // ============================================================
    // Display Summary
    // ============================================================
    echo "\n════════════════════════════════════════════════════════════════\n";
    echo "📊 SUMMARY\n";
    echo "════════════════════════════════════════════════════════════════\n\n";
    printf("Reports processed:    %d\n", count($missingReports));
    printf("Reports fixed:        %d ✅\n", $fixed);
    printf("Reports no match:     %d ❌\n\n", $noMatch);
    
    if (!empty($stats)) {
        echo "Countries identified:\n";
        foreach ($stats as $country => $count) {
            if (!in_array($country, ['no_match', 'no_vehicles'])) {
                printf("  - %s: %d reports\n", $country, $count);
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
