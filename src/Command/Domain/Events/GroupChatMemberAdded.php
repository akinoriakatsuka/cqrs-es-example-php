<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Member;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidValidator;

final readonly class GroupChatMemberAdded implements GroupChatEvent
{
    public function __construct(
        private string $id,
        private GroupChatId $aggregate_id,
        private Member $member,
        private int $seq_nr,
        private UserAccountId $executor_id,
        private int $occurred_at
    ) {
    }

    public static function create(
        GroupChatId $aggregate_id,
        Member $member,
        int $seq_nr,
        UserAccountId $executor_id
    ): self {
        $ulid = \Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\Ulid::generate();
        $id = $ulid->toString();
        $occurred_at = (int)(microtime(true) * 1000);
        return new self($id, $aggregate_id, $member, $seq_nr, $executor_id, $occurred_at);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTypeName(): string
    {
        return 'GroupChatMemberAdded';
    }

    public function getAggregateId(): string
    {
        return $this->aggregate_id->toString();
    }

    public function getSeqNr(): int
    {
        return $this->seq_nr;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function getOccurredAt(): int
    {
        return $this->occurred_at;
    }

    public function isCreated(): bool
    {
        return false;
    }

    public function toArray(): array
    {
        return [
            'type_name' => $this->getTypeName(),
            'id' => $this->id,
            'aggregate_id' => $this->aggregate_id->toArray(),
            'member' => $this->member->toArray(),
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
            Member::fromArray($data['member'], $validator),
            $data['seq_nr'],
            UserAccountId::fromArray($data['executor_id'], $validator),
            $data['occurred_at']
        );
    }

    public static function fromArrayWithFactories(
        array $data,
        \Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatIdFactory $groupChatIdFactory,
        \Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountIdFactory $userAccountIdFactory,
        \Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberIdFactory $memberIdFactory
    ): self {
        return new self(
            $data['id'],
            $groupChatIdFactory->fromArray($data['aggregate_id']),
            Member::fromArrayWithFactories($data['member'], $userAccountIdFactory, $memberIdFactory),
            $data['seq_nr'],
            $userAccountIdFactory->fromArray($data['executor_id']),
            $data['occurred_at']
        );
    }
}
