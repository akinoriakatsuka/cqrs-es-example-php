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

$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true);

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

// GraphQL endpoint
$app->post('/graphql', function (Request $request, Response $response) use ($schema) {
    try {
        $body = $request->getBody()->getContents();
        $input = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON in request body');
        }

        $query = $input['query'] ?? '';
        $variableValues = $input['variables'] ?? null;

        $result = GraphQL::executeQuery($schema, $query, null, null, $variableValues);
        $output = $result->toArray();

    } catch (\Exception $e) {
        $output = [
            'error' => [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ],
        ];
    }

    $response->getBody()->write(json_encode($output, JSON_PRETTY_PRINT));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=UTF-8')
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type');
});

// CORS preflight handler
$app->options('/graphql', function (Request $request, Response $response) {
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type');
});

$app->run();
