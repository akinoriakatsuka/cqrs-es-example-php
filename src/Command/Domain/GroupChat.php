<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Errors\AlreadyDeletedException;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatDeleted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatEvent;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberAdded;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberRemoved;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessageDeleted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessageEdited;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessagePosted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatRenamed;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Message;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageId;
use J5ik2o\EventStoreAdapterPhp\Aggregate;
use J5ik2o\EventStoreAdapterPhp\AggregateId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberRole;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Members;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Messages;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;

readonly class GroupChat implements Aggregate {
    private GroupChatId $id;
    private GroupChatName $name;
    private Members $members;
    private Messages $messages;
    private int $sequenceNumber;
    private int $version;
    private bool $isDeleted;

    public function __construct(
        GroupChatId $id,
        GroupChatName $name,
        Members $members,
        Messages $messages,
        int $sequenceNumber,
        int $version,
        bool $isDeleted = false
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->members = $members;
        $this->messages = $messages;
        $this->sequenceNumber = $sequenceNumber;
        $this->version = $version;
        $this->isDeleted = $isDeleted;
    }

    /**
     * @param GroupChatName $name
     * @param UserAccountId $executorId
     * @return GroupChatWithEventPair
     */
    public static function create(
        GroupChatName $name,
        UserAccountId $executorId
    ): GroupChatWithEventPair {
        $id = new GroupChatId();
        $members = Members::create($executorId);
        $messages = new Messages([]);
        $sequenceNumber = 1;
        $version = 1;
        $aggregate = new GroupChat(
            $id,
            $name,
            $members,
            $messages,
            $sequenceNumber,
            $version,
            false
        );
        $event = GroupChatEventFactory::ofCreated(
            $id,
            $name
        );
        return new GroupChatWithEventPair($aggregate, $event);
    }

    /**
     * @param array<GroupChatEvent> $events
     * @param GroupChat $latestSnapshot
     * @return GroupChat
     */
    public static function replay(array $events, GroupChat $latestSnapshot): GroupChat {
        $aggregate = $latestSnapshot;
        foreach ($events as $event) {
            $aggregate = $aggregate->applyEvent($event);
        }
        return $aggregate;
    }

    public function applyEvent(GroupChatEvent $event): GroupChat {
        switch (true) {
            case $event instanceof GroupChatMemberAdded:
                $groupChat = $this;
                $GroupChatWithEventPair = $groupChat->addMember(
                    $event->getMember()->getId(),
                    $event->getMember()->getUserAccountId(),
                    $event->getMember()->getRole(),
                    $event->getExecutorId()
                );
                return $GroupChatWithEventPair->getGroupChat();
            case $event instanceof GroupChatMemberRemoved:
                $groupChat = $this;
                $GroupChatWithEventPair = $groupChat->removeMember(
                    $event->getMemberUserAccountId(),
                    $event->getExecutorId()
                );
                return $GroupChatWithEventPair->getGroupChat();
            case $event instanceof GroupChatRenamed:
                $groupChat = $this;
                $GroupChatWithEventPair = $groupChat->rename(
                    $event->getName(),
                    $event->getExecutorId()
                );
                return $GroupChatWithEventPair->getGroupChat();
            case $event instanceof GroupChatDeleted:
                return new GroupChat(
                    $this->id,
                    $this->name,
                    $this->members,
                    $this->messages,
                    $event->getSequenceNumber(),
                    $this->version,
                    true
                );
            case $event instanceof GroupChatMessagePosted:
                $newMessages = new Messages(array_merge($this->messages->getValues(), [$event->getMessage()]));
                return new GroupChat(
                    $this->id,
                    $this->name,
                    $this->members,
                    $newMessages,
                    $event->getSequenceNumber(),
                    $this->version,
                    $this->isDeleted
                );
            case $event instanceof GroupChatMessageEdited:
                $groupChat = $this;
                $GroupChatWithEventPair = $groupChat->editMessage(
                    $event->getMessageId(),
                    $event->getNewText(),
                    $event->getExecutorId()
                );
                return $GroupChatWithEventPair->getGroupChat();
            case $event instanceof GroupChatMessageDeleted:
                $groupChat = $this;
                $GroupChatWithEventPair = $groupChat->deleteMessage(
                    $event->getMessageId(),
                    $event->getExecutorId()
                );
                return $GroupChatWithEventPair->getGroupChat();
            default:
                return $this;
        }
    }

    /**
     * @param MemberId $memberId
     * @param UserAccountId $userAccountId
     * @param MemberRole $role
     * @param UserAccountId $executorId
     * @return GroupChatWithEventPair
     */
    public function addMember(
        MemberId $memberId,
        UserAccountId $userAccountId,
        MemberRole $role,
        UserAccountId $executorId
    ): GroupChatWithEventPair {
        if ($this->isDeleted) {
            throw new AlreadyDeletedException("Cannot add member to a deleted group chat");
        }
        $newMembers = $this->getMembers()->addMember($userAccountId);
        $newState = new GroupChat(
            $this->id,
            $this->name,
            $newMembers,
            $this->messages,
            $this->sequenceNumber + 1,
            $this->version,
        );
        $event = GroupChatEventFactory::ofMemberAdded(
            $this->id,
            $memberId,
            $userAccountId,
            $role,
            $newState->getSequenceNumber(),
            $executorId
        );
        return new GroupChatWithEventPair($newState, $event);
    }

    /**
     * @param GroupChatName $name
     * @param UserAccountId $executorId
     * @return GroupChatWithEventPair
     */
    public function rename(
        GroupChatName $name,
        UserAccountId $executorId
    ): GroupChatWithEventPair {
        // TODO: Error handling
        $newState = new GroupChat(
            $this->id,
            $name,
            $this->members,
            $this->messages,
            $this->sequenceNumber + 1,
            $this->version,
        );
        $event = GroupChatEventFactory::ofRenamed(
            $this->id,
            $name,
            $newState->getSequenceNumber(),
            $executorId
        );
        return new GroupChatWithEventPair($newState, $event);
    }

    public function getName(): GroupChatName {
        return $this->name;
    }

    public function getId(): AggregateId {
        return $this->id;
    }

    public function getMembers(): Members {
        return $this->members;
    }

    public function getMessages(): Messages {
        return $this->messages;
    }

    public function getSequenceNumber(): int {
        return $this->sequenceNumber;
    }

    public function getVersion(): int {
        return $this->version;
    }

    /**
     * Sets the version.
     *
     * @param int $version
     * @return Aggregate
     */
    public function withVersion(int $version): Aggregate {
        return $this;
    }

    public function equals(Aggregate $other): bool {
        return true;
    }

    /**
     * Delete the group chat
     *
     * @param UserAccountId $executorId
     * @return GroupChatWithEventPair
     * @throws AlreadyDeletedException
     */
    public function delete(UserAccountId $executorId): GroupChatWithEventPair {
        if ($this->isDeleted) {
            throw new AlreadyDeletedException("Group chat is already deleted");
        }

        $newState = new GroupChat(
            $this->id,
            $this->name,
            $this->members,
            $this->messages,
            $this->sequenceNumber + 1,
            $this->version,
            true
        );

        $event = GroupChatEventFactory::ofDeleted(
            $this->id,
            $newState->getSequenceNumber(),
            $executorId
        );

        return new GroupChatWithEventPair($newState, $event);
    }

    /**
     * Post a message to the group chat
     *
     * @param MessageId $messageId
     * @param Message        $message
     * @param UserAccountId  $memberUserAccountId
     * @return GroupChatWithEventPair
     */
    /**
     * Remove a member from the group chat
     *
     * @param UserAccountId $memberUserAccountId
     * @param UserAccountId $executorId
     * @return GroupChatWithEventPair
     * @throws AlreadyDeletedException If group chat is deleted
     * @throws \RuntimeException If member not found
     */
    public function removeMember(
        UserAccountId $memberUserAccountId,
        UserAccountId $executorId
    ): GroupChatWithEventPair {
        if ($this->isDeleted) {
            throw new AlreadyDeletedException("Cannot remove member from a deleted group chat");
        }

        // Check if the member exists
        $member = $this->members->findByUserAccountId($memberUserAccountId);
        if ($member === null) {
            throw new \RuntimeException("Member not found with user account ID: " . $memberUserAccountId->getValue());
        }

        // Remove the member
        $newMembers = $this->members->removeMember($memberUserAccountId);

        // Create a new state with the updated members
        $newState = new GroupChat(
            $this->id,
            $this->name,
            $newMembers,
            $this->messages,
            $this->sequenceNumber + 1,
            $this->version
        );

        // Create the event
        $event = GroupChatEventFactory::ofMemberRemoved(
            $this->id,
            $memberUserAccountId,
            $executorId,
            $newState->getSequenceNumber()
        );

        return new GroupChatWithEventPair($newState, $event);
    }

    /**
     * Post a message to the group chat
     *
     * @param MessageId $messageId
     * @param Message        $message
     * @param UserAccountId  $memberUserAccountId
     * @return GroupChatWithEventPair
     * @throws AlreadyDeletedException If group chat is deleted
     */
    public function postMessage(MessageId $messageId, Message $message, UserAccountId $memberUserAccountId): GroupChatWithEventPair {
        if ($this->isDeleted) {
            throw new AlreadyDeletedException("Cannot post message to a deleted group chat");
        }

        // Add the message to the messages collection
        $newMessages = new Messages(array_merge($this->messages->getValues(), [$message]));

        // Create a new state with the updated messages
        $newState = new GroupChat(
            $this->id,
            $this->name,
            $this->members,
            $newMessages,
            $this->sequenceNumber + 1,
            $this->version
        );

        // Create the event
        $event = GroupChatEventFactory::ofMessagePosted(
            $this->id,
            $message,
            $memberUserAccountId,
            $newState->getSequenceNumber()
        );

        return new GroupChatWithEventPair($newState, $event);
    }

    /**
     * Edit a message in the group chat
     *
     * @param MessageId $messageId
     * @param string $newText
     * @param UserAccountId $executorId
     * @return GroupChatWithEventPair
     * @throws AlreadyDeletedException If group chat is deleted
     * @throws \RuntimeException If message not found
     */
    public function editMessage(
        MessageId $messageId,
        string $newText,
        UserAccountId $executorId
    ): GroupChatWithEventPair {
        if ($this->isDeleted) {
            throw new AlreadyDeletedException("Cannot edit message in a deleted group chat");
        }

        // Check if the message exists
        $message = $this->messages->findById($messageId);
        if ($message === null) {
            throw new \RuntimeException("Message not found with ID: " . $messageId->getValue());
        }

        // Edit the message
        $newMessages = $this->messages->editMessage($messageId, $newText);

        // Create a new state with the updated messages
        $newState = new GroupChat(
            $this->id,
            $this->name,
            $this->members,
            $newMessages,
            $this->sequenceNumber + 1,
            $this->version
        );

        // Create the event
        $event = GroupChatEventFactory::ofMessageEdited(
            $this->id,
            $messageId,
            $newText,
            $executorId,
            $newState->getSequenceNumber()
        );

        return new GroupChatWithEventPair($newState, $event);
    }

    /**
     * Delete a message from the group chat
     *
     * @param MessageId $messageId
     * @param UserAccountId $executorId
     * @return GroupChatWithEventPair
     * @throws AlreadyDeletedException If group chat is deleted
     * @throws \RuntimeException If message not found
     */
    public function deleteMessage(
        MessageId $messageId,
        UserAccountId $executorId
    ): GroupChatWithEventPair {
        if ($this->isDeleted) {
            throw new AlreadyDeletedException("Cannot delete message from a deleted group chat");
        }

        // Check if the message exists
        $message = $this->messages->findById($messageId);
        if ($message === null) {
            throw new \RuntimeException("Message not found with ID: " . $messageId->getValue());
        }

        // Delete the message
        $newMessages = $this->messages->deleteMessage($messageId);

        // Create a new state with the updated messages
        $newState = new GroupChat(
            $this->id,
            $this->name,
            $this->members,
            $newMessages,
            $this->sequenceNumber + 1,
            $this->version
        );

        // Create the event
        $event = GroupChatEventFactory::ofMessageDeleted(
            $this->id,
            $messageId,
            $executorId,
            $newState->getSequenceNumber()
        );

        return new GroupChatWithEventPair($newState, $event);
    }

    public function isDeleted(): bool {
        return $this->isDeleted;
    }

    public function jsonSerialize(): mixed {
        return [
            "id" => $this->id,
            "sequenceNumber" => $this->sequenceNumber,
            "name" => $this->name,
            "version" => $this->version,
            "isDeleted" => $this->isDeleted,
        ];
    }
}
