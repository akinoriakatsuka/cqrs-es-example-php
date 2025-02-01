<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

readonly class Member {
    private MemberId $id;
    private UserAccountId $userAccountId;
    private MemberRole $role;

    public function __construct(
        MemberId $id,
        UserAccountId $userAccountId,
        MemberRole $role
    ) {
        $this->id = $id;
        $this->userAccountId = $userAccountId;
        $this->role = $role;
    }

    public function getId(): MemberId {
        return $this->id;
    }

    public function getUserAccountId(): UserAccountId {
        return $this->userAccountId;
    }

    public function getRole(): MemberRole {
        return $this->role;
    }
}
