<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Members;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Messages;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountIdFactory;
use J5ik2o\EventStoreAdapterPhp\Aggregate;

class SnapshotConverter
{
    public function __construct(
        private GroupChatIdFactory $groupChatIdFactory,
        private UserAccountIdFactory $userAccountIdFactory,
        private MemberIdFactory $memberIdFactory,
        private MessageIdFactory $messageIdFactory
    ) {
    }

    public function convert(array $data): Aggregate
    {
        $group_chat_id = $this->groupChatIdFactory->fromArray($data['id']);
        $name = GroupChatName::fromArray($data['name']);
        $members = Members::fromArrayWithFactories($data['members'], $this->userAccountIdFactory, $this->memberIdFactory);
        $messages = Messages::fromArrayWithFactories($data['messages'], $this->userAccountIdFactory, $this->messageIdFactory);
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
