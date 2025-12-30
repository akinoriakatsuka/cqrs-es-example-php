<?php

declare(strict_types=1);

namespace App\Rmu\Models;

/**
 * チェックポイント値オブジェクト
 *
 * DynamoDB Streams の Shard 処理位置を表す
 */
readonly class Checkpoint
{
    /**
     * @param string $shardId Shard ID
     * @param string $sequenceNumber 最後に処理した SequenceNumber
     * @param string $streamArn Stream ARN
     */
    public function __construct(
        public string $shardId,
        public string $sequenceNumber,
        public string $streamArn
    ) {}
}
