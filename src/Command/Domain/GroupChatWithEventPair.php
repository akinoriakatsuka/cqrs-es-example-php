<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatEvent;

readonly class GroupChatWithEventPair {
    private GroupChat $groupChat;
    private GroupChatEvent $event;

    public function __construct(GroupChat $groupChat, GroupChatEvent $groupChatEvent) {
        $this->groupChat = $groupChat;
        $this->event = $groupChatEvent;
    }

    public function getGroupChat(): GroupChat {
        return $this->groupChat;
    }

    public function getEvent(): GroupChatEvent {
        return $this->event;
    }
}
