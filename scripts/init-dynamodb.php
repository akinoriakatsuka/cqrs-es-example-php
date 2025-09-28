<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Aws\DynamoDb\DynamoDbClient;
use Dotenv\Dotenv;

// Load environment variables
$dotenv_path = __DIR__ . '/..';
if (file_exists($dotenv_path . '/.env')) {
    $dotenv = Dotenv::createImmutable($dotenv_path);
    $dotenv->safeLoad();
}

// DynamoDB configuration
$dynamodb_endpoint = $_ENV['DYNAMODB_ENDPOINT'] ?? 'http://localhost:8000';
$dynamodb_region = $_ENV['DYNAMODB_REGION'] ?? 'us-east-1';
$dynamodb_access_key = $_ENV['DYNAMODB_ACCESS_KEY_ID'] ?? 'dummy';
$dynamodb_secret_key = $_ENV['DYNAMODB_SECRET_ACCESS_KEY'] ?? 'dummy';

$client = new DynamoDbClient([
    'version' => 'latest',
    'region' => $dynamodb_region,
    'endpoint' => $dynamodb_endpoint,
    'credentials' => [
        'key' => $dynamodb_access_key,
        'secret' => $dynamodb_secret_key,
    ],
]);

// EventStore用のテーブル定義（Goサンプルに合わせたテーブル名）
$tables = [
    [
        'TableName' => 'journal',
        'KeySchema' => [
            [
                'AttributeName' => 'aid',
                'KeyType' => 'HASH',
            ],
            [
                'AttributeName' => 'seq_nr',
                'KeyType' => 'RANGE',
            ],
        ],
        'AttributeDefinitions' => [
            [
                'AttributeName' => 'aid',
                'AttributeType' => 'S',
            ],
            [
                'AttributeName' => 'seq_nr',
                'AttributeType' => 'N',
            ],
        ],
        'GlobalSecondaryIndexes' => [
            [
                'IndexName' => 'journal-aid-index',
                'KeySchema' => [
                    [
                        'AttributeName' => 'aid',
                        'KeyType' => 'HASH',
                    ],
                ],
                'Projection' => [
                    'ProjectionType' => 'ALL',
                ],
            ],
        ],
        'BillingMode' => 'PAY_PER_REQUEST',
    ],
    [
        'TableName' => 'snapshot',
        'KeySchema' => [
            [
                'AttributeName' => 'aid',
                'KeyType' => 'HASH',
            ],
            [
                'AttributeName' => 'seq_nr',
                'KeyType' => 'RANGE',
            ],
        ],
        'AttributeDefinitions' => [
            [
                'AttributeName' => 'aid',
                'AttributeType' => 'S',
            ],
            [
                'AttributeName' => 'seq_nr',
                'AttributeType' => 'N',
            ],
        ],
        'GlobalSecondaryIndexes' => [
            [
                'IndexName' => 'snapshot-aid-index',
                'KeySchema' => [
                    [
                        'AttributeName' => 'aid',
                        'KeyType' => 'HASH',
                    ],
                ],
                'Projection' => [
                    'ProjectionType' => 'ALL',
                ],
            ],
        ],
        'BillingMode' => 'PAY_PER_REQUEST',
    ],
];

echo "DynamoDB初期化を開始します...\n";

foreach ($tables as $table) {
    $tableName = $table['TableName'];

    try {
        // テーブルが既に存在するかチェック
        $result = $client->describeTable(['TableName' => $tableName]);
        echo "テーブル '{$tableName}' は既に存在します。\n";
    } catch (\Aws\DynamoDb\Exception\DynamoDbException $e) {
        if ($e->getAwsErrorCode() === 'ResourceNotFoundException') {
            // テーブルが存在しない場合は作成
            echo "テーブル '{$tableName}' を作成中...\n";

            try {
                $client->createTable($table);
                echo "テーブル '{$tableName}' の作成が完了しました。\n";
            } catch (\Exception $createException) {
                echo "エラー: テーブル '{$tableName}' の作成に失敗しました: " . $createException->getMessage() . "\n";
                exit(1);
            }
        } else {
            echo "エラー: テーブル '{$tableName}' の確認に失敗しました: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}

echo "DynamoDB初期化が完了しました。\n";
echo "DynamoDB Admin UI: http://localhost:8003\n";
echo "DynamoDB Local endpoint: {$dynamodb_endpoint}\n";
