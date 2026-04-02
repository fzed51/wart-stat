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
        private ReportValidator $validator,
        private ReportParser $parser,
        private MissionRepository $missionRepository,
        private MissionActionRepository $actionRepository,
        private MissionBonusRepository $bonusRepository,
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
}
