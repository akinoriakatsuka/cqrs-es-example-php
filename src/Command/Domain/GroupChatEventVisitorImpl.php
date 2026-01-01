<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatCreated;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatDeleted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberAdded;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberRemoved;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessageDeleted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessageEdited;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessagePosted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatRenamed;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Messages;

/**
 * Implementation of GroupChatEventVisitor
 * Applies events to GroupChat aggregate using the Visitor pattern
 */
class GroupChatEventVisitorImpl implements GroupChatEventVisitor
{
    /**
     * {@inheritDoc}
     */
    public function visitCreated(GroupChatCreated $event, GroupChat $aggregate): GroupChat
    {
        return GroupChat::fromSnapshot(
            $event->getAggregateIdAsObject(),
            $event->getName(),
            $event->getMembers(),
            Messages::create(),
            $event->getSeqNr(),
            1,
            false
        );
    }

    /**
     * {@inheritDoc}
     */
    public function visitRenamed(GroupChatRenamed $event, GroupChat $aggregate): GroupChat
    {
        return GroupChat::fromSnapshot(
            $aggregate->getId(),
            $event->getName(),
            $aggregate->getMembers(),
            $aggregate->getMessages(),
            $event->getSeqNr(),
            $aggregate->getVersion() + 1,
            $aggregate->isDeleted()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function visitDeleted(GroupChatDeleted $event, GroupChat $aggregate): GroupChat
    {
        return GroupChat::fromSnapshot(
            $aggregate->getId(),
            $aggregate->getName(),
            $aggregate->getMembers(),
            $aggregate->getMessages(),
            $event->getSeqNr(),
            $aggregate->getVersion() + 1,
            true
        );
    }

    /**
     * {@inheritDoc}
     */
    public function visitMemberAdded(GroupChatMemberAdded $event, GroupChat $aggregate): GroupChat
    {
        return GroupChat::fromSnapshot(
            $aggregate->getId(),
            $aggregate->getName(),
            $aggregate->getMembers()->addMember($event->getMember()),
            $aggregate->getMessages(),
            $event->getSeqNr(),
            $aggregate->getVersion() + 1,
            $aggregate->isDeleted()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function visitMemberRemoved(GroupChatMemberRemoved $event, GroupChat $aggregate): GroupChat
    {
        return GroupChat::fromSnapshot(
            $aggregate->getId(),
            $aggregate->getName(),
            $aggregate->getMembers()->removeMemberByUserAccountId($event->getUserAccountId()),
            $aggregate->getMessages(),
            $event->getSeqNr(),
            $aggregate->getVersion() + 1,
            $aggregate->isDeleted()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function visitMessagePosted(GroupChatMessagePosted $event, GroupChat $aggregate): GroupChat
    {
        return GroupChat::fromSnapshot(
            $aggregate->getId(),
            $aggregate->getName(),
            $aggregate->getMembers(),
            $aggregate->getMessages()->add($event->getMessage()),
            $event->getSeqNr(),
            $aggregate->getVersion() + 1,
            $aggregate->isDeleted()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function visitMessageEdited(GroupChatMessageEdited $event, GroupChat $aggregate): GroupChat
    {
        return GroupChat::fromSnapshot(
            $aggregate->getId(),
            $aggregate->getName(),
            $aggregate->getMembers(),
            $aggregate->getMessages()->edit(
                $event->getMessage()->getId(),
                $event->getMessage()->getText(),
                $event->getMessage()->getSenderId()
            ),
            $event->getSeqNr(),
            $aggregate->getVersion() + 1,
            $aggregate->isDeleted()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function visitMessageDeleted(GroupChatMessageDeleted $event, GroupChat $aggregate): GroupChat
    {
        return GroupChat::fromSnapshot(
            $aggregate->getId(),
            $aggregate->getName(),
            $aggregate->getMembers(),
            $aggregate->getMessages()->remove(
                $event->getMessageId(),
                $aggregate->getMessages()->findById($event->getMessageId())->getSenderId()
            ),
            $event->getSeqNr(),
            $aggregate->getVersion() + 1,
            $aggregate->isDeleted()
        );
    }
}