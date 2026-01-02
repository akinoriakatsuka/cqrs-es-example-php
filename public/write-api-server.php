<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use DI\ContainerBuilder;
use GraphQL\Error\DebugFlag;
use GraphQL\GraphQL;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;

$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(true);
$containerBuilder->useAttributes(true);

$configLoader = require __DIR__ . '/../config/di-write-api.php';
$configLoader($containerBuilder);

$container = $containerBuilder->build();

$app = AppFactory::createFromContainer($container);
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$app->add(function (Request $request, RequestHandler $handler): Response {
    $response = $handler->handle($request);

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type');
});

$app->options('/{routes:.+}', function (Request $request, Response $response): Response {
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/health', function (Request $request, Response $response): Response {
    $response->getBody()->write(json_encode(['status' => 'ok']));

    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/query', function (Request $request, Response $response) use ($container): Response {
    $payload = (array)$request->getParsedBody();
    $query = $payload['query'] ?? '';
    $variables = $payload['variables'] ?? null;

    try {
        $schema = $container->get('GraphQLSchema');
        $result = GraphQL::executeQuery($schema, $query, null, null, $variables);
        $output = $result->toArray(DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE);
    } catch (\Throwable $e) {
        $output = [
            'errors' => [
                [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ],
            ],
        ];
    }

    $response->getBody()->write(json_encode($output));

    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/', function (Request $request, Response $response): Response {
    $html = <<<'HTML'
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <title>GraphQL Playground - Write API</title>
            <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/graphql-playground-react/build/static/css/index.css" />
            <link rel="shortcut icon" href="//cdn.jsdelivr.net/npm/graphql-playground-react/build/favicon.png" />
            <script src="//cdn.jsdelivr.net/npm/graphql-playground-react/build/static/js/middleware.js"></script>
        </head>
        <body>
            <div id="root"></div>
            <script>
                window.addEventListener('load', function () {
                    GraphQLPlayground.init(document.getElementById('root'), {
                        endpoint: '/query'
                    })
                })
            </script>
        </body>
        </html>
        HTML;

    $response->getBody()->write($html);

    return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
});

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function (Request $request, Response $response): Response {
    $response->getBody()->write(json_encode(['error' => 'Not found']));

    return $response
        ->withStatus(404)
        ->withHeader('Content-Type', 'application/json');
});

$app->run();
