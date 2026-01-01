<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatCreated;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatDeleted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatEvent;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberAdded;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberRemoved;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessageDeleted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessageEdited;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessagePosted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatRenamed;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Member;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Members;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Message;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Messages;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Role;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use DomainException;

/**
 * GroupChat is an aggregate of a group chat.
 * This corresponds to Go's GroupChat struct in pkg/command/domain/group_chat.go
 */
final readonly class GroupChat
{
    private function __construct(
        private GroupChatId   $id,
        private GroupChatName $name,
        private Members       $members,
        private Messages      $messages,
        private int           $seq_nr,
        private int           $version,
        private bool          $deleted,
    ) {
    }

    /**
     * fromSnapshot reconstructs a GroupChat from snapshot data.
     * This corresponds to Go's: func NewGroupChatFrom(...) GroupChat
     *
     * @param GroupChatId $id
     * @param GroupChatName $name
     * @param Members $members
     * @param Messages $messages
     * @param int $seq_nr
     * @param int $version
     * @param bool $deleted
     * @return self
     */
    public static function fromSnapshot(
        GroupChatId $id,
        GroupChatName $name,
        Members $members,
        Messages $messages,
        int $seq_nr,
        int $version,
        bool $deleted
    ): self {
        return new self($id, $name, $members, $messages, $seq_nr, $version, $deleted);
    }

    /**
     * NewGroupChat creates a new group chat.
     * This corresponds to Go's: func NewGroupChat(name models.GroupChatName, executorId models.UserAccountId) (GroupChat, events.GroupChatEvent)
     *
     * @param GroupChatId $id
     * @param GroupChatName $name
     * @param UserAccountId $executor_id
     * @param MemberIdFactory $member_id_factory
     * @return GroupChatWithEventPair
     */
    public static function create(
        GroupChatId $id,
        GroupChatName $name,
        UserAccountId $executor_id,
        MemberIdFactory $member_id_factory
    ): GroupChatWithEventPair {
        $members = Members::create($executor_id, $member_id_factory);
        $messages = Messages::create();
        $seq_nr = 1;
        $version = 1;

        $group_chat = new self($id, $name, $members, $messages, $seq_nr, $version, false);

        $event = GroupChatCreated::create(
            $id,
            $name,
            $members,
            $seq_nr,
            $executor_id
        );

        return new GroupChatWithEventPair($group_chat, $event);
    }

    /**
     * GetId returns the aggregate id.
     */
    public function getId(): GroupChatId
    {
        return $this->id;
    }

    /**
     * GetName returns the aggregate name.
     */
    public function getName(): GroupChatName
    {
        return $this->name;
    }

    /**
     * GetMembers returns the aggregate members.
     */
    public function getMembers(): Members
    {
        return $this->members;
    }

    /**
     * GetMessages returns the aggregate messages.
     */
    public function getMessages(): Messages
    {
        return $this->messages;
    }

    /**
     * GetSeqNr returns the sequence number.
     */
    public function getSeqNr(): int
    {
        return $this->seq_nr;
    }

    /**
     * GetVersion returns the version number.
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * IsDeleted returns whether the aggregate is deleted.
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * IsMember returns whether the user account is a member.
     */
    public function isMember(UserAccountId $user_account_id): bool
    {
        return $this->members->isMember($user_account_id);
    }

    /**
     * IsAdministrator returns whether the user account is an administrator.
     */
    public function isAdministrator(UserAccountId $user_account_id): bool
    {
        return $this->members->isAdministrator($user_account_id);
    }

    /**
     * Rename renames the aggregate.
     * This corresponds to Go's: func (g *GroupChat) Rename(name models.GroupChatName, executorId models.UserAccountId) mo.Result[GroupChatWithEventPair]
     *
     * Constraints:
     * - The group chat is not deleted
     * - The executorId is the administrator of the group chat
     * - The name is not the same as the current name
     *
     * @param GroupChatName $name
     * @param UserAccountId $executor_id
     * @return GroupChatWithEventPair
     * @throws DomainException
     */
    public function rename(
        GroupChatName $name,
        UserAccountId $executor_id
    ): GroupChatWithEventPair {
        if ($this->deleted) {
            throw new DomainException('The group chat is deleted');
        }
        if (!$this->members->isMember($executor_id)) {
            throw new DomainException('The executorId is not the member of the group chat');
        }
        if (!$this->members->isAdministrator($executor_id)) {
            throw new DomainException('The executorId is not an administrator of the group chat');
        }
        if ($this->name->equals($name)) {
            throw new DomainException('The name is already the same as the current name');
        }

        $new_state = new self(
            $this->id,
            $name,
            $this->members,
            $this->messages,
            $this->seq_nr + 1,
            $this->version,
            $this->deleted
        );

        $event = GroupChatRenamed::create(
            $new_state->id,
            $name,
            $new_state->seq_nr,
            $executor_id
        );

        return new GroupChatWithEventPair($new_state, $event);
    }

    /**
     * Delete deletes the aggregate.
     * This corresponds to Go's: func (g *GroupChat) Delete(executorId models.UserAccountId) mo.Result[GroupChatWithEventPair]
     *
     * Constraints:
     * - The group chat is not deleted
     * - The executorId is the administrator of the group chat
     *
     * @param UserAccountId $executor_id
     * @return GroupChatWithEventPair
     * @throws DomainException
     */
    public function delete(
        UserAccountId $executor_id
    ): GroupChatWithEventPair {
        if ($this->deleted) {
            throw new DomainException('The group chat is deleted');
        }
        if (!$this->members->isMember($executor_id)) {
            throw new DomainException('The executorId is not the member of the group chat');
        }
        if (!$this->members->isAdministrator($executor_id)) {
            throw new DomainException('The executorId is not an administrator of the group chat');
        }

        $new_state = new self(
            $this->id,
            $this->name,
            $this->members,
            $this->messages,
            $this->seq_nr + 1,
            $this->version,
            true  // deleted = true
        );

        $event = GroupChatDeleted::create(
            $new_state->id,
            $new_state->seq_nr,
            $executor_id
        );

        return new GroupChatWithEventPair($new_state, $event);
    }

    /**
     * AddMember adds a new member to the aggregate.
     * This corresponds to Go's: func (g *GroupChat) AddMember(memberId models.MemberId, userAccountId models.UserAccountId, role models.Role, executorId models.UserAccountId) mo.Result[GroupChatWithEventPair]
     *
     * Constraints:
     * - The group chat is not deleted
     * - The userAccountId is not the member of the group chat
     * - The executorId is the administrator of the group chat
     *
     * @param MemberId $member_id
     * @param UserAccountId $user_account_id
     * @param Role $role
     * @param UserAccountId $executor_id
     * @return GroupChatWithEventPair
     * @throws DomainException
     */
    public function addMember(
        MemberId $member_id,
        UserAccountId $user_account_id,
        Role $role,
        UserAccountId $executor_id
    ): GroupChatWithEventPair {
        if ($this->deleted) {
            throw new DomainException('The group chat is deleted');
        }
        if ($this->members->isMember($user_account_id)) {
            throw new DomainException('The userAccountId is already the member of the group chat');
        }
        if (!$this->members->isAdministrator($executor_id)) {
            throw new DomainException('The executorId is not the administrator of the group chat');
        }

        $new_member = new Member($member_id, $user_account_id, $role);
        $new_members = $this->members->addMember($new_member);

        $new_state = new self(
            $this->id,
            $this->name,
            $new_members,
            $this->messages,
            $this->seq_nr + 1,
            $this->version,
            $this->deleted
        );

        $event = GroupChatMemberAdded::create(
            $new_state->id,
            $new_member,
            $new_state->seq_nr,
            $executor_id
        );

        return new GroupChatWithEventPair($new_state, $event);
    }

    public function removeMember(
        UserAccountId $user_account_id,
        UserAccountId $executor_id
    ): GroupChatWithEventPair {
        if ($this->deleted) {
            throw new DomainException('The group chat is deleted');
        }
        if (!$this->members->isMember($user_account_id)) {
            throw new DomainException('The userAccountId is not a member of the group chat');
        }
        if (!$this->members->isAdministrator($executor_id)) {
            throw new DomainException('The executorId is not the administrator of the group chat');
        }

        $new_members = $this->members->removeMemberByUserAccountId($user_account_id);

        $new_state = new self(
            $this->id,
            $this->name,
            $new_members,
            $this->messages,
            $this->seq_nr + 1,
            $this->version,
            $this->deleted
        );

        $event = GroupChatMemberRemoved::create(
            $new_state->id,
            $user_account_id,
            $new_state->seq_nr,
            $executor_id
        );

        return new GroupChatWithEventPair($new_state, $event);
    }

    public function postMessage(
        MessageId $message_id,
        string $text,
        UserAccountId $sender_id
    ): GroupChatWithEventPair {
        if ($this->deleted) {
            throw new DomainException('The group chat is deleted');
        }
        if (!$this->members->isMember($sender_id)) {
            throw new DomainException('The senderId is not a member of the group chat');
        }

        $message = new Message($message_id, $text, $sender_id);
        $new_messages = $this->messages->add($message);

        $new_state = new self(
            $this->id,
            $this->name,
            $this->members,
            $new_messages,
            $this->seq_nr + 1,
            $this->version,
            $this->deleted
        );

        $event = GroupChatMessagePosted::create(
            $new_state->id,
            $message,
            $new_state->seq_nr,
            $sender_id
        );

        return new GroupChatWithEventPair($new_state, $event);
    }

    public function editMessage(
        MessageId $message_id,
        string $new_text,
        UserAccountId $executor_id
    ): GroupChatWithEventPair {
        if ($this->deleted) {
            throw new DomainException('The group chat is deleted');
        }
        if (!$this->members->isMember($executor_id)) {
            throw new DomainException('The executorId is not a member of the group chat');
        }

        $new_messages = $this->messages->edit($message_id, $new_text, $executor_id);
        $edited_message = $new_messages->findById($message_id);

        $new_state = new self(
            $this->id,
            $this->name,
            $this->members,
            $new_messages,
            $this->seq_nr + 1,
            $this->version,
            $this->deleted
        );

        $event = GroupChatMessageEdited::create(
            $new_state->id,
            $edited_message,
            $new_state->seq_nr,
            $executor_id
        );

        return new GroupChatWithEventPair($new_state, $event);
    }

    public function deleteMessage(
        MessageId $message_id,
        UserAccountId $executor_id
    ): GroupChatWithEventPair {
        if ($this->deleted) {
            throw new DomainException('The group chat is deleted');
        }
        if (!$this->members->isMember($executor_id)) {
            throw new DomainException('The executorId is not a member of the group chat');
        }

        $new_messages = $this->messages->remove($message_id, $executor_id);

        $new_state = new self(
            $this->id,
            $this->name,
            $this->members,
            $new_messages,
            $this->seq_nr + 1,
            $this->version,
            $this->deleted
        );

        $event = GroupChatMessageDeleted::create(
            $new_state->id,
            $message_id,
            $new_state->seq_nr,
            $executor_id
        );

        return new GroupChatWithEventPair($new_state, $event);
    }

    /**
     * ApplyEvent applies an event to the aggregate to produce a new state.
     * This corresponds to Go's: func (g *GroupChat) ApplyEvent(event esa.Event) GroupChat
     *
     * @param GroupChatEvent $event
     * @return self
     */
    public function applyEvent(GroupChatEvent $event): self
    {
        return $event->applyTo($this);
    }
}
