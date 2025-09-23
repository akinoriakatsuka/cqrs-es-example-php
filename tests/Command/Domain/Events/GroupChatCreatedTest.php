<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain\Events;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatCreated;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;

class GroupChatCreatedTest extends TestCase {
    private GroupChatCreated $event;
    private string $id;
    private GroupChatId $aggregateId;
    private GroupChatName $name;
    private int $sequenceNumber;
    private DateTimeImmutable $occurredAt;

    protected function setUp(): void {
        $this->id = 'event-id-123';
        $this->aggregateId = new GroupChatId();
        $this->name = new GroupChatName('Test Group Chat');
        $this->sequenceNumber = 5;
        $this->occurredAt = new DateTimeImmutable();

        $this->event = new GroupChatCreated(
            $this->id,
            $this->aggregateId,
            $this->name,
            $this->sequenceNumber,
            $this->occurredAt
        );
    }

    public function testGetId(): void {
        $this->assertSame($this->id, $this->event->getId());
    }

    public function testGetTypeName(): void {
        $this->assertSame('GroupChatCreated', $this->event->getTypeName());
    }

    public function testGetAggregateId(): void {
        $this->assertSame($this->aggregateId, $this->event->getAggregateId());
    }

    public function testGetName(): void {
        $this->assertSame($this->name, $this->event->getName());
    }

    public function testGetSequenceNumber(): void {
        // Should return the sequence number passed to constructor
        $this->assertSame($this->sequenceNumber, $this->event->getSequenceNumber());
    }

    public function testGetSequenceNumberWithDifferentValue(): void {
        $sequenceNumber = 5;
        $event = new GroupChatCreated(
            'test-id',
            new GroupChatId(),
            new GroupChatName('Test'),
            $sequenceNumber,
            new DateTimeImmutable()
        );

        $this->assertEquals($sequenceNumber, $event->getSequenceNumber());
    }

    public function testIsCreated(): void {
        $this->assertTrue($this->event->isCreated());
    }

    public function testGetOccurredAt(): void {
        // Should return the occurredAt passed to constructor
        $this->assertSame($this->occurredAt, $this->event->getOccurredAt());
    }

    public function testGetOccurredAtWithDifferentValue(): void {
        $occurredAt = new DateTimeImmutable('2023-01-01 12:00:00');
        $event = new GroupChatCreated(
            'test-id',
            new GroupChatId(),
            new GroupChatName('Test'),
            1,
            $occurredAt
        );
        $this->assertEquals($occurredAt, $event->getOccurredAt());
    }

    public function testJsonSerialize(): void {
        $json = $this->event->jsonSerialize();

        $this->assertIsArray($json);
        $this->assertArrayHasKey('typeName', $json);
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('aggregateId', $json);
        $this->assertArrayHasKey('sequenceNumber', $json);
        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('occurredAt', $json);

        $this->assertSame('GroupChatCreated', $json['typeName']);
        $this->assertSame($this->id, $json['id']);
        $this->assertSame($this->aggregateId, $json['aggregateId']);
        $this->assertSame($this->sequenceNumber, $json['sequenceNumber']);
        $this->assertSame($this->name, $json['name']);
        $this->assertIsInt($json['occurredAt']);
    }
}
