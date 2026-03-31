<?php

declare(strict_types=1);

// Auto-loader for composer
require_once __DIR__ . '/vendor/autoload.php';

use WartStat\Report\ReportParser;

try {
    // Déterminer le fichier de rapport à utiliser
    $reportIndex = null;
    if (isset($argv[1])) {
        // Utiliser l'index fourni en paramètre
        $reportIndex = (int)$argv[1];
    } else {
        // Choisir un index aléatoire
        $files = glob(__DIR__ . '/report/report*.txt');
        if (empty($files)) {
            fwrite(STDERR, "Erreur: Aucun fichier de rapport trouvé\n");
            exit(1);
        }
        $randomFile = $files[array_rand($files)];
        // Extraire l'index du nom de fichier
        preg_match('/report(\d+)\.txt$/', $randomFile, $matches);
        $reportIndex = (int)$matches[1];
    }
    
    // Vérifier les paramètres secondaires (peuvent être dans n'importe quel ordre après l'index)
    $verbose = false;
    $showReport = false;
    
    for ($i = 2; $i < count($argv); $i++) {
        if ($argv[$i] === '--verbose' || $argv[$i] === '-v') {
            $verbose = true;
        } elseif ($argv[$i] === '--show-report' || $argv[$i] === '-r') {
            $showReport = true;
        }
    }
    
    // Charger le rapport
    $reportFile = __DIR__ . '/report/report' . $reportIndex . '.txt';
    
    if (!file_exists($reportFile)) {
        fwrite(STDERR, "Erreur: Le fichier de rapport '$reportFile' n'existe pas\n");
        exit(1);
    }
    
    $reportContent = file_get_contents($reportFile);

    if ($reportContent === false) {
        fwrite(STDERR, "Erreur: Impossible de lire le fichier de rapport\n");
        exit(1);
    }

    // Parser le rapport
    $parser = new ReportParser();
    
    if ($verbose) {
        $parser->setVerbose(true);
    }
    
    $parsedData = $parser->parse($reportContent);

    // Affichage des résultats
    echo "========================================\n";
    echo "RÉSULTATS DU PARSING: report" . $reportIndex . ".txt\n";
    echo "========================================\n\n";

    // Données de la mission
    echo "📋 DONNÉES DE LA MISSION\n";
    echo "------------------------\n";
    $mission = $parsedData['mission'];
    echo "  Type: " . ($mission['mission_type'] ?? 'N/A') . "\n";
    echo "  Localité: " . ($mission['location'] ?? 'N/A') . "\n";
    echo "  Résultat: " . ($mission['result'] ?? 'N/A') . "\n";
    if (isset($mission['mission_duration_sec']) && $mission['mission_duration_sec'] > 0) {
        $mins = intdiv($mission['mission_duration_sec'], 60);
        $secs = $mission['mission_duration_sec'] % 60;
        echo "  Durée: " . sprintf("%d:%02d", $mins, $secs) . "\n";
    }
    echo "  Session: " . ($mission['session_id'] ?? 'N/A') . "\n";
    echo "  Total SL gagné: " . ($mission['total_sl'] ?? 0) . "\n";
    echo "  Total RP gagné: " . ($mission['total_rp'] ?? 0) . "\n";
    echo "\n";

    // Actions
    echo "⚔️  ACTIONS PARSÉES\n";
    echo "-------------------\n";
    echo "  Total: " . count($parsedData['actions']) . " actions\n";
    $actionTypes = [];
    foreach ($parsedData['actions'] as $action) {
        $type = $action['type_action'];
        $actionTypes[$type] = ($actionTypes[$type] ?? 0) + 1;
    }
    foreach ($actionTypes as $type => $count) {
        echo "    - $type: $count\n";
    }
    echo "\n";

    // Vérification des 3 premières actions détaillées
    echo "📊 DÉTAILS DES 3 PREMIÈRES ACTIONS\n";
    echo "-----------------------------------\n";
    for ($i = 0; $i < min(3, count($parsedData['actions'])); $i++) {
        $a = $parsedData['actions'][$i];
        echo "  Action #" . ($i + 1) . "\n";
        echo "    Type: " . $a['type_action'] . "\n";
        echo "    Timestamp: " . $a['timestamp_sec'] . "s\n";
        echo "    Véhicule: " . $a['vehicle_name'] . "\n";
        echo "    Arme: " . ($a['weapon_used'] ? $a['weapon_used'] : 'N/A') . "\n";
        echo "    Cible: " . ($a['target_name'] ? $a['target_name'] : 'N/A') . "\n";
        echo "    Points: " . $a['point_score'] . "\n";
        echo "    SL: " . $a['sl_awarded'] . " | RP: " . $a['rp_awarded'] . "\n";
        echo "\n";
    }

    // Bonus de mission
    echo "🎁 BONUS DE MISSION\n";
    echo "-------------------\n";
    echo "  Total: " . count($parsedData['mission_bonuses']) . " bonus\n";
    foreach ($parsedData['mission_bonuses'] as $bonus) {
        echo "  - " . $bonus['bonus_name'] . "\n";
        echo "      SL: " . $bonus['sl_awarded'] . " | RP: " . $bonus['rp_awarded'] . "\n";
    }
    echo "\n";

    // Coûts réels
    echo "💰 COÛTS RÉELS\n";
    echo "--------------\n";
    if (!empty($parsedData['mission'])) {
        $mission = $parsedData['mission'];
        echo "  Réparation: " . ($mission['repair_cost'] ?? 0) . " SL\n";
        echo "  Ammo/Équipage: " . ($mission['ammo_crew_cost'] ?? 0) . " SL\n";
        $totalCosts = ($mission['repair_cost'] ?? 0) + ($mission['ammo_crew_cost'] ?? 0);
        echo "  Total des coûts: " . $totalCosts . " SL\n";
    } else {
        echo "  Aucun coût parsé\n";
    }
    echo "\n";

    // Résumé
    echo "✅ RÉSUMÉ\n";
    echo "---------\n";
    echo "  Actions extraites: " . count($parsedData['actions']) . "\n";
    echo "  Bonus extraits: " . count($parsedData['mission_bonuses']) . "\n";
    echo "  Total SL (actions): " . $mission['total_sl'] . "\n";
    echo "  Total RP (actions): " . $mission['total_rp'] . "\n";
    echo "\n";

    // Vérification avec rapport attendus
    echo "🔍 VÉRIFICATIONS\n";
    echo "-----------------\n";
    $checks = [
        ["nom" => "Mission n'est pas vide", "ok" => !empty($mission)],
        ["nom" => "Type de mission extrait", "ok" => !empty($mission['mission_type'])],
        ["nom" => "Localité extraite", "ok" => !empty($mission['location'])],
        ["nom" => "Résultat extrait", "ok" => !empty($mission['result'])],
        ["nom" => "Actions extraites", "ok" => count($parsedData['actions']) > 0],
        ["nom" => "Total SL > 0", "ok" => $mission['total_sl'] > 0],
        ["nom" => "Total RP > 0", "ok" => $mission['total_rp'] > 0],
    ];

    foreach ($checks as $check) {
        $symbol = $check['ok'] ? "✅" : "❌";
        echo "  $symbol {$check['nom']}\n";
    }
    echo "\n";

    echo "========================================\n";
    echo "FIN DU TEST\n";
    echo "========================================\n";
    
    // Afficher le rapport original si demandé
    if ($showReport) {
        echo "\n";
        echo "========================================\n";
        echo "RAPPORT ORIGINAL\n";
        echo "========================================\n";
        echo $reportContent;
        echo "\n";
        echo "========================================\n";
        echo "FIN DU RAPPORT ORIGINAL\n";
        echo "========================================\n";
    }
} catch (Throwable $e) {
    fwrite(STDERR, "ERREUR: " . $e->getMessage() . "\n");
    fwrite(STDERR, $e->getTraceAsString() . "\n");
    exit(1);
}
