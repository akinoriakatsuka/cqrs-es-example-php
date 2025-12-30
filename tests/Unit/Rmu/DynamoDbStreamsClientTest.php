<?php

declare(strict_types=1);

namespace Tests\Unit\Rmu;

use App\Rmu\DynamoDbStreamsClient;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDbStreams\DynamoDbStreamsClient as AwsStreamsClient;
use Aws\Result;
use PHPUnit\Framework\TestCase;

/**
 * AWS SDKのクライアントはfinalクラスまたは__call()を使用しているため、
 * 匿名クラスでスタブを作成する
 */
class DynamoDbStreamsClientTest extends TestCase
{
    /**
     * DynamoDbClientスタブを作成
     */
    private function createDynamoDbClientStub(callable $describeTableCallback): DynamoDbClient
    {
        return new class ($describeTableCallback) extends DynamoDbClient {
            public function __construct(private $callback)
            {
                // 親のコンストラクタは呼ばない
            }

            public function describeTable(array $args = [])
            {
                return ($this->callback)($args);
            }
        };
    }

    /**
     * DynamoDbStreamsClientスタブを作成
     */
    private function createStreamsClientStub(
        ?callable $describeStreamCallback = null,
        ?callable $getShardIteratorCallback = null,
        ?callable $getRecordsCallback = null
    ): AwsStreamsClient {
        return new class ($describeStreamCallback, $getShardIteratorCallback, $getRecordsCallback) extends AwsStreamsClient {
            public function __construct(
                private $describeStreamCallback,
                private $getShardIteratorCallback,
                private $getRecordsCallback
            ) {
                // 親のコンストラクタは呼ばない
            }

            public function describeStream(array $args = [])
            {
                return ($this->describeStreamCallback)($args);
            }

            public function getShardIterator(array $args = [])
            {
                return ($this->getShardIteratorCallback)($args);
            }

            public function getRecords(array $args = [])
            {
                return ($this->getRecordsCallback)($args);
            }
        };
    }

    public function test_getStreamArn_StreamARNを取得できる(): void
    {
        $expectedArn = 'arn:aws:dynamodb:ap-northeast-1:123456789012:table/journal/stream/2024-01-01T00:00:00.000';

        $dynamoDbClient = $this->createDynamoDbClientStub(function ($args) use ($expectedArn) {
            $this->assertEquals('journal', $args['TableName']);
            return new Result([
                'Table' => [
                    'LatestStreamArn' => $expectedArn,
                ],
            ]);
        });

        $streamsClient = $this->createStreamsClientStub();
        $client = new DynamoDbStreamsClient($dynamoDbClient, $streamsClient, 'journal');

        $arn = $client->getStreamArn();

        $this->assertEquals($expectedArn, $arn);
    }

    public function test_getStreamArn_StreamARNが存在しない場合はnullを返す(): void
    {
        $dynamoDbClient = $this->createDynamoDbClientStub(function ($args) {
            return new Result(['Table' => []]);
        });

        $streamsClient = $this->createStreamsClientStub();
        $client = new DynamoDbStreamsClient($dynamoDbClient, $streamsClient, 'journal');

        $arn = $client->getStreamArn();

        $this->assertNull($arn);
    }

    public function test_describeStream_Shard一覧を取得できる(): void
    {
        $streamArn = 'arn:aws:dynamodb:ap-northeast-1:123456789012:table/journal/stream/2024-01-01T00:00:00.000';

        $streamsClient = $this->createStreamsClientStub(
            describeStreamCallback: function ($args) use ($streamArn) {
                $this->assertEquals($streamArn, $args['StreamArn']);
                return new Result([
                    'StreamDescription' => [
                        'Shards' => [
                            ['ShardId' => 'shardId-000000000001'],
                            ['ShardId' => 'shardId-000000000002'],
                        ],
                    ],
                ]);
            }
        );

        $dynamoDbClient = $this->createDynamoDbClientStub(fn () => new Result([]));
        $client = new DynamoDbStreamsClient($dynamoDbClient, $streamsClient, 'journal');

        $shards = $client->describeStream($streamArn);

        $this->assertCount(2, $shards);
        $this->assertEquals('shardId-000000000001', $shards[0]['ShardId']);
        $this->assertEquals('shardId-000000000002', $shards[1]['ShardId']);
    }

    public function test_describeStream_ページネーション対応(): void
    {
        $streamArn = 'arn:aws:dynamodb:ap-northeast-1:123456789012:table/journal/stream/2024-01-01T00:00:00.000';
        $callCount = 0;

        $streamsClient = $this->createStreamsClientStub(
            describeStreamCallback: function ($args) use (&$callCount) {
                $callCount++;
                if ($callCount === 1) {
                    return new Result([
                        'StreamDescription' => [
                            'Shards' => [
                                ['ShardId' => 'shardId-000000000001'],
                            ],
                            'LastEvaluatedShardId' => 'shardId-000000000001',
                        ],
                    ]);
                }
                return new Result([
                    'StreamDescription' => [
                        'Shards' => [
                            ['ShardId' => 'shardId-000000000002'],
                        ],
                    ],
                ]);
            }
        );

        $dynamoDbClient = $this->createDynamoDbClientStub(fn () => new Result([]));
        $client = new DynamoDbStreamsClient($dynamoDbClient, $streamsClient, 'journal');

        $shards = $client->describeStream($streamArn);

        $this->assertCount(2, $shards);
        $this->assertEquals(2, $callCount);
    }

    public function test_getShardIterator_チェックポイントなしの場合はTRIM_HORIZONを使用(): void
    {
        $streamArn = 'arn:aws:dynamodb:ap-northeast-1:123456789012:table/journal/stream/2024-01-01T00:00:00.000';
        $shardId = 'shardId-000000000001';
        $expectedIterator = 'shard-iterator-value';

        $streamsClient = $this->createStreamsClientStub(
            getShardIteratorCallback: function ($args) use ($streamArn, $shardId, $expectedIterator) {
                $this->assertEquals($streamArn, $args['StreamArn']);
                $this->assertEquals($shardId, $args['ShardId']);
                $this->assertEquals('TRIM_HORIZON', $args['ShardIteratorType']);
                $this->assertArrayNotHasKey('SequenceNumber', $args);
                return new Result(['ShardIterator' => $expectedIterator]);
            }
        );

        $dynamoDbClient = $this->createDynamoDbClientStub(fn () => new Result([]));
        $client = new DynamoDbStreamsClient($dynamoDbClient, $streamsClient, 'journal');

        $iterator = $client->getShardIterator($streamArn, $shardId, null);

        $this->assertEquals($expectedIterator, $iterator);
    }

    public function test_getShardIterator_チェックポイントありの場合はAFTER_SEQUENCE_NUMBERを使用(): void
    {
        $streamArn = 'arn:aws:dynamodb:ap-northeast-1:123456789012:table/journal/stream/2024-01-01T00:00:00.000';
        $shardId = 'shardId-000000000001';
        $sequenceNumber = '49590338621307415481851246730790933368426679716659519490';
        $expectedIterator = 'shard-iterator-value';

        $streamsClient = $this->createStreamsClientStub(
            getShardIteratorCallback: function ($args) use ($streamArn, $shardId, $sequenceNumber, $expectedIterator) {
                $this->assertEquals($streamArn, $args['StreamArn']);
                $this->assertEquals($shardId, $args['ShardId']);
                $this->assertEquals('AFTER_SEQUENCE_NUMBER', $args['ShardIteratorType']);
                $this->assertEquals($sequenceNumber, $args['SequenceNumber']);
                return new Result(['ShardIterator' => $expectedIterator]);
            }
        );

        $dynamoDbClient = $this->createDynamoDbClientStub(fn () => new Result([]));
        $client = new DynamoDbStreamsClient($dynamoDbClient, $streamsClient, 'journal');

        $iterator = $client->getShardIterator($streamArn, $shardId, $sequenceNumber);

        $this->assertEquals($expectedIterator, $iterator);
    }

    public function test_getRecords_Recordsを取得できる(): void
    {
        $shardIterator = 'shard-iterator-value';
        $nextIterator = 'next-shard-iterator-value';

        $streamsClient = $this->createStreamsClientStub(
            getRecordsCallback: function ($args) use ($shardIterator, $nextIterator) {
                $this->assertEquals($shardIterator, $args['ShardIterator']);
                return new Result([
                    'Records' => [
                        ['eventID' => 'event-1'],
                        ['eventID' => 'event-2'],
                    ],
                    'NextShardIterator' => $nextIterator,
                ]);
            }
        );

        $dynamoDbClient = $this->createDynamoDbClientStub(fn () => new Result([]));
        $client = new DynamoDbStreamsClient($dynamoDbClient, $streamsClient, 'journal');

        $response = $client->getRecords($shardIterator);

        $this->assertCount(2, $response['Records']);
        $this->assertEquals('event-1', $response['Records'][0]['eventID']);
        $this->assertEquals('event-2', $response['Records'][1]['eventID']);
        $this->assertEquals($nextIterator, $response['NextShardIterator']);
    }

    public function test_getRecords_Records空の場合(): void
    {
        $shardIterator = 'shard-iterator-value';

        $streamsClient = $this->createStreamsClientStub(
            getRecordsCallback: function ($args) use ($shardIterator) {
                $this->assertEquals($shardIterator, $args['ShardIterator']);
                return new Result(['Records' => []]);
            }
        );

        $dynamoDbClient = $this->createDynamoDbClientStub(fn () => new Result([]));
        $client = new DynamoDbStreamsClient($dynamoDbClient, $streamsClient, 'journal');

        $response = $client->getRecords($shardIterator);

        $this->assertEmpty($response['Records']);
        $this->assertNull($response['NextShardIterator']);
    }
}
