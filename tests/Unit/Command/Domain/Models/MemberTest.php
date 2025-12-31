<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain\Models;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Member;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Role;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidValidator;
use PHPUnit\Framework\TestCase;

class MemberTest extends TestCase
{
    private RobinvdvleutenUlidGenerator $generator;
    private RobinvdvleutenUlidValidator $validator;

    protected function setUp(): void
    {
        $this->generator = new RobinvdvleutenUlidGenerator();
        $this->validator = new RobinvdvleutenUlidValidator();
    }

    public function test_constructor_正常に生成できる(): void
    {
        $member_id = MemberId::generate($this->generator);
        $user_account_id = UserAccountId::generate($this->generator);
        $role = Role::ADMINISTRATOR;

        $member = new Member($member_id, $user_account_id, $role);

        $this->assertEquals($member_id->toString(), $member->getId()->toString());
        $this->assertEquals($user_account_id->toString(), $member->getUserAccountId()->toString());
        $this->assertTrue($member->getRole()->isAdministrator());
    }

    public function test_getters_各種ゲッターが正しく動作する(): void
    {
        $member_id = MemberId::generate($this->generator);
        $user_account_id = UserAccountId::generate($this->generator);
        $role = Role::MEMBER;

        $member = new Member($member_id, $user_account_id, $role);

        $this->assertInstanceOf(MemberId::class, $member->getId());
        $this->assertInstanceOf(UserAccountId::class, $member->getUserAccountId());
        $this->assertInstanceOf(Role::class, $member->getRole());
    }

    public function test_equals_同じIDのメンバーは等価(): void
    {
        $member_id = MemberId::generate($this->generator);
        $user_account_id1 = UserAccountId::generate($this->generator);
        $user_account_id2 = UserAccountId::generate($this->generator);

        $member1 = new Member($member_id, $user_account_id1, Role::ADMINISTRATOR);
        $member2 = new Member($member_id, $user_account_id2, Role::MEMBER);

        $this->assertTrue($member1->equals($member2));
    }

    public function test_equals_異なるIDのメンバーは等価でない(): void
    {
        $member_id1 = MemberId::generate($this->generator);
        $member_id2 = MemberId::generate($this->generator);
        $user_account_id = UserAccountId::generate($this->generator);

        $member1 = new Member($member_id1, $user_account_id, Role::MEMBER);
        $member2 = new Member($member_id2, $user_account_id, Role::MEMBER);

        $this->assertFalse($member1->equals($member2));
    }

    public function test_toArray_配列に変換できる(): void
    {
        $member_id = MemberId::generate($this->generator);
        $user_account_id = UserAccountId::generate($this->generator);
        $member = new Member($member_id, $user_account_id, Role::ADMINISTRATOR);

        $array = $member->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('user_account_id', $array);
        $this->assertArrayHasKey('role', $array);
        $this->assertEquals(1, $array['role']); // Role::ADMINISTRATOR->value
    }

    public function test_fromArray_配列から復元できる(): void
    {
        $member_id = MemberId::generate($this->generator);
        $user_account_id = UserAccountId::generate($this->generator);

        $data = [
            'id' => ['value' => $member_id->toString()],
            'user_account_id' => ['value' => $user_account_id->toString()],
            'role' => 1, // Role::ADMINISTRATOR->value
        ];

        $member = Member::fromArray($data, $this->validator);

        $this->assertEquals($member_id->toString(), $member->getId()->toString());
        $this->assertEquals($user_account_id->toString(), $member->getUserAccountId()->toString());
        $this->assertTrue($member->getRole()->isAdministrator());
    }

    public function test_toArray_fromArray_ラウンドトリップでデータが保持される(): void
    {
        $member_id = MemberId::generate($this->generator);
        $user_account_id = UserAccountId::generate($this->generator);
        $original_member = new Member($member_id, $user_account_id, Role::MEMBER);

        $array = $original_member->toArray();
        $restored_member = Member::fromArray($array, $this->validator);

        $this->assertTrue($original_member->equals($restored_member));
        $this->assertEquals(
            $original_member->getUserAccountId()->toString(),
            $restored_member->getUserAccountId()->toString()
        );
        $this->assertEquals(
            $original_member->getRole()->value,
            $restored_member->getRole()->value
        );
    }
}
