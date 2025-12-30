<?php

declare(strict_types=1);

namespace Tests\Unit\Rmu\Models;

use App\Rmu\Models\Checkpoint;
use PHPUnit\Framework\TestCase;

class CheckpointTest extends TestCase
{
    public function test_インスタンス生成(): void
    {
        $shardId = 'shardId-000000000001';
        $sequenceNumber = '49590338621307415481851246730790933368426679716659519490';
        $streamArn = 'arn:aws:dynamodb:ap-northeast-1:123456789012:table/journal/stream/2024-01-01T00:00:00.000';

        $checkpoint = new Checkpoint($shardId, $sequenceNumber, $streamArn);

        $this->assertInstanceOf(Checkpoint::class, $checkpoint);
        $this->assertEquals($shardId, $checkpoint->shardId);
        $this->assertEquals($sequenceNumber, $checkpoint->sequenceNumber);
        $this->assertEquals($streamArn, $checkpoint->streamArn);
    }

    public function test_readonlyプロパティで変更できない(): void
    {
        $checkpoint = new Checkpoint(
            'shardId-000000000001',
            '49590338621307415481851246730790933368426679716659519490',
            'arn:aws:dynamodb:ap-northeast-1:123456789012:table/journal/stream/2024-01-01T00:00:00.000'
        );

        // readonlyプロパティなので、代入しようとするとエラーになる
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot modify readonly property');

        $checkpoint->shardId = 'new-shard-id';
    }

    public function test_等価性判定(): void
    {
        $checkpoint1 = new Checkpoint(
            'shardId-000000000001',
            '49590338621307415481851246730790933368426679716659519490',
            'arn:aws:dynamodb:ap-northeast-1:123456789012:table/journal/stream/2024-01-01T00:00:00.000'
        );

        $checkpoint2 = new Checkpoint(
            'shardId-000000000001',
            '49590338621307415481851246730790933368426679716659519490',
            'arn:aws:dynamodb:ap-northeast-1:123456789012:table/journal/stream/2024-01-01T00:00:00.000'
        );

        $checkpoint3 = new Checkpoint(
            'shardId-000000000002',
            '49590338621307415481851246730790933368426679716659519490',
            'arn:aws:dynamodb:ap-northeast-1:123456789012:table/journal/stream/2024-01-01T00:00:00.000'
        );

        // PHP8.1+ではreadonly classは自動的に値の等価性を持つ
        $this->assertEquals($checkpoint1->shardId, $checkpoint2->shardId);
        $this->assertEquals($checkpoint1->sequenceNumber, $checkpoint2->sequenceNumber);
        $this->assertEquals($checkpoint1->streamArn, $checkpoint2->streamArn);

        $this->assertNotEquals($checkpoint1->shardId, $checkpoint3->shardId);
    }

    public function test_空文字列での生成(): void
    {
        // 空文字列でも生成可能（初期状態の場合）
        $checkpoint = new Checkpoint('', '', '');

        $this->assertEquals('', $checkpoint->shardId);
        $this->assertEquals('', $checkpoint->sequenceNumber);
        $this->assertEquals('', $checkpoint->streamArn);
    }
}
