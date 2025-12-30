<?php

declare(strict_types=1);

namespace App\Command\Domain\Events;

use App\Command\Domain\Models\GroupChatId;
use App\Command\Domain\Models\MessageId;
use App\Command\Domain\Models\UserAccountId;
use App\Infrastructure\Ulid\UlidGenerator;
use App\Infrastructure\Ulid\UlidValidator;

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
        UserAccountId $executor_id,
        UlidGenerator $generator
    ): self {
        $ulid = \App\Infrastructure\Ulid\Ulid::generate($generator);
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

    public static function fromArray(array $data, UlidValidator $validator): self
    {
        return new self(
            $data['id'],
            GroupChatId::fromArray($data['aggregate_id'], $validator),
            MessageId::fromArray($data['message_id'], $validator),
            $data['seq_nr'],
            UserAccountId::fromArray($data['executor_id'], $validator),
            $data['occurred_at']
        );
    }
}
