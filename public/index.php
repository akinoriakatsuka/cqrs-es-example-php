<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Application\GroupChatController;

$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true);

// Test endpoints
$app->get('/health', function ($request, $response, $args) {
    $data = [
        'status' => 'OK',
    ];
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});
$app->run();
