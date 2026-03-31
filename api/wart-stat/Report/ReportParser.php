<?php

declare(strict_types=1);

namespace WartStat\Report;

/**
 * ReportParser - Parses War Thunder mission reports into database-ready structures
 * 
 * Transforms raw mission report text into organized data structures compatible with the DB schema
 */
class ReportParser {
    
    /** Flag pour contrôler la verbosité des logs */
    protected bool $verbose = false;
    
    /** Action type names that appear in reports */
    protected array $actionNames = [
        'Destruction de cibles terrestres',
        'Destruction d\'avions',
        'Destructions de munitions',
        'Assistance à la destruction d\'adversaires',
        'Dégâts importants infligés à l\'ennemi',
        'Coups critiques infligés aux ennemis',
        'Dégâts infligés aux ennemis',
    ];
    
    /** Bonus type names that appear in reports */
    protected array $bonusNames = [
        "Temps d'activité",
        "Temps Joué",
        "Récompense pour la victoire",
        "Bonus de Compétence"
    ];

    public function __construct() {}

    /**
     * Définir le mode verbose (affichage des logs de debug)
     * @param bool $verbose True pour afficher les logs, false pour les désactiver
     */
    public function setVerbose(bool $verbose = true): void {
        $this->verbose = $verbose;
    }

    /**
     * Logs debug - affiche uniquement si verbose est activé
     */
    private function log(string $message, string $level = 'DEBUG'): void {
        if ($this->verbose) {
            fwrite(STDERR, "[$level] $message\n");
        }
    }

    /**
     * Convertit un temps au format MM:SS ou M:SS en secondes
     * @param string $time Temps au format MM:SS (ex: "3:40")
     * @return int Nombre total de secondes (0 si format invalide)
     */
    private function timeToSeconds(string $time): int {
        $parts = explode(':', trim($time));
        if (count($parts) !== 2) {
            $this->log("Format de temps invalide: '$time'", 'WARN');
            return 0;
        }
        $minutes = (int)$parts[0];
        $seconds = (int)$parts[1];
        return $minutes * 60 + $seconds;
    }

    /**
     * Parse mission description line
     * @return array mission description data: {success, mission_type, location}
     */
    private function parseMissionDescription(string $line): array {
        $data = [];
        $this->log("Parsing mission description: '$line'");
        $descriptionMatch = preg_match('/^(?:\xEF\xBB\xBF)?(\S+) en \[(.+?)\] (.+?) mission\!?/i', $line, $m);
        if ($descriptionMatch) {
            $data['result'] = strtolower($m[1]) === "victoire" ? "Victoire" : "Défaite";
            $data['mission_type'] = $m[2];
            $data['location'] = trim($m[3]);
        }
        return $data;
    }

    /**
     * Parse mission actions (combat actions)
     * @param string $line Current line
     * @param array $lines Remaining lines (will be modified)
     * @return array action data with details
     */
    private function parseMissionActions(string $line, array &$lines): array {
        $data = [];
        if (preg_match('/^(.+?)  ? /i', $line, $m)) {
            $actionName = trim($m[1]);
            if (!in_array($actionName, $this->actionNames)) {
                return [];
            }
            $regMatch = preg_match('/\s{2,}(\d+)\s{2,}(\d+) SL\s{2,}(\d+) RP$/i', $line, $m);
            if ($regMatch) {
                $count = (int)$m[1];
                $sl = (int)$m[2];
                $rp = (int)$m[3];
                $data = [
                    'type_action' => $actionName,
                    'count' => $count,
                    'total_sl' => $sl,
                    'total_rp' => $rp,
                    'details' => [],
                ];

                if (count($lines) < $count) {
                    $this->log("Nombre insuffisant de lignes pour les détails de '$actionName'", 'WARN');
                    return [];
                }
                
                // Parse action details
                for ($i = 0; $i < $count; $i++) {
                    $detailLine = trim(array_shift($lines));
                    $pattern = '/^\s*(\d{1,2}:\d{2})\s+(.+?)(?:\s{2,}\((.+?)\))?\s{2,}(.+?)\s{2,}(.+?)\s{2,}(\d+)\s+points\s+.+?= (\d+) SL\s+.+?= (\d+) RP/';
                    if (preg_match($pattern, $detailLine, $matches)) {
                        $data['details'][] = [
                            'timestamp_sec' => $this->timeToSeconds($matches[1]),
                            'vehicle_name' => trim($matches[2]),
                            'origin_vehicle' => $matches[3] ? trim($matches[3]) : null,
                            'weapon_used' => trim($matches[4]),
                            'target_name' => trim($matches[5]),
                            'point_score' => (int)$matches[6],
                            'sl_awarded' => (int)$matches[7],
                            'rp_awarded' => (int)$matches[8],
                        ];
                    } else {
                        $this->log("Détail d'action invalide: '$detailLine'", 'WARN');
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Parse zone capture actions
     * @param string $line Current line
     * @param array $lines Remaining lines (will be modified)
     * @return array capture data with details
     */
    private function parseMissionCapture(string $line, array &$lines): array {
        $data = [];
        if (preg_match('/^(.+?)  ? /i', $line, $m)) {
            $actionName = trim($m[1]);
            if ($actionName !== 'Capture de zones') {
                return [];
            }
            $regMatch = preg_match('/\s{2,}(\d+)\s{2,}(\d+) SL\s{2,}(\d+) RP$/i', $line, $m);
            if ($regMatch) {
                $count = (int)$m[1];
                $sl = (int)$m[2];
                $rp = (int)$m[3];
                $data = [
                    'type_action' => 'Capture de zones',
                    'count' => $count,
                    'total_sl' => $sl,
                    'total_rp' => $rp,
                    'details' => [],
                ];

                if (count($lines) < $count) {
                    $this->log("Nombre insuffisant de lignes pour les détails de capture", 'WARN');
                    return [];
                }
                
                // Parse capture details
                for ($i = 0; $i < $count; $i++) {
                    $detailLine = trim(array_shift($lines));
                    $pattern = '/^(\d{1,2}:\d{2})\s+(.+?)\s{2,}(\d+)\%\s{2,}(\d+)\s+points de score\s{2,}.+?(\d+) SL\s{2,}.+?(\d+) RP/';
                    if (preg_match($pattern, $detailLine, $matches)) {
                        $data['details'][] = [
                            'timestamp_sec' => $this->timeToSeconds($matches[1]),
                            'vehicle_name' => trim($matches[2]),
                            'capture_percentage' => (int)$matches[3],
                            'point_score' => (int)$matches[4],
                            'sl_awarded' => (int)$matches[5],
                            'rp_awarded' => (int)$matches[6],
                        ];
                    } else {
                        $this->log("Détail de capture invalide: '$detailLine'", 'WARN');
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Parse mission costs/prices
     * @param string $line Current line
     * @param array $lines Remaining lines (will be modified)
     * @return array cost data for each vehicle
     */
    private function parseMissionPrice(string $line, array &$lines): array {
        $data = [];
        if (preg_match('/^Prix\s{2,}(\d+)\s{2,}(\d+) SL(?:\s{2,}(\d+) RP)?$/i', $line, $m)) {
            $count = (int)$m[1];
            $sl = (int)$m[2];
            $rp = isset($m[3]) ? (int)$m[3] : 0;
            $data = [
                'repair_cost_sl' => $sl,
                'repair_cost_rp' => $rp,
                'details' => [],
            ];
            
            if (count($lines) < $count) {
                $this->log("Nombre insuffisant de lignes pour les détails des prix", 'WARN');
                return [];
            }
            
            for ($i = 0; $i < $count; $i++) {
                $detailLine = trim(array_shift($lines));
                if (preg_match('/^(.+?)\s{2,}(.+?)\s{2,}.*?(\d+) SL(?:\s{2,}.*?(\d+) RP)?$/i', $detailLine, $matches)) {
                    $data['details'][] = [
                        'vehicle_name' => trim($matches[1]),
                        'repair_reason' => trim($matches[2]),
                        'sl_cost' => (int)$matches[3],
                        'rp_cost' => isset($matches[4]) ? (int)$matches[4] : 0,
                    ];
                } else {
                    $this->log("Détail du prix invalide: '$detailLine'", 'WARN');
                }
            }
            return $data;
        }
        return [];
    }

    /**
     * Parse mission bonuses
     * @param string $line Current line
     * @param array $lines Remaining lines (will be modified)
     * @return array bonus data
     */
    private function parseMissionBonus(string $line, array &$lines): array {
        $data = [];
        if (preg_match('/^(.+?)(?:\s{2,}(\d+:\d+))?(?:\s{2,}(\d+) SL)?(?:\s{2,}(\d+) RP)?$/i', $line, $m)) {
            $bonusName = trim($m[1]);
            if (!in_array($bonusName, $this->bonusNames)) {
                return [];
            }
            
            $timestamp = !empty($m[2]) ? $this->timeToSeconds($m[2]) : 0;
            $sl = !empty($m[3]) ? (int)$m[3] : 0;
            $rp = !empty($m[4]) ? (int)$m[4] : 0;
            
            $data = [
                'bonus_name' => $bonusName,
                'timestamp_sec' => $timestamp,
                'sl_awarded' => $sl,
                'rp_awarded' => $rp,
            ];
        }
        return $data;
    }

    /**
     * Parse mission duration (from "Temps Joué" line)
     * @param string $line Current line
     * @return int Mission duration in seconds (0 if not found)
     */
    private function parseMissionDuration(string $line): int {
        if (preg_match('/^Temps\s+Joué\s{2,}(\d{1,2}):(\d{2})/i', $line, $m)) {
            $minutes = (int)$m[1];
            $seconds = (int)$m[2];
            return $minutes * 60 + $seconds;
        }
        return 0;
    }

    /**
     * Parse session ID (from "Session:" line)
     * @param string $line Current line
     * @return string|null Session ID or null if not found
     */
    private function parseSessionId(string $line): ?string {
        if (preg_match('/^Session:\s*([a-f0-9]+)\s*$/i', $line, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    /**
     * Parse a complete mission report into a database-ready structure
     * 
     * @param string $report Raw mission report text
     * @return array Structure compatible with DB: {mission, actions, mission_bonuses, activity_data, costs}
     */
    public function parse(string $report): array {
        $lines = preg_split("/\r?\n/", $report);
        
        $missionData = [
            'mission' => [
                'mission_type' => null,
                'location' => null,
                'result' => null,
                'mission_duration_sec' => 0,
                'session_id' => null,
                'total_sl' => 0,
                'total_crp' => 0,
                'total_rp' => 0,
                'activity_pct' => 0,
                'repair_cost' => 0,
                'ammo_crew_cost' => 0,
                'victory_reward' => 0,
                'participation_reward' => 0,
                'earned_final' => 0,
            ],
            'actions' => [],
            'mission_bonuses' => [],
            'activity_data' => [],
            'costs' => [],
        ];
        
        $vehicleActivityData = [];
        
        while (count($lines) > 0) {
            $currentLine = trim(array_shift($lines));
            
            if ($currentLine === '') {
                continue;
            }
            
            // Parse mission description
            $descriptionData = $this->parseMissionDescription($currentLine);
            if (!empty($descriptionData)) {
                $this->log("Parsed mission description", 'VERBOSE');
                $missionData['mission'] = array_merge($missionData['mission'], $descriptionData);
                continue;
            }
            
            // Parse combat actions
            $actionData = $this->parseMissionActions($currentLine, $lines);
            if (!empty($actionData)) {
                $this->log("Parsed mission actions: {$actionData['type_action']}", 'VERBOSE');
                $this->addActionsToMissionData($missionData, $actionData);
                continue;
            }
            
            // Parse zone captures
            $captureData = $this->parseMissionCapture($currentLine, $lines);
            if (!empty($captureData)) {
                $this->log("Parsed zone captures", 'VERBOSE');
                $this->addActionsToMissionData($missionData, $captureData);
                continue;
            }
            
            // Parse costs/prices
            $priceData = $this->parseMissionPrice($currentLine, $lines);
            if (!empty($priceData)) {
                $this->log("Parsed mission costs", 'VERBOSE');
                $missionData['costs'] = $priceData;
                continue;
            }
            
            // Parse mission duration (before bonuses, since "Temps Joué" can match both)
            $duration = $this->parseMissionDuration($currentLine);
            if ($duration > 0) {
                $this->log("Parsed mission duration: {$duration}s", 'VERBOSE');
                $missionData['mission']['mission_duration_sec'] = $duration;
                // Still consume the remaining lines for this section (vehicle details)
                while (count($lines) > 0) {
                    $nextLine = trim($lines[0]);
                    if ($nextLine === '' || preg_match('/^[A-Z]/', $nextLine)) {
                        break;
                    }
                    array_shift($lines);
                }
                continue;
            }
            
            // Parse bonuses
            $bonusData = $this->parseMissionBonus($currentLine, $lines);
            if (!empty($bonusData)) {
                $this->log("Parsed bonus: {$bonusData['bonus_name']}", 'VERBOSE');
                $missionData['mission_bonuses'][] = $bonusData;
                continue;
            }
            
            // Parse session ID
            $sessionId = $this->parseSessionId($currentLine);
            if ($sessionId !== null) {
                $this->log("Parsed session ID: {$sessionId}", 'VERBOSE');
                $missionData['mission']['session_id'] = $sessionId;
                continue;
            }
            
            $this->log("Unparsed line: '$currentLine'");
        }
        
        return $missionData;
    }
    
    /**
     * Add parsed actions to mission data structure
     * Accumulates totals and flattens details to actions array
     * 
     * @param array $missionData Mission data (passed by reference)
     * @param array $actionData Parsed action data with details
     */
    private function addActionsToMissionData(array &$missionData, array $actionData): void {
        $missionData['mission']['total_sl'] += $actionData['total_sl'] ?? 0;
        $missionData['mission']['total_rp'] += $actionData['total_rp'] ?? 0;
        
        if (!empty($actionData['details']) && is_array($actionData['details'])) {
            foreach ($actionData['details'] as $detail) {
                $action = [
                    'type_action' => $actionData['type_action'] ?? 'Unknown',
                    'timestamp_sec' => $detail['timestamp_sec'] ?? 0,
                    'vehicle_name' => $detail['vehicle_name'] ?? 'Unknown',
                    'weapon_used' => $detail['weapon_used'] ?? null,
                    'target_name' => $detail['target_name'] ?? null,
                    'point_score' => $detail['point_score'] ?? 0,
                    'sl_awarded' => $detail['sl_awarded'] ?? 0,
                    'rp_awarded' => $detail['rp_awarded'] ?? 0,
                ];
                $missionData['actions'][] = $action;
            }
        }
    }
}