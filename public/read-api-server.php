<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL\Schema;
use GraphQL\GraphQL;
use GraphQL\Error\DebugFlag;

// 環境変数を取得
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_port = getenv('DB_PORT') ?: '3306';
$db_database = getenv('DB_DATABASE') ?: 'ceer';
$db_username = getenv('DB_USERNAME') ?: 'root';
$db_password = getenv('DB_PASSWORD') ?: 'passwd';

// PDO接続
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $db_host, $db_port, $db_database);

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_method = $_SERVER['REQUEST_METHOD'];

// Health check
if ($request_uri === '/health' && $request_method === 'GET') {
    echo json_encode(['status' => 'ok']);
    exit;
}

// GraphQL endpoint
if ($request_uri === '/query' && $request_method === 'POST') {
    $schema = Schema::build($pdo);

    $raw_input = file_get_contents('php://input');
    $input = json_decode($raw_input, true);

    $query = $input['query'] ?? '';
    $variables = $input['variables'] ?? null;

    try {
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
    ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>GraphQL Playground</title>
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
echo json_encode(['error' => 'Not found']);
