<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Message;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;

final readonly class GroupChatMessageEdited implements GroupChatEvent
{
    public function __construct(
        private string $id,
        private GroupChatId $aggregate_id,
        private Message $message,
        private int $seq_nr,
        private UserAccountId $executor_id,
        private int $occurred_at
    ) {
    }

    public static function create(
        GroupChatId $aggregate_id,
        Message $message,
        int $seq_nr,
        UserAccountId $executor_id
    ): self {
        $ulid = \Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\Ulid::generate();
        $id = $ulid->toString();
        $occurred_at = (int)(microtime(true) * 1000);
        return new self($id, $aggregate_id, $message, $seq_nr, $executor_id, $occurred_at);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTypeName(): string
    {
        return 'GroupChatMessageEdited';
    }

    public function getAggregateId(): string
    {
        return $this->aggregate_id->toString();
    }

    public function getSeqNr(): int
    {
        return $this->seq_nr;
    }

    public function getMessage(): Message
    {
        return $this->message;
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
            'message' => $this->message->toArray(),
            'executor_id' => $this->executor_id->toArray(),
            'seq_nr' => $this->seq_nr,
            'occurred_at' => $this->occurred_at,
        ];
    }


    /**
     * @deprecated Use GroupChatMessageEditedFactory::fromArray() instead
     */
    public static function fromArrayWithFactories(
        array $data,
        \Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatIdFactory $groupChatIdFactory,
        \Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountIdFactory $userAccountIdFactory,
        \Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageIdFactory $messageIdFactory
    ): self {
        return new self(
            $data['id'],
            $groupChatIdFactory->fromArray($data['aggregate_id']),
            Message::fromArrayWithFactories($data['message'], $userAccountIdFactory, $messageIdFactory),
            $data['seq_nr'],
            $userAccountIdFactory->fromArray($data['executor_id']),
            $data['occurred_at']
        );
    }
}
