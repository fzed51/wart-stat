<?php

namespace WartStat\Report;

use Monolog\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use WartStat\Base\Controller;

class ReportController extends Controller
{

    public function __construct(
        private ReportRepository $repository,
        private ReportDetailsRepository $detailsRepository,
        private ReportValidator $validator,
        private ReportParser $parser,
        private MissionRepository $missionRepository,
        private MissionActionRepository $actionRepository,
        private MissionBonusRepository $bonusRepository,
        private ReportFileHandler $fileHandler,
        private Logger $logger
    ) {
    }

    public function create(Request $request, Response $response): Response
    {
        $this->logger->info('~create~');

        $data = $this->parseRequestBody($request);

        // Validation
        if (!$this->validator->safeValidate($data)) {
            $errors = $this->validator->getErrors();
            return $this->makeJsonResponse($response, 400, ['errors' => $errors]);
        }

        // 1. Créer le report
        $report = $this->repository->create([
            'country' => $data['country'],
            'datetime' => $data['datetime'],
            'content' => $data['content'],
        ]);

        // 1.5 Enregistrer le contenu du rapport dans un fichier
        $fileInfo = $this->fileHandler->saveReport($data['content']);
        $this->logger->debug("Report saved to file", [
            'report_id' => $report['id'],
            'file_index' => $fileInfo['index'],
            'filename' => $fileInfo['filename'],
        ]);

        // 2. Parser le contenu du report
        $parsedData = $this->parser->parse($data['content']);

        // 3. Créer la mission avec report_id
        $missionData = $parsedData['mission'];
        $missionData['report_id'] = $report['id'];
        $mission = $this->missionRepository->create($missionData);
        $this->logger->debug("Mission created for report {$report['id']}: {$mission['id']}");

        // 4. Créer les actions associées
        foreach ($parsedData['actions'] as $actionData) {
            $actionData['mission_id'] = $mission['id'];
            $this->actionRepository->create($actionData);
        }
        $this->logger->debug("Created " . count($parsedData['actions']) . " actions for mission {$mission['id']}");

        // 5. Créer les bonus associés
        foreach ($parsedData['mission_bonuses'] as $bonusData) {
            $bonusData['mission_id'] = $mission['id'];
            $this->bonusRepository->create($bonusData);
        }
        $this->logger->debug("Created " . count($parsedData['mission_bonuses']) . " bonuses for mission {$mission['id']}");

        return $this->makeJsonResponse($response, 201, $report);
    }

    public function list(Response $response): Response
    {
        $this->logger->info('~list~');

        $reports = $this->repository->findAll();

        return $this->makeJsonResponse($response, 200, $reports);
    }

    public function listDetails(Request $request, Response $response): Response
    {
        $this->logger->info('~listDetails~');

        $params = $request->getQueryParams();
        $limit = (int)($params['limit'] ?? 1000);
        $offset = (int)($params['offset'] ?? 0);
        $country = $params['country'] ?? null;

        if ($country) {
            $reports = $this->detailsRepository->findByCountry($country, $limit, $offset);
        } else {
            $reports = $this->detailsRepository->findAll($limit, $offset);
        }

        return $this->makeJsonResponse($response, 200, $reports);
    }

    public function getById(Request $request, Response $response): Response
    {
        $this->logger->info('~getById~');

        $reportId = (int)$request->getAttribute('id');

        // Get report details
        $report = $this->repository->findById($reportId);
        if (!$report) {
            return $this->makeJsonResponse($response, 404, ['error' => 'Report not found']);
        }

        // Get associated mission
        $missions = $this->missionRepository->findByReportId($reportId, 1);
        $mission = $missions[0] ?? null;

        if (!$mission) {
            return $this->makeJsonResponse($response, 404, ['error' => 'Mission not found for report']);
        }

        // Get associated actions
        $actions = $this->actionRepository->findByMissionId($mission['id']);

        // Get associated bonuses
        $bonuses = $this->bonusRepository->findByMissionId($mission['id']);

        return $this->makeJsonResponse($response, 200, [
            'report' => $report,
            'mission' => $mission,
            'actions' => $actions,
            'bonuses' => $bonuses,
        ]);
    }

    public function update(Request $request, Response $response): Response
    {
        $this->logger->info('~update~');

        $reportId = (int)$request->getAttribute('id');
        $data = $this->parseRequestBody($request);

        // Check report exists
        $report = $this->repository->findById($reportId);
        if (!$report) {
            return $this->makeJsonResponse($response, 404, ['error' => 'Report not found']);
        }

        // Validate data (only country and datetime can be updated)
        $allowedFields = [];
        if (isset($data['country'])) {
            $allowedFields['country'] = $data['country'];
        }
        if (isset($data['datetime'])) {
            $allowedFields['datetime'] = $data['datetime'];
        }

        if (empty($allowedFields)) {
            return $this->makeJsonResponse($response, 400, ['error' => 'No valid fields to update']);
        }

        // Update the report
        $updatedReport = $this->repository->update($reportId, $allowedFields);

        return $this->makeJsonResponse($response, 200, $updatedReport);
    }
}
