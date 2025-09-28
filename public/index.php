<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Resolvers\MutationResolver;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Schema\GroupChatSchema;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\Repository\GroupChatRepositoryImpl;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Processor\GroupChatCommandProcessor;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\Middleware\RateLimitMiddleware;
use Dotenv\Dotenv;
use GraphQL\GraphQL;
use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\QueryDepth;
use GraphQL\Validator\DocumentValidator;
use Aws\DynamoDb\DynamoDbClient;
use J5ik2o\EventStoreAdapterPhp\EventStoreFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// Load environment variables securely using phpdotenv
$dotenv_path = __DIR__ . '/..';
if (file_exists($dotenv_path . '/.env')) {
    $dotenv = Dotenv::createImmutable($dotenv_path);
    $dotenv->safeLoad();
}

// Environment configuration with defaults
$app_debug = filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
$graphql_debug = filter_var($_ENV['GRAPHQL_DEBUG_MODE'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
$allowed_origins_string = $_ENV['GRAPHQL_CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:3000';
$allowed_origins = array_map('trim', explode(',', $allowed_origins_string));
$max_query_complexity = (int)($_ENV['GRAPHQL_MAX_QUERY_COMPLEXITY'] ?? 100);
$max_query_depth = (int)($_ENV['GRAPHQL_MAX_QUERY_DEPTH'] ?? 10);
$rate_limit_max_requests = (int)($_ENV['RATE_LIMIT_MAX_REQUESTS'] ?? 60);
$rate_limit_window_seconds = (int)($_ENV['RATE_LIMIT_WINDOW_SECONDS'] ?? 60);

// DynamoDB configuration
$event_store_type = $_ENV['EVENT_STORE_TYPE'] ?? 'memory';
$dynamodb_endpoint = $_ENV['DYNAMODB_ENDPOINT'] ?? 'http://localhost:8000';
$dynamodb_region = $_ENV['DYNAMODB_REGION'] ?? 'us-east-1';
$dynamodb_access_key = $_ENV['DYNAMODB_ACCESS_KEY_ID'] ?? 'dummy';
$dynamodb_secret_key = $_ENV['DYNAMODB_SECRET_ACCESS_KEY'] ?? 'dummy';

$app = AppFactory::create();

$app->addErrorMiddleware($app_debug, true, true);

// EventStore setup - configurable based on environment
if ($event_store_type === 'dynamodb') {
    // DynamoDB client setup
    $dynamodb_client = new DynamoDbClient([
        'version' => 'latest',
        'region' => $dynamodb_region,
        'endpoint' => $dynamodb_endpoint,
        'credentials' => [
            'key' => $dynamodb_access_key,
            'secret' => $dynamodb_secret_key,
        ],
    ]);

    // EventStore creation with Goサンプル準拠のテーブル名とインデックス名
    $eventStore = EventStoreFactory::create(
        $dynamodb_client,
        'journal',                    // journalTableName
        'snapshot', // snapshotTableName
        'journal-aid-index',          // journalAidIndexName
        'snapshot-aid-index',         // snapshotAidIndexName
        32,                           // shardCount
        function ($event) {            // eventConverter
            return json_encode($event);
        },
        function ($snapshot) {         // snapshotConverter
            return json_encode($snapshot);
        }
    );
} else {
    // Default to in-memory for development
    $eventStore = EventStoreFactory::createInMemory();
}

// Repository setup
$repository = new GroupChatRepositoryImpl($eventStore);

// Command processor setup
$commandProcessor = new GroupChatCommandProcessor($repository);

// GraphQL resolver setup
$mutationResolver = new MutationResolver($commandProcessor);

// Schema setup
$groupChatSchema = new GroupChatSchema($mutationResolver);
$schema = $groupChatSchema->build();

// Rate limiter setup
$rate_limiter = new RateLimitMiddleware($rate_limit_max_requests, $rate_limit_window_seconds);

// Health check endpoint
$app->get('/health', function (Request $request, Response $response) {
    $data = [
        'status' => 'OK',
        'timestamp' => date('c'),
    ];
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

// Helper function for CORS headers
$add_cors_headers = function (Response $response, ?string $origin = null) use ($allowed_origins): Response {
    $allowed_origin = 'null';
    if ($origin && in_array($origin, $allowed_origins, true)) {
        $allowed_origin = $origin;
    } elseif (count($allowed_origins) === 1) {
        $allowed_origin = $allowed_origins[0];
    }

    return $response
        ->withHeader('Access-Control-Allow-Origin', $allowed_origin)
        ->withHeader('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type')
        ->withHeader('Access-Control-Max-Age', '86400');
};

// Simple rate limiting check function
$check_rate_limit = function (Request $request) use ($rate_limit_max_requests, $rate_limit_window_seconds): ?Response {
    static $requests = [];

    $client_ip = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
    $current_time = time();
    $window_start = $current_time - $rate_limit_window_seconds;

    // Clean old requests
    $requests[$client_ip] = array_filter($requests[$client_ip] ?? [], fn ($time) => $time > $window_start);

    // Check limit
    if (count($requests[$client_ip]) >= $rate_limit_max_requests) {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'errors' => [[
                'message' => 'Rate limit exceeded. Too many requests.',
                'extensions' => ['category' => 'rate_limit'],
            ]],
        ]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(429);
    }

    // Record request
    $requests[$client_ip][] = $current_time;
    return null;
};

// GraphQL endpoint
$app->post('/graphql', function (Request $request, Response $response) use ($schema, $graphql_debug, $add_cors_headers, $max_query_complexity, $max_query_depth, $check_rate_limit) {
    $origin = $request->getHeaderLine('Origin');

    // Check rate limit
    $rate_limit_response = $check_rate_limit($request);
    if ($rate_limit_response !== null) {
        return $add_cors_headers($rate_limit_response, $origin);
    }

    $http_status = 200;

    try {
        // Validate Content-Type
        $content_type = $request->getHeaderLine('Content-Type');
        if (!str_contains($content_type, 'application/json')) {
            $http_status = 400;
            throw new \InvalidArgumentException('Content-Type must be application/json');
        }

        $body = $request->getBody()->getContents();

        // Validate request body is not empty
        if (empty(trim($body))) {
            $http_status = 400;
            throw new \InvalidArgumentException('Request body cannot be empty');
        }

        $input = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $http_status = 400;
            throw new \InvalidArgumentException('Invalid JSON in request body: ' . json_last_error_msg());
        }

        // Validate required fields
        if (!isset($input['query']) || !is_string($input['query'])) {
            $http_status = 400;
            throw new \InvalidArgumentException('Query field is required and must be a string');
        }

        $query = trim($input['query']);
        if (empty($query)) {
            $http_status = 400;
            throw new \InvalidArgumentException('Query cannot be empty');
        }

        $variable_values = $input['variables'] ?? null;

        // Validate variables if provided
        if ($variable_values !== null && !is_array($variable_values)) {
            $http_status = 400;
            throw new \InvalidArgumentException('Variables must be an object/array');
        }

        // Configure GraphQL security validators
        $validation_rules = DocumentValidator::allRules();

        // Add query complexity and depth limits
        $validation_rules[] = new QueryComplexity($max_query_complexity);
        $validation_rules[] = new QueryDepth($max_query_depth);

        $result = GraphQL::executeQuery(
            $schema,
            $query,
            null,
            null,
            $variable_values,
            null,
            null,
            $validation_rules
        );

        $output = $result->toArray();

        // If there are GraphQL errors, set appropriate HTTP status
        if (!empty($output['errors'])) {
            $http_status = 400;
        }

    } catch (\InvalidArgumentException $e) {
        // Client errors (400)
        $error_output = [
            'errors' => [
                [
                    'message' => $e->getMessage(),
                    'extensions' => [
                        'category' => 'validation',
                    ],
                ],
            ],
        ];

        if ($graphql_debug) {
            $error_output['errors'][0]['extensions']['trace'] = $e->getTraceAsString();
            $error_output['errors'][0]['extensions']['file'] = $e->getFile();
            $error_output['errors'][0]['extensions']['line'] = $e->getLine();
        }

        $output = $error_output;

    } catch (\Throwable $e) {
        // Server errors (500)
        $http_status = 500;
        $error_output = [
            'errors' => [
                [
                    'message' => $graphql_debug ? $e->getMessage() : 'Internal server error',
                    'extensions' => [
                        'category' => 'internal',
                    ],
                ],
            ],
        ];

        if ($graphql_debug) {
            $error_output['errors'][0]['extensions']['trace'] = $e->getTraceAsString();
            $error_output['errors'][0]['extensions']['file'] = $e->getFile();
            $error_output['errors'][0]['extensions']['line'] = $e->getLine();
        }

        $output = $error_output;

        // Log server errors (in production, you'd use a proper logger)
        error_log(sprintf(
            'GraphQL Server Error: %s in %s:%d',
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        ));
    }

    $response->getBody()->write(json_encode($output, JSON_PRETTY_PRINT));
    $response = $response
        ->withHeader('Content-Type', 'application/json; charset=UTF-8')
        ->withStatus($http_status);

    return $add_cors_headers($response, $origin);
});

// CORS preflight handler
$app->options('/graphql', function (Request $request, Response $response) use ($add_cors_headers) {
    $origin = $request->getHeaderLine('Origin');
    return $add_cors_headers($response, $origin);
});

$app->run();
