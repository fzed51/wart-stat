<?php

namespace WartStat\Base;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Controller {
    protected function makeJsonResponse(ResponseInterface $response, int $code, $data): ResponseInterface
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

    protected function parseRequestBody(ServerRequestInterface $request): null|array|object
    {
        $data = $request->getBody()->getContents();
        $contentType = $request->getHeaderLine('Content-Type');
        var_dump($contentType,$data );
        if (str_contains($contentType, 'application/json')) {
            return json_decode($data, true);
        } elseif (str_contains($contentType, 'application/x-www-form-urlencoded')) {
            parse_str($data, $data);
            return $data;
        }
        return null;
    }
}