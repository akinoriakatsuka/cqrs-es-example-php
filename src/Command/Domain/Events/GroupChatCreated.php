<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Members;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\Ulid;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidValidator;

final readonly class GroupChatCreated implements GroupChatEvent
{
    public function __construct(
        private string $id,
        private GroupChatId $aggregate_id,
        private GroupChatName $name,
        private Members $members,
        private int $seq_nr,
        private UserAccountId $executor_id,
        private int $occurred_at
    ) {
    }

    public static function create(
        GroupChatId $aggregate_id,
        GroupChatName $name,
        Members $members,
        int $seq_nr,
        UserAccountId $executor_id
    ): self {
        $ulid = Ulid::generate();
        $id = $ulid->toString();
        $occurred_at = (int)(microtime(true) * 1000);
        return new self($id, $aggregate_id, $name, $members, $seq_nr, $executor_id, $occurred_at);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTypeName(): string
    {
        return 'GroupChatCreated';
    }

    public function getAggregateId(): string
    {
        return $this->aggregate_id->toString();
    }

    public function getAggregateIdAsObject(): GroupChatId
    {
        return $this->aggregate_id;
    }

    public function getName(): GroupChatName
    {
        return $this->name;
    }

    public function getMembers(): Members
    {
        return $this->members;
    }

    public function getSeqNr(): int
    {
        return $this->seq_nr;
    }

    public function getOccurredAt(): int
    {
        return $this->occurred_at;
    }

    public function isCreated(): bool
    {
        return true;
    }

    public function toArray(): array
    {
        return [
            'type_name' => $this->getTypeName(),
            'id' => $this->id,
            'aggregate_id' => $this->aggregate_id->toArray(),
            'name' => $this->name->toArray(),
            'members' => $this->members->toArray(),
            'executor_id' => $this->executor_id->toArray(),
            'seq_nr' => $this->seq_nr,
            'occurred_at' => $this->occurred_at,
        ];
    }

    /**
     * @deprecated Use fromArrayWithFactories() instead. This method will be removed in future versions.
     */
    public static function fromArray(array $data, UlidValidator $validator): self
    {
        return new self(
            $data['id'],
            GroupChatId::fromArray($data['aggregate_id'], $validator),
            GroupChatName::fromArray($data['name']),
            Members::fromArray($data['members'], $validator),
            $data['seq_nr'],
            UserAccountId::fromArray($data['executor_id'], $validator),
            $data['occurred_at']
        );
    }

    public static function fromArrayWithFactories(
        array $data,
        GroupChatIdFactory $groupChatIdFactory,
        UserAccountIdFactory $userAccountIdFactory,
        MemberIdFactory $memberIdFactory,
        UlidValidator $validator
    ): self {
        return new self(
            $data['id'],
            $groupChatIdFactory->fromArray($data['aggregate_id']),
            GroupChatName::fromArray($data['name']),
            Members::fromArrayWithFactories($data['members'], $userAccountIdFactory, $memberIdFactory),
            $data['seq_nr'],
            $userAccountIdFactory->fromArray($data['executor_id']),
            $data['occurred_at']
        );
    }
}
