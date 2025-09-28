<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Resolvers\MutationResolver;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Schema\GroupChatSchema;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\Repository\GroupChatRepositoryImpl;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Processor\GroupChatCommandProcessor;
use GraphQL\GraphQL;
use J5ik2o\EventStoreAdapterPhp\EventStoreFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// Load environment variables
$dotenv_path = __DIR__ . '/../.env';
if (file_exists($dotenv_path)) {
    $env_vars = parse_ini_file($dotenv_path);
    foreach ($env_vars as $key => $value) {
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
        }
    }
}

// Environment configuration
$app_debug = filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
$graphql_debug = filter_var($_ENV['GRAPHQL_DEBUG_MODE'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
$allowed_origins = explode(',', $_ENV['GRAPHQL_CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:3000');

$app = AppFactory::create();

$app->addErrorMiddleware($app_debug, true, true);

// EventStore setup - In-Memory for development
$eventStore = EventStoreFactory::createInMemory();

// Repository setup
$repository = new GroupChatRepositoryImpl($eventStore);

// Command processor setup
$commandProcessor = new GroupChatCommandProcessor($repository);

// GraphQL resolver setup
$mutationResolver = new MutationResolver($commandProcessor);

// Schema setup
$groupChatSchema = new GroupChatSchema($mutationResolver);
$schema = $groupChatSchema->build();

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
$add_cors_headers = function (Response $response, string $origin = null) use ($allowed_origins): Response {
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

// GraphQL endpoint
$app->post('/graphql', function (Request $request, Response $response) use ($schema, $graphql_debug, $add_cors_headers) {
    $origin = $request->getHeaderLine('Origin');

    try {
        // Validate Content-Type
        $content_type = $request->getHeaderLine('Content-Type');
        if (!str_contains($content_type, 'application/json')) {
            throw new \InvalidArgumentException('Content-Type must be application/json');
        }

        $body = $request->getBody()->getContents();

        // Validate request body is not empty
        if (empty(trim($body))) {
            throw new \InvalidArgumentException('Request body cannot be empty');
        }

        $input = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON in request body: ' . json_last_error_msg());
        }

        // Validate required fields
        if (!isset($input['query']) || !is_string($input['query'])) {
            throw new \InvalidArgumentException('Query field is required and must be a string');
        }

        $query = trim($input['query']);
        if (empty($query)) {
            throw new \InvalidArgumentException('Query cannot be empty');
        }

        $variable_values = $input['variables'] ?? null;

        // Validate variables if provided
        if ($variable_values !== null && !is_array($variable_values)) {
            throw new \InvalidArgumentException('Variables must be an object/array');
        }

        $result = GraphQL::executeQuery($schema, $query, null, null, $variable_values);
        $output = $result->toArray();

    } catch (\Exception $e) {
        $error_output = [
            'errors' => [
                [
                    'message' => $e->getMessage(),
                ],
            ],
        ];

        // Add debug information only in debug mode
        if ($graphql_debug) {
            $error_output['errors'][0]['trace'] = $e->getTraceAsString();
            $error_output['errors'][0]['file'] = $e->getFile();
            $error_output['errors'][0]['line'] = $e->getLine();
        }

        $output = $error_output;
    }

    $response->getBody()->write(json_encode($output, JSON_PRETTY_PRINT));
    $response = $response->withHeader('Content-Type', 'application/json; charset=UTF-8');

    return $add_cors_headers($response, $origin);
});

// CORS preflight handler
$app->options('/graphql', function (Request $request, Response $response) use ($add_cors_headers) {
    $origin = $request->getHeaderLine('Origin');
    return $add_cors_headers($response, $origin);
});

$app->run();
