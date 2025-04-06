<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use DateTimeImmutable;

readonly class GroupChatMessageEdited implements GroupChatEvent {
    private string $id;
    private GroupChatId $groupChatId;
    private MessageId $messageId;
    private string $newText;
    private UserAccountId $executorId;
    private int $sequenceNumber;
    private DateTimeImmutable $occurredAt;

    public function __construct(
        string $id,
        GroupChatId $groupChatId,
        MessageId $messageId,
        string $newText,
        UserAccountId $executorId,
        int $sequenceNumber,
        DateTimeImmutable $occurredAt
    ) {
        $this->id = $id;
        $this->groupChatId = $groupChatId;
        $this->messageId = $messageId;
        $this->newText = $newText;
        $this->executorId = $executorId;
        $this->sequenceNumber = $sequenceNumber;
        $this->occurredAt = $occurredAt;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getTypeName(): string {
        return 'group-chat-message-edited';
    }

    public function getSequenceNumber(): int {
        return $this->sequenceNumber;
    }

    public function isCreated(): bool {
        return false;
    }

    public function getOccurredAt(): DateTimeImmutable {
        return $this->occurredAt;
    }

    public function getAggregateId(): GroupChatId {
        return $this->groupChatId;
    }

    public function getMessageId(): MessageId {
        return $this->messageId;
    }

    public function getNewText(): string {
        return $this->newText;
    }

    public function getExecutorId(): UserAccountId {
        return $this->executorId;
    }

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'type' => $this->getTypeName(),
            'groupChatId' => $this->groupChatId,
            'messageId' => $this->messageId,
            'newText' => $this->newText,
            'executorId' => $this->executorId,
            'sequenceNumber' => $this->sequenceNumber,
            'occurredAt' => $this->occurredAt->format(DATE_ATOM),
        ];
    }
}
