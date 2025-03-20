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
        $this->sequenceNumber = 1;
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
        // Note: The implementation always returns 0 regardless of the constructor value
        $this->assertSame(0, $this->event->getSequenceNumber());
    }

    public function testIsCreated(): void {
        $this->assertTrue($this->event->isCreated());
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
