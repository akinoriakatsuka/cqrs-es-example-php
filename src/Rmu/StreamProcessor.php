<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Rmu;

use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\EventHandlers\GroupChatCreatedEventHandler;
use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\EventHandlers\GroupChatDeletedEventHandler;
use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\EventHandlers\GroupChatMemberAddedEventHandler;
use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\EventHandlers\GroupChatMemberRemovedEventHandler;
use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\EventHandlers\GroupChatMessageDeletedEventHandler;
use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\EventHandlers\GroupChatMessageEditedEventHandler;
use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\EventHandlers\GroupChatMessagePostedEventHandler;
use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\EventHandlers\GroupChatRenamedEventHandler;
use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\Models\Checkpoint;
use Exception;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * DynamoDB Streamsからイベントを読み取り、Read Modelを更新
 */
final readonly class StreamProcessor
{
    private const int POLL_INTERVAL_MS = 100; // ポーリング間隔(ミリ秒)

    public function __construct(
        private DynamoDbStreamsClient $streamsClient,
        private CheckpointRepository $checkpointRepo,
        private GroupChatCreatedEventHandler $groupChatCreatedHandler,
        private GroupChatRenamedEventHandler $groupChatRenamedHandler,
        private GroupChatDeletedEventHandler $groupChatDeletedHandler,
        private GroupChatMemberAddedEventHandler $groupChatMemberAddedHandler,
        private GroupChatMemberRemovedEventHandler $groupChatMemberRemovedHandler,
        private GroupChatMessagePostedEventHandler $groupChatMessagePostedHandler,
        private GroupChatMessageEditedEventHandler $groupChatMessageEditedHandler,
        private GroupChatMessageDeletedEventHandler $groupChatMessageDeletedHandler,
        private LoggerInterface $logger
    ) {
    }

    /**
     * 無限ループでStreamsを処理
     */
    public function run(): void
    {
        $this->logger->info('StreamProcessor: starting...');

        // Stream ARNを取得
        $streamArn = $this->streamsClient->getStreamArn();
        if ($streamArn === null) {
            $this->logger->error('StreamProcessor: Stream ARN not found');
            throw new RuntimeException('Stream ARN not found');
        }

        $this->logger->info('StreamProcessor: Stream ARN found', ['arn' => $streamArn]);

        // Shardsを取得
        $shards = $this->streamsClient->describeStream($streamArn);
        $this->logger->info('StreamProcessor: Shards found', ['count' => count($shards)]);

        // 各Shardを処理
        $shardIterators = [];
        foreach ($shards as $shard) {
            $shardId = $shard['ShardId'];

            // チェックポイントを読み込み
            $checkpoint = $this->checkpointRepo->loadByShard($streamArn, $shardId);
            $sequenceNumber = $checkpoint?->sequenceNumber;

            // ShardIteratorを取得
            $iterator = $this->streamsClient->getShardIterator($streamArn, $shardId, $sequenceNumber);
            if ($iterator !== null) {
                $shardIterators[$shardId] = $iterator;
                $this->logger->info('StreamProcessor: ShardIterator obtained', [
                    'shardId' => $shardId,
                    'checkpoint' => $sequenceNumber ?? 'none',
                ]);
            }
        }

        // 無限ループでレコードを処理
        // @phpstan-ignore-next-line
        while (true) {
            foreach ($shardIterators as $shardId => $iterator) {
                if ($iterator === null) {
                    continue;
                }

                // レコードを取得
                $response = $this->streamsClient->getRecords($iterator);
                $records = $response['Records'];
                $nextIterator = $response['NextShardIterator'];

                // レコードを処理
                if (count($records) > 0) {
                    $this->logger->info('StreamProcessor: Records received', [
                        'shardId' => $shardId,
                        'count' => count($records),
                    ]);

                    foreach ($records as $record) {
                        $this->processRecord($record, $streamArn, $shardId);
                    }
                }

                // イテレータを更新
                $shardIterators[$shardId] = $nextIterator;
            }

            // ポーリング間隔を設定
            usleep(self::POLL_INTERVAL_MS * 1000);
        }
    }

    /**
     * 1レコードを処理
     */
    private function processRecord(array $record, string $streamArn, string $shardId): void
    {
        $eventName = $record['eventName'];
        $this->logger->debug('StreamProcessor: Processing record', [
            'eventID' => $record['eventID'],
            'eventName' => $eventName,
        ]);

        // INSERTイベントのみ処理
        if ($eventName !== 'INSERT') {
            return;
        }

        // NewImageからpayloadを取得
        $newImage = $record['dynamodb']['NewImage'];
        if (!isset($newImage['payload'])) {
            $this->logger->warning('StreamProcessor: payload not found in record');
            return;
        }

        // payloadはBinary (B) またはString (S) 型で来る
        $payloadData = $newImage['payload'];

        $this->logger->debug('StreamProcessor: Payload Data Type', [
            'has_B' => isset($payloadData['B']),
            'has_S' => isset($payloadData['S']),
            'B_type' => isset($payloadData['B']) ? gettype($payloadData['B']) : 'N/A',
            'B_class' => isset($payloadData['B']) && is_object($payloadData['B']) ? get_class($payloadData['B']) : 'N/A',
            'B_sample' => isset($payloadData['B']) ? (is_string($payloadData['B']) ? substr($payloadData['B'], 0, 50) : 'not-string') : 'N/A',
        ]);

        if (isset($payloadData['B'])) {
            // Binary型の場合
            // AWS PHP SDKは既にBase64デコード済みの値を返すので、そのまま使用
            $payloadJson = (string)$payloadData['B'];
        } elseif (isset($payloadData['S'])) {
            // String型の場合はそのまま
            $payloadJson = $payloadData['S'];
        } else {
            $this->logger->warning('StreamProcessor: payload type not supported', ['payload' => $payloadData]);
            return;
        }

        $this->logger->debug('StreamProcessor: Payload JSON', [
            'payloadJson' => substr($payloadJson, 0, 200),
            'length' => strlen($payloadJson),
        ]);

        $payload = json_decode($payloadJson, true);

        if ($payload === null) {
            $this->logger->error('StreamProcessor: Failed to decode payload JSON', [
                'error' => json_last_error_msg(),
                'payloadSample' => substr($payloadJson, 0, 100),
            ]);
            return;
        }

        // type_nameを確認
        $typeName = $payload['type_name'] ?? null;
        if ($typeName === null) {
            $this->logger->warning('StreamProcessor: type_name not found in payload');
            return;
        }

        // イベントを処理
        try {
            $this->handleEvent($typeName, $payload);
        } catch (Exception $e) {
            $this->logger->error('StreamProcessor: Failed to handle event', [
                'type' => $typeName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        // チェックポイントを保存
        $sequenceNumber = $record['dynamodb']['SequenceNumber'];
        $checkpoint = new Checkpoint($shardId, $sequenceNumber, $streamArn);
        $this->checkpointRepo->save($checkpoint);

        $this->logger->debug('StreamProcessor: Checkpoint saved', [
            'shardId' => $shardId,
            'sequenceNumber' => $sequenceNumber,
        ]);
    }

    /**
     * イベントタイプに応じて処理
     */
    private function handleEvent(string $typeName, array $payload): void
    {
        match ($typeName) {
            'GroupChatCreated' => $this->groupChatCreatedHandler->handle($payload),
            'GroupChatRenamed' => $this->groupChatRenamedHandler->handle($payload),
            'GroupChatDeleted' => $this->groupChatDeletedHandler->handle($payload),
            'GroupChatMemberAdded' => $this->groupChatMemberAddedHandler->handle($payload),
            'GroupChatMemberRemoved' => $this->groupChatMemberRemovedHandler->handle($payload),
            'GroupChatMessagePosted' => $this->groupChatMessagePostedHandler->handle($payload),
            'GroupChatMessageEdited' => $this->groupChatMessageEditedHandler->handle($payload),
            'GroupChatMessageDeleted' => $this->groupChatMessageDeletedHandler->handle($payload),
            default => $this->logger->debug('StreamProcessor: Unhandled event type', ['type' => $typeName])
        };
    }
}
