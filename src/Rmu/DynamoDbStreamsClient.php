<?php

declare(strict_types=1);

namespace App\Rmu;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDbStreams\DynamoDbStreamsClient as AwsStreamsClient;

/**
 * DynamoDB Streamsクライアントラッパー
 *
 * Go版のlocalRmu.goのstreamDriver関数に相当
 */
class DynamoDbStreamsClient
{
    public function __construct(
        private DynamoDbClient $dynamoDbClient,
        private AwsStreamsClient $streamsClient,
        private string $tableName
    ) {}

    /**
     * テーブルのStream ARNを取得
     *
     * @return string|null Stream ARN（Streamが有効でない場合はnull）
     */
    public function getStreamArn(): ?string
    {
        $result = $this->dynamoDbClient->describeTable([
            'TableName' => $this->tableName,
        ]);

        return $result['Table']['LatestStreamArn'] ?? null;
    }

    /**
     * Streamの全Shard一覧を取得
     *
     * ページネーションに対応し、全てのShardを取得する
     *
     * @param string $streamArn Stream ARN
     * @return array<int, array<string, mixed>> Shard配列
     */
    public function describeStream(string $streamArn): array
    {
        $shards = [];
        $exclusiveStartShardId = null;

        do {
            $params = ['StreamArn' => $streamArn];

            if ($exclusiveStartShardId !== null) {
                $params['ExclusiveStartShardId'] = $exclusiveStartShardId;
            }

            $result = $this->streamsClient->describeStream($params);

            $description = $result['StreamDescription'];
            $shards = array_merge($shards, $description['Shards'] ?? []);

            $exclusiveStartShardId = $description['LastEvaluatedShardId'] ?? null;

        } while ($exclusiveStartShardId !== null);

        return $shards;
    }

    /**
     * ShardIteratorを取得
     *
     * チェックポイント（SequenceNumber）がある場合は AFTER_SEQUENCE_NUMBER を使用し、
     * ない場合は TRIM_HORIZON（最古から）を使用する
     *
     * @param string $streamArn Stream ARN
     * @param string $shardId Shard ID
     * @param string|null $sequenceNumber チェックポイント（nullの場合はTRIM_HORIZON）
     * @return string|null ShardIterator
     */
    public function getShardIterator(
        string $streamArn,
        string $shardId,
        ?string $sequenceNumber = null
    ): ?string {
        $params = [
            'StreamArn' => $streamArn,
            'ShardId' => $shardId,
        ];

        if ($sequenceNumber !== null) {
            // チェックポイントがある場合は、その次から取得
            $params['ShardIteratorType'] = 'AFTER_SEQUENCE_NUMBER';
            $params['SequenceNumber'] = $sequenceNumber;
        } else {
            // 初回実行時は最古から取得
            $params['ShardIteratorType'] = 'TRIM_HORIZON';
        }

        $result = $this->streamsClient->getShardIterator($params);

        return $result['ShardIterator'] ?? null;
    }

    /**
     * Recordsを取得
     *
     * @param string $shardIterator ShardIterator
     * @return array{Records: array, NextShardIterator: string|null} Records配列とNextShardIterator
     */
    public function getRecords(string $shardIterator): array
    {
        $result = $this->streamsClient->getRecords([
            'ShardIterator' => $shardIterator,
        ]);

        return [
            'Records' => $result['Records'] ?? [],
            'NextShardIterator' => $result['NextShardIterator'] ?? null,
        ];
    }
}
