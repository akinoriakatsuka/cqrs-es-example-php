<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain\Events;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessageEdited;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;

class GroupChatMessageEditedTest extends TestCase {
    private GroupChatMessageEdited $event;
    private string $id;
    private GroupChatId $groupChatId;
    private MessageId $messageId;
    private string $newText;
    private UserAccountId $executorId;
    private int $sequenceNumber;
    private DateTimeImmutable $occurredAt;

    protected function setUp(): void {
        $this->id = 'event-id-123';
        $this->groupChatId = new GroupChatId();
        $this->messageId = new MessageId();
        $this->newText = 'Updated message text';
        $this->executorId = new UserAccountId();
        $this->sequenceNumber = 4;
        $this->occurredAt = new DateTimeImmutable();

        $this->event = new GroupChatMessageEdited(
            $this->id,
            $this->groupChatId,
            $this->messageId,
            $this->newText,
            $this->executorId,
            $this->sequenceNumber,
            $this->occurredAt
        );
    }

    public function testGetId(): void {
        $this->assertSame($this->id, $this->event->getId());
    }

    public function testGetTypeName(): void {
        $this->assertSame('group-chat-message-edited', $this->event->getTypeName());
    }

    public function testGetAggregateId(): void {
        $this->assertSame($this->groupChatId, $this->event->getAggregateId());
    }

    public function testGetMessageId(): void {
        $this->assertSame($this->messageId, $this->event->getMessageId());
    }

    public function testGetNewText(): void {
        $this->assertSame($this->newText, $this->event->getNewText());
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
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('type', $json);
        $this->assertArrayHasKey('groupChatId', $json);
        $this->assertArrayHasKey('messageId', $json);
        $this->assertArrayHasKey('newText', $json);
        $this->assertArrayHasKey('executorId', $json);
        $this->assertArrayHasKey('sequenceNumber', $json);
        $this->assertArrayHasKey('occurredAt', $json);

        $this->assertSame($this->id, $json['id']);
        $this->assertSame('group-chat-message-edited', $json['type']);
        $this->assertSame($this->groupChatId, $json['groupChatId']);
        $this->assertSame($this->messageId, $json['messageId']);
        $this->assertSame($this->newText, $json['newText']);
        $this->assertSame($this->executorId, $json['executorId']);
        $this->assertSame($this->sequenceNumber, $json['sequenceNumber']);
    }
}
