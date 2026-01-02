<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DI\ContainerBuilder;
use GraphQL\GraphQL;
use GraphQL\Error\DebugFlag;

// DIコンテナの設定と構築
$container_builder = new ContainerBuilder();
$container_builder->useAutowiring(true);
$container_builder->useAttributes(true);

// 設定ファイルの読み込み
$config_loader = require __DIR__ . '/../config/di-write-api.php';
$config_loader($container_builder);

// コンテナのビルド
$container = $container_builder->build();

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Content-Type: application/json');
    http_response_code(200);
    exit;
}

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_method = $_SERVER['REQUEST_METHOD'];

// Health check
if ($request_uri === '/health' && $request_method === 'GET') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    exit;
}

// GraphQL endpoint
if ($request_uri === '/query' && $request_method === 'POST') {
    header('Content-Type: application/json');

    try {
        $schema = $container->get('GraphQLSchema');

        $raw_input = file_get_contents('php://input');
        $input = json_decode($raw_input, true);

        $query = $input['query'] ?? '';
        $variables = $input['variables'] ?? null;

        $result = GraphQL::executeQuery($schema, $query, null, null, $variables);
        $output = $result->toArray(DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE);
    } catch (\Throwable $e) {
        $output = [
            'errors' => [
                [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]
            ]
        ];
    }

    echo json_encode($output);
    exit;
}

// GraphQL Playground
if ($request_uri === '/' && $request_method === 'GET') {
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html>
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
        window.addEventListener('load', function (event) {
            GraphQLPlayground.init(document.getElementById('root'), {
                endpoint: '/query'
            })
        })
    </script>
</body>
</html>
    <?php
    exit;
}

// Not found
http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['error' => 'Not found']);