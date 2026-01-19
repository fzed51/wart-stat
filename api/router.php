<?php

use Slim\App;
use Slim\Routing\RouteCollectorProxy as Group;
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
    $app->group('/reports', function (Group $group) {
        $group->get('', [ReportController::class, 'list']);
        //$group->get('/{id}', [ReportController::class, 'getById']);
        $group->post('', [ReportController::class, 'create']);
        //$group->put('/{id}', [ReportController::class, 'update']);
        //$group->delete('/{id}', [ReportController::class, 'delete']);
    });
};