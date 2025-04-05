<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Message;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use DateTimeImmutable;

class GroupChatMessagePosted implements GroupChatEvent {
    private string $id;
    private GroupChatId $groupChatId;
    private Message $message;
    private UserAccountId $executorId;
    private int $sequenceNumber;
    private DateTimeImmutable $occurredAt;

    public function __construct(
        string $id,
        GroupChatId $groupChatId,
        Message $message,
        UserAccountId $executorId,
        int $sequenceNumber,
        DateTimeImmutable $occurredAt
    ) {
        $this->id = $id;
        $this->groupChatId = $groupChatId;
        $this->message = $message;
        $this->executorId = $executorId;
        $this->sequenceNumber = $sequenceNumber;
        $this->occurredAt = $occurredAt;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getTypeName(): string {
        return 'group-chat-message-posted';
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

    public function getMessage(): Message {
        return $this->message;
    }

    public function getExecutorId(): UserAccountId {
        return $this->executorId;
    }

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'type' => $this->getTypeName(),
            'groupChatId' => $this->groupChatId,
            'message' => $this->message,
            'executorId' => $this->executorId,
            'sequenceNumber' => $this->sequenceNumber,
            'occurredAt' => $this->occurredAt->format(DATE_ATOM),
        ];
    }
}
