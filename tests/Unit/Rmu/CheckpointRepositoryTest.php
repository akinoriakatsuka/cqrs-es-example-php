<?php

declare(strict_types=1);

namespace Tests\Unit\Rmu;

use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\CheckpointRepository;
use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\Models\Checkpoint;
use PDO;
use PHPUnit\Framework\TestCase;

class CheckpointRepositoryTest extends TestCase
{
    private PDO $pdo;
    private CheckpointRepository $repository;

    protected function setUp(): void
    {
        // インメモリSQLiteを使用
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // テーブル作成
        $this->pdo->exec('
            CREATE TABLE rmu_checkpoint (
                shard_id TEXT PRIMARY KEY,
                sequence_number TEXT NOT NULL,
                stream_arn TEXT NOT NULL,
                last_processed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
        ');

        $this->repository = new CheckpointRepository($this->pdo);
    }

    protected function tearDown(): void
    {
        // SQLiteの接続を明示的に閉じる（不要だが念のため）
        unset($this->pdo);
    }

    public function test_loadByShard_初期状態ではnullを返す(): void
    {
        $checkpoint = $this->repository->loadByShard(
            'arn:aws:dynamodb:ap-northeast-1:123456789012:table/journal/stream/2024-01-01T00:00:00.000',
            'shardId-000000000001'
        );

        $this->assertNull($checkpoint);
    }

    public function test_loadByShard_既存データがある場合は取得できる(): void
    {
        // テストデータ挿入
        $shardId = 'shardId-000000000001';
        $sequenceNumber = '49590338621307415481851246730790933368426679716659519490';
        $streamArn = 'arn:aws:dynamodb:ap-northeast-1:123456789012:table/journal/stream/2024-01-01T00:00:00.000';

        $this->pdo->prepare('
            INSERT INTO rmu_checkpoint (shard_id, sequence_number, stream_arn)
            VALUES (:shard_id, :sequence_number, :stream_arn)
        ')->execute([
            'shard_id' => $shardId,
            'sequence_number' => $sequenceNumber,
            'stream_arn' => $streamArn,
        ]);

        // 取得
        $checkpoint = $this->repository->loadByShard($streamArn, $shardId);

        $this->assertInstanceOf(Checkpoint::class, $checkpoint);
        $this->assertEquals($shardId, $checkpoint->shardId);
        $this->assertEquals($sequenceNumber, $checkpoint->sequenceNumber);
        $this->assertEquals($streamArn, $checkpoint->streamArn);
    }

    public function test_save_新規チェックポイントを保存できる(): void
    {
        $checkpoint = new Checkpoint(
            'shardId-000000000001',
            '49590338621307415481851246730790933368426679716659519490',
            'arn:aws:dynamodb:ap-northeast-1:123456789012:table/journal/stream/2024-01-01T00:00:00.000'
        );

        $this->repository->save($checkpoint);

        // データベースから確認
        $stmt = $this->pdo->prepare('SELECT * FROM rmu_checkpoint WHERE shard_id = :shard_id');
        $stmt->execute(['shard_id' => $checkpoint->shardId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($row);
        $this->assertEquals($checkpoint->shardId, $row['shard_id']);
        $this->assertEquals($checkpoint->sequenceNumber, $row['sequence_number']);
        $this->assertEquals($checkpoint->streamArn, $row['stream_arn']);
    }

    public function test_save_既存チェックポイントを更新できる(): void
    {
        $shardId = 'shardId-000000000001';
        $streamArn = 'arn:aws:dynamodb:ap-northeast-1:123456789012:table/journal/stream/2024-01-01T00:00:00.000';

        // 初期データ
        $checkpoint1 = new Checkpoint($shardId, 'old-sequence-number', $streamArn);
        $this->repository->save($checkpoint1);

        // 更新
        $checkpoint2 = new Checkpoint($shardId, 'new-sequence-number', $streamArn);
        $this->repository->save($checkpoint2);

        // データベースから確認
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM rmu_checkpoint');
        $count = $stmt->fetchColumn();

        $this->assertEquals(1, $count); // レコードは1件のまま

        $loaded = $this->repository->loadByShard($streamArn, $shardId);
        $this->assertEquals('new-sequence-number', $loaded->sequenceNumber);
    }

    public function test_loadAll_Stream全体のチェックポイントを取得できる(): void
    {
        $streamArn = 'arn:aws:dynamodb:ap-northeast-1:123456789012:table/journal/stream/2024-01-01T00:00:00.000';

        // 複数のShardのチェックポイントを保存
        $this->repository->save(new Checkpoint('shardId-000000000001', 'seq1', $streamArn));
        $this->repository->save(new Checkpoint('shardId-000000000002', 'seq2', $streamArn));
        $this->repository->save(new Checkpoint('shardId-000000000003', 'seq3', $streamArn));

        // 別のStreamのチェックポイント
        $this->repository->save(new Checkpoint('shardId-000000000004', 'seq4', 'other-stream-arn'));

        // 取得
        $checkpoints = $this->repository->loadAll($streamArn);

        $this->assertCount(3, $checkpoints);
        $this->assertEquals('shardId-000000000001', $checkpoints[0]['shard_id']);
        $this->assertEquals('shardId-000000000002', $checkpoints[1]['shard_id']);
        $this->assertEquals('shardId-000000000003', $checkpoints[2]['shard_id']);
    }

    public function test_loadAll_空の配列を返す(): void
    {
        $checkpoints = $this->repository->loadAll('non-existent-stream-arn');

        $this->assertEmpty($checkpoints);
    }
}
