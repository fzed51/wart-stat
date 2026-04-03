<?php
declare(strict_types=1);

namespace WartStat\Report;

use Monolog\Logger;

class ReportFileHandler
{
    private string $reportDir;

    public function __construct(private Logger $logger)
    {
        $this->reportDir = __DIR__ . '/../../../report';
        $this->ensureDirectoryExists();
    }

    /**
     * Enregistrer le contenu du rapport dans un fichier
     * Convention: /report/report{n}.txt où n est l'index du rapport
     *
     * @param string $content Contenu du rapport
     * @return array Tableau avec 'index' et 'filePath'
     */
    public function saveReport(string $content): array
    {
        $nextIndex = $this->getNextIndex();
        $filename = sprintf('report%d.txt', $nextIndex);
        $filePath = $this->reportDir . '/' . $filename;

        if (!file_put_contents($filePath, $content)) {
            throw new \RuntimeException("Failed to write report file: $filePath");
        }

        $this->logger->debug("Report file saved", [
            'index' => $nextIndex,
            'filePath' => $filePath,
        ]);

        return [
            'index' => $nextIndex,
            'filePath' => $filePath,
            'filename' => $filename,
        ];
    }

    /**
     * Obtenir le prochain index (max index existant + 1)
     */
    private function getNextIndex(): int
    {
        if (!is_dir($this->reportDir)) {
            return 1;
        }

        $files = scandir($this->reportDir, SCANDIR_SORT_NONE);
        if ($files === false) {
            throw new \RuntimeException("Failed to read report directory: {$this->reportDir}");
        }

        $maxIndex = 0;

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            // Extraire l'index du fichier (ex: report123.txt → 123)
            if (preg_match('/^report(\d+)\.txt$/', $file, $matches)) {
                $index = (int) $matches[1];
                if ($index > $maxIndex) {
                    $maxIndex = $index;
                }
            }
        }

        return $maxIndex + 1;
    }

    /**
     * S'assurer que le répertoire des rapports existe
     */
    private function ensureDirectoryExists(): void
    {
        if (!is_dir($this->reportDir)) {
            if (!mkdir($this->reportDir, 0755, true) && !is_dir($this->reportDir)) {
                throw new \RuntimeException(sprintf(
                    'Directory "%s" was not created',
                    $this->reportDir
                ));
            }
            $this->logger->info("Report directory created", ['path' => $this->reportDir]);
        }
    }
}
