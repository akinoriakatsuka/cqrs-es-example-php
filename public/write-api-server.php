<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Schema;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Processor\GroupChatCommandProcessor;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\Repository\GroupChatRepository;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore\DynamoDBEventStore;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore\EventSerializer;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore\EventConverter;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore\GroupChatEventAdapter;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore\SnapshotSerializer;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore\SnapshotConverter;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidValidator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use Aws\DynamoDb\DynamoDbClient;
use J5ik2o\EventStoreAdapterPhp\EventStoreFactory;
use GraphQL\GraphQL;
use GraphQL\Error\DebugFlag;

// 環境変数を取得
$aws_region = getenv('AWS_REGION') ?: 'ap-northeast-1';
$dynamodb_endpoint_url = getenv('AWS_DYNAMODB_ENDPOINT_URL') ?: '';
$dynamodb_access_key_id = getenv('AWS_DYNAMODB_ACCESS_KEY_ID') ?: '';
$dynamodb_secret_key = getenv('AWS_DYNAMODB_SECRET_ACCESS_KEY') ?: '';
$journal_table_name = getenv('PERSISTENCE_JOURNAL_TABLE_NAME') ?: 'journal';
$snapshot_table_name = getenv('PERSISTENCE_SNAPSHOT_TABLE_NAME') ?: 'snapshot';
$journal_aid_index_name = getenv('PERSISTENCE_JOURNAL_AID_INDEX_NAME') ?: 'journal-aid-index';
$snapshot_aid_index_name = getenv('PERSISTENCE_SNAPSHOT_AID_INDEX_NAME') ?: 'snapshot-aid-index';
$shard_count = (int)(getenv('PERSISTENCE_SHARD_COUNT') ?: 10);

// DynamoDBクライアントの初期化
$dynamodb_config = [
    'region' => $aws_region,
    'version' => 'latest',
];

if ($dynamodb_endpoint_url !== '') {
    $dynamodb_config['endpoint'] = $dynamodb_endpoint_url;
}

if ($dynamodb_access_key_id !== '' && $dynamodb_secret_key !== '') {
    $dynamodb_config['credentials'] = [
        'key' => $dynamodb_access_key_id,
        'secret' => $dynamodb_secret_key,
    ];
}

$dynamodb_client = new DynamoDbClient($dynamodb_config);

// DIコンテナ（簡易版）
$validator = new RobinvdvleutenUlidValidator();
$generator = new RobinvdvleutenUlidGenerator();

// EventStoreの初期化
$event_serializer = new EventSerializer();
$event_converter = new EventConverter($validator);
$snapshot_serializer = new SnapshotSerializer();
$snapshot_converter = new SnapshotConverter($validator);

// EventConverter/SnapshotConverterをcallableに変換
$event_converter_callable = function(array $data) use ($event_converter, $validator) {
    return new GroupChatEventAdapter(
        $event_converter->convert($data),
        $validator
    );
};

$snapshot_converter_callable = function(array $data) use ($snapshot_converter) {
    return $snapshot_converter->convert($data);
};

$j5_event_store = EventStoreFactory::create(
    $dynamodb_client,
    $journal_table_name,
    $snapshot_table_name,
    $journal_aid_index_name,
    $snapshot_aid_index_name,
    $shard_count,
    $event_converter_callable,
    $snapshot_converter_callable
);

// EventSerializerとSnapshotSerializerを設定
$j5_event_store = $j5_event_store
    ->withEventSerializer($event_serializer)
    ->withSnapshotSerializer($snapshot_serializer);

$event_store = new DynamoDBEventStore($j5_event_store, $validator);
$repository = new GroupChatRepository($event_store);
$processor = new GroupChatCommandProcessor($repository, $validator, $generator);

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
    $schema = Schema::build($processor);

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

// Root endpoint
if ($request_uri === '/' && $request_method === 'GET') {
    echo json_encode([
        'service' => 'write-api-server',
        'endpoints' => [
            'health' => '/health',
            'graphql' => '/query'
        ]
    ]);
    exit;
}

// Not found
http_response_code(404);
echo json_encode(['error' => 'Not found']);
