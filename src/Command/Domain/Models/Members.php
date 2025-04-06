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

    /**
     * @param UserAccountId $userAccountId
     * @return Members
     */
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

    /**
     * @param UserAccountId $userAccountId
     * @return Members
     * @throws \RuntimeException If member not found
     */
    public function removeMember(UserAccountId $userAccountId): Members {
        $newValues = [];
        $found = false;

        foreach ($this->values as $member) {
            if (!$member->getUserAccountId()->equals($userAccountId)) {
                $newValues[] = $member;
            } else {
                $found = true;
            }
        }

        if (!$found) {
            throw new \RuntimeException("Member not found with user account ID: " . $userAccountId->getValue());
        }

        return new Members($newValues);
    }

    /**
     * @param UserAccountId $userAccountId
     * @return Member|null
     */
    public function findByUserAccountId(UserAccountId $userAccountId): Member|null {
        foreach ($this->values as $member) {
            if ($member->getUserAccountId()->equals($userAccountId)) {
                return $member;
            }
        }
        return null;
    }
}
