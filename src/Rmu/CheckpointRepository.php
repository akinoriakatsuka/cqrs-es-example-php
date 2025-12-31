<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Rmu;

use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\Models\Checkpoint;
use PDO;

/**
 * チェックポイント (Shard処理位置) を管理
 */
class CheckpointRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Shard別チェックポイント読み込み
     *
     * @param string $streamArn Stream ARN
     * @param string $shardId Shard ID
     * @return Checkpoint|null チェックポイント（存在しない場合はnull）
     */
    public function loadByShard(string $streamArn, string $shardId): ?Checkpoint
    {
        $stmt = $this->pdo->prepare(
            'SELECT shard_id, sequence_number, stream_arn
             FROM rmu_checkpoint
             WHERE shard_id = :shard_id AND stream_arn = :stream_arn'
        );

        $stmt->execute([
            'shard_id' => $shardId,
            'stream_arn' => $streamArn,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return new Checkpoint(
            $row['shard_id'],
            $row['sequence_number'],
            $row['stream_arn']
        );
    }

    /**
     * チェックポイント保存
     *
     * 既存のチェックポイントがあれば更新、なければ挿入（UPSERT）
     *
     * @param Checkpoint $checkpoint
     * @return void
     */
    public function save(Checkpoint $checkpoint): void
    {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            // SQLite: REPLACE INTO を使用
            $stmt = $this->pdo->prepare(
                'REPLACE INTO rmu_checkpoint (shard_id, sequence_number, stream_arn, last_processed_at)
                 VALUES (:shard_id, :sequence_number, :stream_arn, CURRENT_TIMESTAMP)'
            );
        } else {
            // MySQL: ON DUPLICATE KEY UPDATE を使用
            $stmt = $this->pdo->prepare(
                'INSERT INTO rmu_checkpoint (shard_id, sequence_number, stream_arn, last_processed_at)
                 VALUES (:shard_id, :sequence_number, :stream_arn, CURRENT_TIMESTAMP)
                 ON DUPLICATE KEY UPDATE
                    sequence_number = VALUES(sequence_number),
                    last_processed_at = VALUES(last_processed_at)'
            );
        }

        $stmt->execute([
            'shard_id' => $checkpoint->shardId,
            'sequence_number' => $checkpoint->sequenceNumber,
            'stream_arn' => $checkpoint->streamArn,
        ]);
    }

    /**
     * Stream全体のチェックポイント取得（監視用）
     *
     * @param string $streamArn Stream ARN
     * @return array<int, array<string, mixed>> チェックポイント配列
     */
    public function loadAll(string $streamArn): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT shard_id, sequence_number, last_processed_at
             FROM rmu_checkpoint
             WHERE stream_arn = :stream_arn
             ORDER BY last_processed_at DESC'
        );

        $stmt->execute(['stream_arn' => $streamArn]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
