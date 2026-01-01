<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

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
        MemberIdFactory $member_id_factory
    ): self {
        // 作成時にexecutorをADMINISTRATORとして追加
        $member = new Member(
            $member_id_factory->create(),
            $executor_user_account_id,
            Role::ADMINISTRATOR
        );

        return new self([$member]);
    }

    /**
     * @param array<Member> $members
     */
    public static function fromMembers(array $members): self
    {
        return new self($members);
    }


    /**
     * @deprecated Use MembersFactory::fromArray() instead
     */
    public static function fromArrayWithFactories(
        array $data,
        UserAccountIdFactory $userAccountIdFactory,
        MemberIdFactory $memberIdFactory
    ): self {
        $members = array_map(
            fn ($member_data) => Member::fromArrayWithFactories($member_data, $userAccountIdFactory, $memberIdFactory),
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
            'values' => array_map(fn ($member) => $member->toArray(), $this->members),
        ];
    }
}
