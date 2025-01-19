<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain;

use J5ik2o\EventStoreAdapterPhp\Aggregate;
use J5ik2o\EventStoreAdapterPhp\AggregateId;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChatEventFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatCreated;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Members;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Messages;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;

class GroupChat implements Aggregate
{
    private readonly GroupChatId $id;
    private readonly GroupChatName $name;
    private readonly Members $members;
    private readonly Messages $messages;
    private readonly int $sequenceNumber;
    private readonly int $version;

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
     * @param UserAccountId $executerId
     * @return array{0: GroupChat, 1: GroupChatCreated}
     */
    public static function create(
        GroupChatName $name,
        UserAccountId $executerId
    ): array {
        $id = new GroupChatId();
        $members = Members::create($executerId);
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
        return [$aggregate, $event];
    }

    public function getName(): GroupChatName
    {
        return $this->name;
    }

    public function getId(): AggregateId
    {
        return $this->id;
    }

    public function getMembers(): Members
    {
        return $this->members;
    }

    public function getMessages(): Messages
    {
        return $this->messages;
    }

    public function getSequenceNumber(): int
    {
        return 0;
    }

    public function getVersion(): int
    {
        return 0;
    }

    /**
     * Sets the version.
     *
     * @param int $version
     * @return Aggregate
     */
    public function withVersion(int $version): Aggregate
    {
        return $this;
    }

    public function equals(Aggregate $other): bool
    {
        return true;
    }

    public function jsonSerialize(): mixed
    {
        return [
            "id" => $this->id,
            "sequenceNumber" => $this->sequenceNumber,
            "name" => $this->name,
            "version" => $this->version,
        ];
    }
}
