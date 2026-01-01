<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

final readonly class Member
{
    public function __construct(
        private MemberId $id,
        private UserAccountId $user_account_id,
        private Role $role
    ) {
    }

    public function getId(): MemberId
    {
        return $this->id;
    }

    public function getUserAccountId(): UserAccountId
    {
        return $this->user_account_id;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function equals(self $other): bool
    {
        return $this->id->equals($other->id);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toArray(),
            'user_account_id' => $this->user_account_id->toArray(),
            'role' => $this->role->value,
        ];
    }

    /**
     * @deprecated Use fromArrayWithFactories() instead. This method will be removed in future versions.
     */
    public static function fromArray(array $data, \Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidValidator $validator): self
    {
        return new self(
            MemberId::fromArray($data['id'], $validator),
            UserAccountId::fromArray($data['user_account_id'], $validator),
            Role::from((int)$data['role'])
        );
    }

    public static function fromArrayWithFactories(
        array $data,
        UserAccountIdFactory $userAccountIdFactory,
        MemberIdFactory $memberIdFactory
    ): self {
        return new self(
            $memberIdFactory->fromArray($data['id']),
            $userAccountIdFactory->fromArray($data['user_account_id']),
            Role::from((int)$data['role'])
        );
    }
}
