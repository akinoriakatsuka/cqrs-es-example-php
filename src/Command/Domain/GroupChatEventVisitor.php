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

/**
 * Visitor interface for applying GroupChat events
 * Implements the Visitor pattern to handle different event types
 */
interface GroupChatEventVisitor
{
    /**
     * Visit a GroupChatCreated event
     *
     * @param GroupChatCreated $event
     * @param GroupChat        $aggregate
     *
     * @return GroupChat
     */
    public function visitCreated(GroupChatCreated $event, GroupChat $aggregate): GroupChat;

    /**
     * Visit a GroupChatRenamed event
     *
     * @param GroupChatRenamed $event
     * @param GroupChat        $aggregate
     *
     * @return GroupChat
     */
    public function visitRenamed(GroupChatRenamed $event, GroupChat $aggregate): GroupChat;

    /**
     * Visit a GroupChatDeleted event
     *
     * @param GroupChatDeleted $event
     * @param GroupChat        $aggregate
     *
     * @return GroupChat
     */
    public function visitDeleted(GroupChatDeleted $event, GroupChat $aggregate): GroupChat;

    /**
     * Visit a GroupChatMemberAdded event
     *
     * @param GroupChatMemberAdded $event
     * @param GroupChat            $aggregate
     *
     * @return GroupChat
     */
    public function visitMemberAdded(GroupChatMemberAdded $event, GroupChat $aggregate): GroupChat;

    /**
     * Visit a GroupChatMemberRemoved event
     *
     * @param GroupChatMemberRemoved $event
     * @param GroupChat              $aggregate
     *
     * @return GroupChat
     */
    public function visitMemberRemoved(GroupChatMemberRemoved $event, GroupChat $aggregate): GroupChat;

    /**
     * Visit a GroupChatMessagePosted event
     *
     * @param GroupChatMessagePosted $event
     * @param GroupChat              $aggregate
     *
     * @return GroupChat
     */
    public function visitMessagePosted(GroupChatMessagePosted $event, GroupChat $aggregate): GroupChat;

    /**
     * Visit a GroupChatMessageEdited event
     *
     * @param GroupChatMessageEdited $event
     * @param GroupChat              $aggregate
     *
     * @return GroupChat
     */
    public function visitMessageEdited(GroupChatMessageEdited $event, GroupChat $aggregate): GroupChat;

    /**
     * Visit a GroupChatMessageDeleted event
     *
     * @param GroupChatMessageDeleted $event
     * @param GroupChat               $aggregate
     *
     * @return GroupChat
     */
    public function visitMessageDeleted(GroupChatMessageDeleted $event, GroupChat $aggregate): GroupChat;
}