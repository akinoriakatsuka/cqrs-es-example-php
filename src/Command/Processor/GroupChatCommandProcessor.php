<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Processor;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatEvent;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
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

    /**
     * Delete a group chat
     *
     * @param GroupChatId $groupChatId
     * @param UserAccountId $executorId
     * @return GroupChatEvent
     * @throws \RuntimeException If group chat not found
     */
    public function deleteGroupChat(
        GroupChatId $groupChatId,
        UserAccountId $executorId
    ): GroupChatEvent {
        $groupChat = $this->repository->findById($groupChatId);
        if ($groupChat === null) {
            throw new \RuntimeException("Group chat not found");
        }

        $groupChatWithEvent = $groupChat->delete($executorId);
        $this->repository->storeEventAndSnapshot($groupChatWithEvent->getEvent(), $groupChatWithEvent->getGroupChat());

        return $groupChatWithEvent->getEvent();
    }
}
