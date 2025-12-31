<?php

namespace WartStat\Report;

class ReportParser {
    
    /* string[] */
    protected array $actionNames = [
        'Destruction de cibles terrestres',
        'Destruction d\'avions',
        'Assistance à la destruction d\'adversaires',
        'Dégâts importants infligés à l\'ennemi',
        'Coups critiques infligés aux ennemis',
        'Dégâts infligés aux ennemis',
    ];
    /* string[] */
    protected array $bonusNames = [
        "Temps d'activité",
        "Temps Joué",
        "Récompense pour la victoire",
        "Bonus de Compétence"
    ];

    function __construct() {}

    /**
     * Convertit un temps au format MM:SS en secondes
     * @param string $time Temps au format MM:SS ou M:SS (ex: "3:40")
     * @return int Nombre total de secondes
     */
    function timeToSeconds(string $time): int {
        $parts = explode(':', trim($time));
        if (count($parts) !== 2) {
            fwrite(STDERR, "[WARN] Format de temps invalide: '$time'\n");
            return 0;
        }
        $minutes = (int)$parts[0];
        $seconds = (int)$parts[1];
        return $minutes * 60 + $seconds;
    }

    function parseMissionDescription (string $line): array {
        $data = [];
        fwrite(STDERR, "[DEBUG] Parsing mission description: '$line'\n");
        $descriptionMatch = preg_match('/^(?:\xEF\xBB\xBF)?(\S+) en \[(.+?)\] (.+?) mission\!?/i', $line, $m);
        if ($descriptionMatch) {
            $data['success'] = strtolower($m[1]) === "victoire";
            $data['type'] = $m[2];
            $data['map'] = trim($m[3]);
        }
        return $data;
    }

    function parseMissionActions (string $line, array &$lines): array {
        $data = [];
        if (preg_match('/^(.+?)  ? /i', $line, $m)) {
            $actionName = trim($m[1]);
            if (!in_array($actionName,$this->actionNames)) {
                return [];
            }
            $regMatch = preg_match('/\s{2,}(\d+)\s{2,}(\d+) SL\s{2,}(\d+) RP$/i',$line, $m);
            if ($regMatch) {
                $count = (int)$m[1];
                $sl = (int)$m[2];
                $rp = (int)$m[3];
                $data = [
                    'name' => $actionName,
                    'count' => $count,
                    'sl' => $sl,
                    'rp' => $rp,
                ];

                if (count($lines) < $count) {
                    fwrite(STDERR, "[WARN] Nombre insuffisant de lignes pour les détails des actions de '$actionName'\n");
                    return [];
                }
                // Lire les lignes suivantes pour les détails
                for ($i = 0; $i < $count; $i++) {
                    $detailLine = trim(array_shift($lines));
                    $pattern = '/^\s*(\d{1,2}:\d{2})\s+(.+?)(?:\s{2,}\((.+?)\))?\s{2,}(.+?)\s{2,}(.+?)\s{2,}(\d+)\s+points\s+.+?= (\d+) SL\s+.+?= (\d+) RP/';
                    if (preg_match($pattern, $detailLine, $matches)) {
                        $data['stats'][] = [
                            'time' => $this->timeToSeconds($matches[1]),
                            'vehicle' => $matches[2],
                            'originVehicle'=> $matches[3] ?: null,
                            'ammunition ' => trim($matches[4]),
                            'target' => trim($matches[5]),
                            'points' => (int)$matches[6],
                            'sl' => (int)$matches[7],
                            'rp' => (int)$matches[8],
                        ];
                    } else {
                        fwrite(STDERR, "[WARN] Détail de l'action ne correspond pas au format attendu: '$detailLine'\n");       
                    }

                }
            }
        }
        return $data;
    }


    function parseMissionCaptur (string $line, array &$lines): array {
        $data = [];
        if (preg_match('/^(.+?)  ? /i', $line, $m)) {
            $actionName = trim($m[1]);
            if ($actionName !== 'Capture de zones') {
                return [];
            }
            $regMatch = preg_match('/\s{2,}(\d+)\s{2,}(\d+) SL\s{2,}(\d+) RP$/i',$line, $m);
            if ($regMatch) {
                $count = (int)$m[1];
                $sl = (int)$m[2];
                $rp = (int)$m[3];
                $data = [
                    'name' => 'Capture de zones',
                    'count' => $count,
                    'sl' => $sl,
                    'rp' => $rp,
                ];

                if (count($lines) < $count) {
                    fwrite(STDERR, "[WARN] Nombre insuffisant de lignes pour les détails des actions de '$actionName'\n");
                    return [];
                }
                // Lire les lignes suivantes pour les détails
                for ($i = 0; $i < $count; $i++) {
                    $detailLine = trim(array_shift($lines));
                    $pattern = '/^(\d{1,2}:\d{2})\s+(.+?)\s{2,}(\d+)\%\s{2,}(\d+)\s+points de score\s{2,}.+?(\d+) SL\s{2,}.+?(\d+) RP/';
                    if (preg_match($pattern, $detailLine, $matches)) {
                        $data['stats'][] = [
                            'time' => $this->timeToSeconds($matches[1]),
                            'vehicle' => $matches[2],
                            'part'=> $matches[3],
                            'points' => (int)$matches[6],
                            'sl' => (int)$matches[7],
                            'rp' => (int)$matches[8],
                        ];
                    } else {
                        fwrite(STDERR, "[WARN] Détail de l'action ne correspond pas au format attendu: '$detailLine'\n");       
                    }

                }
            }
        }
        return $data;
    }

    function parseMissionPrice (string $line, array &$lines): array {
        $data = [];
        if (preg_match('/^Prix\s{2,}(\d+)\s{2,}(\d+) SL(?:\s{2,}(\d+) RP)?$/i', $line, $m)) {
            $count = (int)$m[1];
            $sl = (int)$m[2];
            $rp = (int)($m[3] ?: 0);
            $data = [
                'name' => 'Prix',
                'count' => $count,
                'sl' => $sl,
                'rp' => $rp,
            ];
            if (count($lines) < $count) {
                fwrite(STDERR, "[WARN] Nombre insuffisant de lignes pour les détails des prix\n");
                return [];
            }
            for ($i = 0; $i < $count; $i++) {
                $detailLine = trim(array_shift($lines));
                if (preg_match('/^(.+?)\s{2,}(.+?)\s{2,}.*?(\d+) SL(?:\s{2,}.*?(\d+) RP)?$/i', $detailLine, $matches)) {
                    $time = $matches[1];
                    $price = $matches[2];
                    $sl = (int)$matches[3];
                    $rp = (int)($matches[4] ?: 0);
                    $data['stats'][] = [
                        'time' => $this->timeToSeconds($time),
                        "price" => $price,
                        'sl' => $sl,
                        'rp' => $rp,
                    ];
                } else {
                    fwrite(STDERR, "[WARN] Détail du prix ne correspond pas au format attendu: '$detailLine'\n");       
                }
            }
            return $data;
        }
        return [];
    }

    function parseMissionBonus (string $line, array &$lines): array {
        $data = [];
        if (preg_match('/^(.+?)(?:\s{2,}(\d+:\d+))?(?:\s{2,}(\d+) SL)?(?:\s{2,}(\d+) RP)?$/i', $line, $m)) {
            $bonusName = $m[1];
            if (!in_array($bonusName,$this->bonusNames)) {
                return [];
            }
            $time = !empty($m[2]) ? $this->timeToSeconds($m[2]) : null;
            $sl = !empty($m[3]) ? (int)$m[3] : 0;
            $rp = !empty($m[4]) ? (int)$m[4] : 0;
            $data = [
                'name' => $bonusName,
                'time' => $time,
                'sl' => $sl,
                'rp' => $rp,
            ];
            $detailLine = trim(array_shift($lines));
            while ($detailLine !== "") {
                # code...
                $detailLine = trim(array_shift($lines));
            }
        } else {
            fwrite(STDERR, "[WARN] Ligne de bonus ne correspond pas au format attendu: '$line'\n");
            return [];
        }
        return $data;
    }

    function parse (string $report): array {
        $lines = preg_split("/\r?\n/", $report);
        $currentLine = '';
        $data = [];

        while (count($lines) > 0) {
            $countLine = count($lines);
            $currentLine = trim(array_shift($lines));
            if ($currentLine === '') {
                continue;
            }
            $descriptionData = $this->parseMissionDescription($currentLine);
            if (!empty($descriptionData)) {
                fwrite(STDERR, "[VERBOSE] Lines is description\n");
                $data = [...$data, ...$descriptionData];
                continue;
            }
            $actionData = $this->parseMissionActions($currentLine, $lines);
            if(!empty($actionData)) {
                fwrite(STDERR, "[VERBOSE] Lines is action\n");
                $data = [
                    ...$data, 
                    $actionData['name'] => $actionData
                ];
                continue;
            }
            $priceData = $this->parseMissionPrice($currentLine, $lines);
            if(!empty($priceData)) {
                fwrite(STDERR, "[VERBOSE] Lines is price\n");
                $data = [
                    ...$data, 
                    'Prix' => $priceData
                ];
                continue;
            }
            $bonusData = $this->parseMissionBonus($currentLine, $lines);
            if(!empty($bonusData)) {
                fwrite(STDERR, "[DEBUG] Lines is bonus\n");
                $data = [
                    ...$data, 
                    $bonusData["name"] => $bonusData
                ];
                continue;
            }
            fwrite(STDERR, "[DEBUG] Lines remaining: {$countLine} \n");
        }


        return $data;
    }
}