<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Processor;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatEvent;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\Repository\GroupChatRepository;

class GroupChatCommandProcessor {
    public GroupChatRepository $repository;
    public function __construct(GroupChatRepository $repository) {
        $this->repository = $repository;
    }

    public function createGroupChat(
        GroupChatName $groupChatName,
        UserAccountId $executorId
    ): GroupChatEvent {
        $groupChatWithEvent = GroupChat::create($groupChatName, $executorId);
        $this->repository->storeEventAndSnapshot($groupChatWithEvent->getEvent(), $groupChatWithEvent->getGroupChat());
        return $groupChatWithEvent->getEvent();
    }
}
