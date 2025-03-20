<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain\Events;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatDeleted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;

class GroupChatDeletedTest extends TestCase {
    private GroupChatDeleted $event;
    private string $id;
    private GroupChatId $aggregateId;
    private UserAccountId $executorId;
    private int $sequenceNumber;
    private DateTimeImmutable $occurredAt;

    protected function setUp(): void {
        $this->id = 'event-id-123';
        $this->aggregateId = new GroupChatId();
        $this->executorId = new UserAccountId();
        $this->sequenceNumber = 5;
        $this->occurredAt = new DateTimeImmutable();

        $this->event = new GroupChatDeleted(
            $this->id,
            $this->aggregateId,
            $this->executorId,
            $this->sequenceNumber,
            $this->occurredAt
        );
    }

    public function testGetId(): void {
        $this->assertSame($this->id, $this->event->getId());
    }

    public function testGetTypeName(): void {
        $this->assertSame('GroupChatDeleted', $this->event->getTypeName());
    }

    public function testGetAggregateId(): void {
        $this->assertSame($this->aggregateId, $this->event->getAggregateId());
    }

    public function testGetExecutorId(): void {
        $this->assertSame($this->executorId, $this->event->getExecutorId());
    }

    public function testGetSequenceNumber(): void {
        $this->assertSame($this->sequenceNumber, $this->event->getSequenceNumber());
    }

    public function testIsCreated(): void {
        $this->assertFalse($this->event->isCreated());
    }

    public function testGetOccurredAt(): void {
        $this->assertSame($this->occurredAt, $this->event->getOccurredAt());
    }

    public function testJsonSerialize(): void {
        $json = $this->event->jsonSerialize();

        $this->assertIsArray($json);
        $this->assertArrayHasKey('typeName', $json);
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('aggregateId', $json);
        $this->assertArrayHasKey('executorId', $json);
        $this->assertArrayHasKey('sequenceNumber', $json);
        $this->assertArrayHasKey('occurredAt', $json);

        $this->assertSame('GroupChatDeleted', $json['typeName']);
        $this->assertSame($this->id, $json['id']);
        $this->assertSame($this->aggregateId, $json['aggregateId']);
        $this->assertSame($this->executorId, $json['executorId']);
        $this->assertSame($this->sequenceNumber, $json['sequenceNumber']);
        $this->assertIsInt($json['occurredAt']);
    }
}
