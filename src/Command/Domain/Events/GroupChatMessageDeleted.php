<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;

final readonly class GroupChatMessageDeleted implements GroupChatEvent
{
    public function __construct(
        private string $id,
        private GroupChatId $aggregate_id,
        private MessageId $message_id,
        private int $seq_nr,
        private UserAccountId $executor_id,
        private int $occurred_at
    ) {
    }

    public static function create(
        GroupChatId $aggregate_id,
        MessageId $message_id,
        int $seq_nr,
        UserAccountId $executor_id
    ): self {
        $ulid = \Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\Ulid::generate();
        $id = $ulid->toString();
        $occurred_at = (int)(microtime(true) * 1000);
        return new self($id, $aggregate_id, $message_id, $seq_nr, $executor_id, $occurred_at);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTypeName(): string
    {
        return 'GroupChatMessageDeleted';
    }

    public function getAggregateId(): string
    {
        return $this->aggregate_id->toString();
    }

    public function getSeqNr(): int
    {
        return $this->seq_nr;
    }

    public function getMessageId(): MessageId
    {
        return $this->message_id;
    }

    public function getOccurredAt(): int
    {
        return $this->occurred_at;
    }

    public function isCreated(): bool
    {
        return false;
    }

    public function toArray(): array
    {
        return [
            'type_name' => $this->getTypeName(),
            'id' => $this->id,
            'aggregate_id' => $this->aggregate_id->toArray(),
            'message_id' => $this->message_id->toArray(),
            'executor_id' => $this->executor_id->toArray(),
            'seq_nr' => $this->seq_nr,
            'occurred_at' => $this->occurred_at,
        ];
    }

    public function applyTo(GroupChat $aggregate): GroupChat
    {
        $messages = $aggregate->getMessages();
        $message = $messages->findById($this->message_id);

        return GroupChat::fromSnapshot(
            $aggregate->getId(),
            $aggregate->getName(),
            $aggregate->getMembers(),
            $messages->remove($this->message_id, $message->getSenderId()),
            $this->seq_nr,
            $aggregate->getVersion() + 1,
            $aggregate->isDeleted()
        );
    }
}
