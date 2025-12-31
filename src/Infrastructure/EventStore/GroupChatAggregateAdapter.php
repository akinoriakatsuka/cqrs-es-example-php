<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use J5ik2o\EventStoreAdapterPhp\Aggregate;
use J5ik2o\EventStoreAdapterPhp\AggregateId;

class GroupChatAggregateAdapter implements Aggregate
{
    public function __construct(
        private GroupChat $group_chat
    ) {
    }

    public function getId(): AggregateId
    {
        return new GroupChatIdAdapter($this->group_chat->getId());
    }

    public function getSequenceNumber(): int
    {
        return $this->group_chat->getSeqNr();
    }

    public function getVersion(): int
    {
        return $this->group_chat->getVersion();
    }

    public function withVersion(int $version): Aggregate
    {
        // GroupChatは不変なので、新しいインスタンスを作成する必要があります
        // ただし、ここでは単純に同じインスタンスを返します（versionは保存時に使用されるだけ）
        return $this;
    }

    public function equals(Aggregate $other): bool
    {
        if (!($other instanceof GroupChatAggregateAdapter)) {
            return false;
        }
        return $this->group_chat->getId()->equals($other->group_chat->getId());
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->group_chat->getId()->toArray(),
            'name' => $this->group_chat->getName()->toArray(),
            'members' => $this->group_chat->getMembers()->toArray(),
            'messages' => $this->group_chat->getMessages()->toArray(),
            'seq_nr' => $this->group_chat->getSeqNr(),
            'version' => $this->group_chat->getVersion(),
            'deleted' => $this->group_chat->isDeleted(),
        ];
    }

    public function getGroupChat(): GroupChat
    {
        return $this->group_chat;
    }
}
