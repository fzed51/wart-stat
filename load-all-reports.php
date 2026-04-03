<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use WartStat\Report\ReportParser;
use WartStat\Report\ReportRepository;
use WartStat\Report\ReportValidator;
use WartStat\Report\MissionRepository;
use WartStat\Report\MissionActionRepository;
use WartStat\Report\MissionBonusRepository;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;

$scriptStart = microtime(true);

try {
    // Setup Logger - only errors to file for performance
    $logger = new Logger('wart-stat-batch');
    $handler = new StreamHandler(__DIR__ . '/data/batch-load.log', Level::Error);
    $handler->setFormatter(new \Monolog\Formatter\JsonFormatter());
    $logger->pushHandler($handler);
    
    // Console output for progress
    $consoleHandler = new StreamHandler('php://stdout', Level::Info);
    $consoleHandler->setFormatter(new \Monolog\Formatter\LineFormatter("[%level_name%] %message%\n"));
    $logger->pushHandler($consoleHandler);
    
    // Setup PDO
    $dbPath = __DIR__ . '/data/database.sqlite';
    $dataDir = dirname($dbPath);
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    $pdo = new \PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    
    // Enable performance optimizations for batch inserts
    $pdo->exec('PRAGMA synchronous = OFF');
    $pdo->exec('PRAGMA journal_mode = WAL');
    
    echo "✅ Database initialized with performance optimizations\n";
    
    // Create repositories
    $reportRepository = new ReportRepository($pdo, $logger);
    $missionRepository = new MissionRepository($pdo, $logger);
    $actionRepository = new MissionActionRepository($pdo, $logger);
    $bonusRepository = new MissionBonusRepository($pdo, $logger);
    $validator = new ReportValidator();
    $parser = new ReportParser();
    

    
    // Scan report directory
    $reportDir = __DIR__ . '/report';
    $files = glob($reportDir . '/report*.txt');
    
    if (empty($files)) {
        throw new RuntimeException('No report files found in ' . $reportDir);
    }
    
    // Sort files numerically
    usort($files, function($a, $b) {
        preg_match('/report(\d+)\.txt$/', $a, $ma);
        preg_match('/report(\d+)\.txt$/', $b, $mb);
        return (int)$ma[1] <=> (int)$mb[1];
    });
    
    echo "\n════════════════════════════════════════════════════════════════\n";
    echo "📊 BATCH LOADING REPORTS\n";
    echo "════════════════════════════════════════════════════════════════\n\n";
    printf("Found %d report files to process\n\n", count($files));
    
    // Statistics
    $stats = [
        'total_files' => count($files),
        'reports_created' => 0,
        'missions_created' => 0,
        'actions_created' => 0,
        'bonuses_created' => 0,
        'errors' => 0,
        'skipped' => 0,
    ];
    
    $errors = [];
    $batchStart = time();
    
    // Process each report file
    foreach ($files as $index => $filePath) {
        $fileNumber = $index + 1;
        $fileInfo = pathinfo($filePath);
        $fileName = $fileInfo['basename'];
        
        try {
            // Extract report number from filename
            preg_match('/report(\d+)\.txt$/', $filePath, $matches);
            $reportNumber = (int)$matches[1];
            
            // Get file modification time
            $fileTime = filemtime($filePath);
            if ($fileTime === false) {
                throw new RuntimeException("Cannot get file modification time for $fileName");
            }
            
            $datetime = gmdate('Y-m-d\TH:i:s\Z', $fileTime);
            
            // Read report content
            $content = file_get_contents($filePath);
            if ($content === false) {
                throw new RuntimeException("Cannot read file $fileName");
            }
            
            // Trim content for consistency
            $content = trim($content);
            
            // Extract session_id from report content (quick extraction without full parse)
            $sessionId = null;
            if (preg_match('/^Session:\s*([a-f0-9]+)\s*$/im', $content, $matches)) {
                $sessionId = $matches[1];
            }
            
            // Check if report already exists by session_id (most reliable identifier)
            if (!empty($sessionId) && $reportRepository->existsBySessionId($sessionId)) {
                $stats['skipped']++;
                continue;
            }
            
            // Validate report data
            $reportData = [
                'country' => 'FR',
                'datetime' => $datetime,
                'session_id' => $sessionId,
                'content' => $content,
            ];
            
            if (!$validator->safeValidate($reportData)) {
                $errors_list = $validator->getErrors();
                throw new RuntimeException('Validation failed: ' . json_encode($errors_list));
            }
            
            // Begin transaction
            $pdo->beginTransaction();
            
            try {
                // Create report
                $report = $reportRepository->create($reportData);
                
                // Parse the report content
                $parsedData = $parser->parse($content);
                
                // Create mission
                $missionData = $parsedData['mission'];
                $missionData['report_id'] = $report['id'];
                $mission = $missionRepository->create($missionData);
                
                // Create actions
                $actionCount = 0;
                foreach ($parsedData['actions'] as $actionData) {
                    $actionData['mission_id'] = $mission['id'];
                    $actionRepository->create($actionData);
                    $actionCount++;
                }
                
                // Create bonuses
                $bonusCount = 0;
                foreach ($parsedData['mission_bonuses'] as $bonusData) {
                    $bonusData['mission_id'] = $mission['id'];
                    $bonusRepository->create($bonusData);
                    $bonusCount++;
                }
                
                // Commit transaction
                $pdo->commit();
                
                // Update statistics
                $stats['reports_created']++;
                $stats['missions_created']++;
                $stats['actions_created'] += $actionCount;
                $stats['bonuses_created'] += $bonusCount;
                
            } catch (\Exception $e) {
                // Rollback on error
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $e;
            }
            
            // Progress output every 50 files
            if ($fileNumber % 50 === 0) {
                $elapsed = time() - $batchStart;
                $rate = $stats['reports_created'] / max(1, $elapsed);
                $remaining = ($stats['total_files'] - $stats['reports_created']) / max(0.1, $rate);
                printf("[%4d/%d] Progress: %.1f%% | Rate: %.2f/sec | ETA: %s\n",
                    $fileNumber,
                    count($files),
                    (($fileNumber / count($files)) * 100),
                    $rate,
                    gmdate('H:i:s', (int)$remaining)
                );
            }
            
        } catch (\Exception $e) {
            $stats['errors']++;
            $errorMsg = "report$reportNumber: " . $e->getMessage();
            $errors[] = $errorMsg;
            
            $logger->error("Error processing report $reportNumber", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }
    
    // Summary
    $scriptEnd = microtime(true);
    $duration = $scriptEnd - $scriptStart;
    
    echo "\n════════════════════════════════════════════════════════════════\n";
    echo "✅ BATCH LOAD COMPLETE\n";
    echo "════════════════════════════════════════════════════════════════\n\n";
    echo "📋 Summary:\n";
    printf("  • Total files:       %d\n", $stats['total_files']);
    printf("  • Reports created:   %d\n", $stats['reports_created']);
    printf("  • Missions created:  %d\n", $stats['missions_created']);
    printf("  • Actions created:   %d\n", $stats['actions_created']);
    printf("  • Bonuses created:   %d\n", $stats['bonuses_created']);
    printf("  • Skipped:           %d\n", $stats['skipped']);
    printf("  • Errors:            %d\n", $stats['errors']);
    printf("  • Skipped (dupes):   %d\n", $stats['skipped']);
    printf("  • Duration:          %.1f minutes\n", $duration / 60);
    printf("  • Rate:              %.2f reports/second\n\n", ($stats['reports_created'] > 0 ? $stats['reports_created'] / $duration : 0));
    
    if (!empty($errors)) {
        echo "⚠️  Errors encountered:\n";
        foreach (array_slice($errors, 0, 20) as $error) {
            echo "   - $error\n";
        }
        if (count($errors) > 20) {
            printf("   ... and %d more errors\n", count($errors) - 20);
        }
        echo "\nCheck " . __DIR__ . "/data/batch-load.log for full details\n";
    }
    
    $logger->info('Batch load completed', [
        'files' => $stats['total_files'],
        'created' => $stats['reports_created'],
        'duration' => $duration,
    ]);
    
    echo "\n════════════════════════════════════════════════════════════════\n";
    
} catch (\Exception $e) {
    echo "\n❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
