<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Members;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Messages;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidValidator;
use J5ik2o\EventStoreAdapterPhp\Aggregate;

class SnapshotConverter
{
    public function __construct(
        private UlidValidator $validator
    ) {
    }

    public function convert(array $data): Aggregate
    {
        $group_chat_id = GroupChatId::fromArray($data['id'], $this->validator);
        $name = GroupChatName::fromArray($data['name']);
        $members = Members::fromArray($data['members'], $this->validator);
        $messages = Messages::fromArray($data['messages'], $this->validator);
        $seq_nr = (int)$data['seq_nr'];
        $version = (int)$data['version'];
        $deleted = (bool)$data['deleted'];

        // GroupChatを再構築（NewGroupChatFromに相当）
        $group_chat = GroupChat::fromSnapshot(
            $group_chat_id,
            $name,
            $members,
            $messages,
            $seq_nr,
            $version,
            $deleted
        );

        return new GroupChatAggregateAdapter($group_chat);
    }
}
