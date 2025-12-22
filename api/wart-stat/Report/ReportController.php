<?php

namespace WartStat\Report;

use DateTime;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReportController
{

    public function __construct(
        private ReportRepository $repository,
        private ReportValidator $validator
    ) {
    }

    protected function makeJsonResponse(Response $response, int $code, $data): Response
    {
        $body = "";
        try {
            $body = json_encode($data);
        } catch (\Throwable $th) {
            throw $th;
        }
        $response->getBody()->write($body);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($code);
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

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
