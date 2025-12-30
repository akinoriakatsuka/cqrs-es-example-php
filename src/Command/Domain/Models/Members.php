<?php

declare(strict_types=1);

namespace App\Command\Domain\Models;

use App\Infrastructure\Ulid\UlidGenerator;

final readonly class Members
{
    /**
     * @param array<Member> $members
     */
    private function __construct(
        private array $members
    ) {
    }

    public static function create(
        UserAccountId $executor_user_account_id,
        UlidGenerator $ulid_generator
    ): self {
        // 作成時にexecutorをADMINISTRATORとして追加
        $member = new Member(
            MemberId::generate($ulid_generator),
            $executor_user_account_id,
            Role::ADMINISTRATOR
        );

        return new self([$member]);
    }

    public static function fromArray(array $data, \App\Infrastructure\Ulid\UlidValidator $validator): self
    {
        $members = array_map(
            fn($member_data) => Member::fromArray($member_data, $validator),
            $data['values']
        );
        return new self($members);
    }

    public function isMember(UserAccountId $user_account_id): bool
    {
        foreach ($this->members as $member) {
            if ($member->getUserAccountId()->equals($user_account_id)) {
                return true;
            }
        }
        return false;
    }

    public function isAdministrator(UserAccountId $user_account_id): bool
    {
        foreach ($this->members as $member) {
            if ($member->getUserAccountId()->equals($user_account_id)) {
                return $member->getRole()->isAdministrator();
            }
        }
        return false;
    }

    public function addMember(Member $member): self
    {
        return new self([...$this->members, $member]);
    }

    public function removeMemberByUserAccountId(UserAccountId $user_account_id): self
    {
        $found = false;
        $new_members = [];

        foreach ($this->members as $member) {
            if ($member->getUserAccountId()->equals($user_account_id)) {
                $found = true;
                continue;
            }
            $new_members[] = $member;
        }

        if (!$found) {
            throw new \DomainException('Member not found: ' . $user_account_id->toString());
        }

        return new self($new_members);
    }

    public function toArray(): array
    {
        return [
            'values' => array_map(fn($member) => $member->toArray(), $this->members)
        ];
    }
}
