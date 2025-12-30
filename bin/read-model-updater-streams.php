#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Rmu\CheckpointRepository;
use App\Rmu\DynamoDbStreamsClient;
use App\Rmu\EventHandlers\GroupChatCreatedEventHandler;
use App\Rmu\EventHandlers\GroupChatRenamedEventHandler;
use App\Rmu\EventHandlers\GroupChatDeletedEventHandler;
use App\Rmu\EventHandlers\GroupChatMemberAddedEventHandler;
use App\Rmu\EventHandlers\GroupChatMemberRemovedEventHandler;
use App\Rmu\EventHandlers\GroupChatMessagePostedEventHandler;
use App\Rmu\EventHandlers\GroupChatMessageEditedEventHandler;
use App\Rmu\EventHandlers\GroupChatMessageDeletedEventHandler;
use App\Rmu\GroupChatDaoImpl;
use App\Rmu\StreamProcessor;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDbStreams\DynamoDbStreamsClient as AwsStreamsClient;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

// ロガー初期化
$logger = new Logger('rmu');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$logger->info('PHP Read Model Updater (DynamoDB Streams) starting...');

// 環境変数から設定を取得
$awsRegion = getenv('AWS_REGION') ?: 'ap-northeast-1';
$dynamoDbEndpoint = getenv('AWS_DYNAMODB_ENDPOINT_URL') ?: null;
$accessKeyId = getenv('AWS_DYNAMODB_ACCESS_KEY_ID') ?: 'x';
$secretAccessKey = getenv('AWS_DYNAMODB_SECRET_ACCESS_KEY') ?: 'x';
$journalTableName = getenv('PERSISTENCE_JOURNAL_TABLE_NAME') ?: 'journal';

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbDatabase = getenv('DB_DATABASE') ?: 'ceer';
$dbUsername = getenv('DB_USERNAME') ?: 'root';
$dbPassword = getenv('DB_PASSWORD') ?: 'passwd';

// DynamoDB Clientsを初期化
$dynamoDbClientConfig = [
    'version' => 'latest',
    'region' => $awsRegion,
    'credentials' => [
        'key' => $accessKeyId,
        'secret' => $secretAccessKey,
    ],
];

if ($dynamoDbEndpoint !== null) {
    $dynamoDbClientConfig['endpoint'] = $dynamoDbEndpoint;
}

$dynamoDbClient = new DynamoDbClient($dynamoDbClientConfig);
$streamsClient = new AwsStreamsClient($dynamoDbClientConfig);

// MySQL PDO接続
$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbDatabase};charset=utf8mb4";
$pdo = new PDO($dsn, $dbUsername, $dbPassword, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// 依存関係を組み立て
$dynamoDbStreamsClient = new DynamoDbStreamsClient($dynamoDbClient, $streamsClient, $journalTableName);
$checkpointRepo = new CheckpointRepository($pdo);
$groupChatDao = new GroupChatDaoImpl($pdo);

// イベントハンドラー
$groupChatCreatedHandler = new GroupChatCreatedEventHandler($groupChatDao, $logger);
$groupChatRenamedHandler = new GroupChatRenamedEventHandler($groupChatDao, $logger);
$groupChatDeletedHandler = new GroupChatDeletedEventHandler($groupChatDao, $logger);
$groupChatMemberAddedHandler = new GroupChatMemberAddedEventHandler($groupChatDao, $logger);
$groupChatMemberRemovedHandler = new GroupChatMemberRemovedEventHandler($groupChatDao, $logger);
$groupChatMessagePostedHandler = new GroupChatMessagePostedEventHandler($groupChatDao, $logger);
$groupChatMessageEditedHandler = new GroupChatMessageEditedEventHandler($groupChatDao, $logger);
$groupChatMessageDeletedHandler = new GroupChatMessageDeletedEventHandler($groupChatDao, $logger);

// StreamProcessor初期化
$processor = new StreamProcessor(
    $dynamoDbStreamsClient,
    $checkpointRepo,
    $groupChatCreatedHandler,
    $groupChatRenamedHandler,
    $groupChatDeletedHandler,
    $groupChatMemberAddedHandler,
    $groupChatMemberRemovedHandler,
    $groupChatMessagePostedHandler,
    $groupChatMessageEditedHandler,
    $groupChatMessageDeletedHandler,
    $logger
);

// 処理開始
try {
    $processor->run();
} catch (Exception $e) {
    $logger->error('Fatal error', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    exit(1);
}
