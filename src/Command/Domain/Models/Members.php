<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

readonly class Members {
    /** @var array<Member> */
    private array $values;

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
        return new Members([$member]);
    }

    /**
     * @return array<Member>
     */
    public function getValues(): array {
        return $this->values;
    }

    public function addMember(UserAccountId $userAccountId): Members {
        $memberId = new MemberId();
        $member = new Member(
            $memberId,
            $userAccountId,
            MemberRole::MEMBER_ROLE
        );
        $values = $this->values;
        $values[] = $member;
        return new Members($values);
    }
}
