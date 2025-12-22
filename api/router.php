<?php

use Slim\App;
use WartStat\Report\ReportController;

return function (App $app) {  
    // Define your routes here
    // Example:
    // $app->get('/hello', function ($request, $response, $args) {
    //     $response->getBody()->write("Hello, world!");
    //     return $response;
    // });
    // ou :
    // $app->get('/hello', [YourController::class, 'yourMethod']);
    
    // Reports
    $app->group('/reports', function (App $app) {
        //$app->get('', [ReportController::class, 'list']);
        //$app->get('/{id}', [ReportController::class, 'getById']);
        $app->post('', [ReportController::class, 'create']);
        //$app->put('/{id}', [ReportController::class, 'update']);
        //$app->delete('/{id}', [ReportController::class, 'delete']);
    });
};