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
}
