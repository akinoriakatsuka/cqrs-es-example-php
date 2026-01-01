<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory\GroupChatIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory\MembersFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory\MessagesFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use J5ik2o\EventStoreAdapterPhp\Aggregate;

class SnapshotConverter
{
    public function __construct(
        private GroupChatIdFactory $groupChatIdFactory,
        private MembersFactory $membersFactory,
        private MessagesFactory $messagesFactory
    ) {
    }

    public function convert(array $data): Aggregate
    {
        $group_chat_id = $this->groupChatIdFactory->fromArray($data['id']);
        $name = GroupChatName::fromArray($data['name']);
        $members = $this->membersFactory->fromArray($data['members']);
        $messages = $this->messagesFactory->fromArray($data['messages']);
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
