<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

// ============================================================
// Available Countries
// ============================================================
const COUNTRIES = [
    'US' => 'États-Unis',
    'GER' => 'Allemagne',
    'URRS' => 'URSS',
    'UK' => 'Royaume-Uni',
    'JAP' => 'Japon',
    'CH' => 'Chine',
    'IT' => 'Italie',
    'FR' => 'France',
    'SU' => 'Suède',
    'IL' => 'Israël',
];

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
// Helper: Get user country selection
// ============================================================
function getUserCountrySelection(): ?string {
    echo "\n  📍 Select report country:\n";
    $index = 1;
    $countryMap = [];
    
    foreach (COUNTRIES as $code => $label) {
        $countryMap[$index] = $code;
        printf("    [%d] %s (%s)\n", $index, $label, $code);
        $index++;
    }
    
    echo "    [s] Skip this report\n";
    echo "    [a] Auto finish (skip unresolved reports)\n";
    
    do {
        echo "\n  Enter your choice (number, s, or a): ";
        $input = trim(fgets(STDIN));
        
        if (strtolower($input) === 's') {
            return 'SKIP';
        }
        
        if (strtolower($input) === 'a') {
            return 'AUTO_MODE';
        }
        
        $num = (int)$input;
        if (isset($countryMap[$num])) {
            return $countryMap[$num];
        }
        
        echo "  ⚠️  Invalid choice. Please try again.\n";
    } while (true);
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
    $noMatch = 0;
    $skipped = 0;
    $autoSkipped = 0;
    $autoMode = false;
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
        
        // Step 4: If not automatically detected, ask user
        if ($detectedCountry === null) {
            if ($autoMode) {
                // Auto mode: skip unresolved reports
                echo "  ⏭️  Auto mode: Skipping (no automatic resolution)\n\n";
                $autoSkipped++;
                $stats['auto_skipped'] = ($stats['auto_skipped'] ?? 0) + 1;
                continue;
            }
            
            echo "  ❌ No countries found with these vehicles\n";
            
            $userChoice = getUserCountrySelection();
            
            if ($userChoice === 'AUTO_MODE') {
                $autoMode = true;
                echo "\n  ✓ Auto finish enabled. Remaining unresolved reports will be skipped.\n\n";
                $autoSkipped++;
                $stats['auto_skipped'] = ($stats['auto_skipped'] ?? 0) + 1;
                continue;
            }
            
            if ($userChoice === 'SKIP') {
                echo "  ⏭️  Report skipped\n\n";
                $skipped++;
                $stats['skipped'] = ($stats['skipped'] ?? 0) + 1;
                continue;
            }
            
            $detectedCountry = $userChoice;
        }
        
        // Step 5: Update the report
        $updateStmt = $pdo->prepare('UPDATE reports SET country = ? WHERE id = ?');
        $updateStmt->execute([$detectedCountry, $reportId]);
        
        $countryLabel = COUNTRIES[$detectedCountry] ?? $detectedCountry;
        echo "  ✅ Country assigned: $countryLabel ($detectedCountry)\n";
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
    printf("Reports skipped:      %d ⏭️\n", $skipped);
    printf("Reports auto-skipped: %d ⏩\n", $autoSkipped);
    printf("Reports without data: %d ⚠️\n\n", $noMatch);
    
    if (!empty($stats)) {
        echo "Countries assigned:\n";
        foreach ($stats as $country => $count) {
            if (!in_array($country, ['skipped', 'no_vehicles', 'auto_skipped'])) {
                $label = COUNTRIES[$country] ?? $country;
                printf("  - %s (%s): %d reports\n", $label, $country, $count);
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
