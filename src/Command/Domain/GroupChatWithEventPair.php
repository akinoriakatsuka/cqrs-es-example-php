<?php

declare(strict_types=1);

namespace App\Command\Domain;

use App\Command\Domain\Common\Pair;
use App\Command\Domain\Events\GroupChatEvent;

/**
 * Type alias for GroupChat and GroupChatEvent pair.
 * This is equivalent to Go's: type GroupChatWithEventPair = gt.Pair[GroupChat, events.GroupChatEvent]
 *
 * @extends Pair<GroupChat, GroupChatEvent>
 */
class GroupChatWithEventPair extends Pair
{
    /**
     * @param GroupChat $group_chat
     * @param GroupChatEvent $event
     */
    public function __construct(GroupChat $group_chat, GroupChatEvent $event)
    {
        parent::__construct($group_chat, $event);
    }

    /**
     * @return GroupChat
     */
    public function getGroupChat(): GroupChat
    {
        return $this->first();
    }

    /**
     * @return GroupChatEvent
     */
    public function getEvent(): GroupChatEvent
    {
        return $this->second();
    }
}
