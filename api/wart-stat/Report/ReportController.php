<?php

namespace WartStat\Report;

use Monolog\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \WartStat\Base\Controller;

class ReportController extends Controller
{

    public function __construct(
        private ReportRepository $repository,
        private ReportValidator $validator,
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

        $report = $this->repository->create([
            'country' => $data['country'],
            'datetime' => $data['datetime'],
            'content' => $data['content'],
        ]);

        return $this->makeJsonResponse($response, 201, $report);
    }
}
