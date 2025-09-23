<?php

require_once 'vendor/autoload.php';

use Aws\DynamoDb\DynamoDbClient;

$client = new DynamoDbClient([
    'region' => 'ap-northeast-1',
    'version' => 'latest',
    'endpoint' => 'http://localhost:8000',
    'credentials' => [
        'key' => 'dummy',
        'secret' => 'dummy',
    ],
]);

// Create Journal Table
try {
    $client->createTable([
        'TableName' => 'group-chat-journal',
        'KeySchema' => [
            [
                'AttributeName' => 'pkey',
                'KeyType' => 'HASH',
            ],
            [
                'AttributeName' => 'skey',
                'KeyType' => 'RANGE',
            ],
        ],
        'AttributeDefinitions' => [
            [
                'AttributeName' => 'pkey',
                'AttributeType' => 'S',
            ],
            [
                'AttributeName' => 'skey',
                'AttributeType' => 'S',
            ],
            [
                'AttributeName' => 'aid',
                'AttributeType' => 'S',
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
                    [
                        'AttributeName' => 'skey',
                        'KeyType' => 'RANGE',
                    ],
                ],
                'Projection' => [
                    'ProjectionType' => 'ALL',
                ],
                'BillingMode' => 'PAY_PER_REQUEST',
            ],
        ],
        'BillingMode' => 'PAY_PER_REQUEST',
    ]);
    echo "Journal table created successfully\n";
} catch (Exception $e) {
    echo "Journal table creation failed: " . $e->getMessage() . "\n";
}

// Create Snapshot Table
try {
    $client->createTable([
        'TableName' => 'group-chat-snapshots',
        'KeySchema' => [
            [
                'AttributeName' => 'pkey',
                'KeyType' => 'HASH',
            ],
            [
                'AttributeName' => 'skey',
                'KeyType' => 'RANGE',
            ],
        ],
        'AttributeDefinitions' => [
            [
                'AttributeName' => 'pkey',
                'AttributeType' => 'S',
            ],
            [
                'AttributeName' => 'skey',
                'AttributeType' => 'S',
            ],
            [
                'AttributeName' => 'aid',
                'AttributeType' => 'S',
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
                    [
                        'AttributeName' => 'skey',
                        'KeyType' => 'RANGE',
                    ],
                ],
                'Projection' => [
                    'ProjectionType' => 'ALL',
                ],
                'BillingMode' => 'PAY_PER_REQUEST',
            ],
        ],
        'BillingMode' => 'PAY_PER_REQUEST',
    ]);
    echo "Snapshot table created successfully\n";
} catch (Exception $e) {
    echo "Snapshot table creation failed: " . $e->getMessage() . "\n";
}

echo "DynamoDB setup complete\n";
