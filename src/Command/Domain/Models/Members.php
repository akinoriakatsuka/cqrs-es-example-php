<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

class Members {
    /** @var array<Member> */
    private readonly array $values;

    /**
     * @param array<Member> $values
     */
    public function __construct(array $values) {
        $this->values = $values;
    }

    public static function create(UserAccountId $userAccountId): Members {
        $memberId = new MemberId();
        $member = new Member(
            $memberId,
            $userAccountId,
            MemberRole::ADMIN_ROLE
        );
        $members = new Members([$member]);
        return $members;
    }

    /**
     * @return array<Member>
     */
    public function getValues(): array {
        return $this->values;
    }
}
