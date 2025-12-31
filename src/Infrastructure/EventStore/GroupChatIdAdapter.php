<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use J5ik2o\EventStoreAdapterPhp\AggregateId;

class GroupChatIdAdapter implements AggregateId
{
    public function __construct(
        private GroupChatId $group_chat_id
    ) {
    }

    public function getTypeName(): string
    {
        return 'GroupChat';
    }

    public function getValue(): string
    {
        return $this->group_chat_id->toString();
    }

    public function asString(): string
    {
        return 'GroupChat-' . $this->group_chat_id->toString();
    }

    public function equals(AggregateId $other): bool
    {
        return $this->asString() === $other->asString();
    }

    public function jsonSerialize(): mixed
    {
        return $this->group_chat_id->toArray();
    }

    public function getGroupChatId(): GroupChatId
    {
        return $this->group_chat_id;
    }
}
