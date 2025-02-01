<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain;

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

    public function __construct(
        GroupChatId $id,
        GroupChatName $name,
        Members $members,
        Messages $messages,
        int $sequenceNumber,
        int $version
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->members = $members;
        $this->messages = $messages;
        $this->sequenceNumber = $sequenceNumber;
        $this->version = $version;
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
            $version
        );
        $event = GroupChatEventFactory::ofCreated(
            $id,
            $name
        );
        return new GroupChatWithEventPair($aggregate, $event);
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
        // TODO: Error handling
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

    public function jsonSerialize(): mixed {
        return [
            "id" => $this->id,
            "sequenceNumber" => $this->sequenceNumber,
            "name" => $this->name,
            "version" => $this->version,
        ];
    }
}
