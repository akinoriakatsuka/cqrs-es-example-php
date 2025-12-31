<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain\Models;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Member;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Members;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Role;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidValidator;
use PHPUnit\Framework\TestCase;

class MembersTest extends TestCase
{
    private RobinvdvleutenUlidGenerator $generator;
    private RobinvdvleutenUlidValidator $validator;

    protected function setUp(): void
    {
        $this->generator = new RobinvdvleutenUlidGenerator();
        $this->validator = new RobinvdvleutenUlidValidator();
    }

    public function test_create_初期メンバーとして管理者が追加される(): void
    {
        $executor_id = UserAccountId::generate($this->generator);

        $members = Members::create($executor_id, $this->generator);

        $this->assertTrue($members->isMember($executor_id));
        $this->assertTrue($members->isAdministrator($executor_id));
    }

    public function test_isMember_メンバーであればtrueを返す(): void
    {
        $executor_id = UserAccountId::generate($this->generator);
        $members = Members::create($executor_id, $this->generator);

        $this->assertTrue($members->isMember($executor_id));
    }

    public function test_isMember_メンバーでなければfalseを返す(): void
    {
        $executor_id = UserAccountId::generate($this->generator);
        $other_user_id = UserAccountId::generate($this->generator);
        $members = Members::create($executor_id, $this->generator);

        $this->assertFalse($members->isMember($other_user_id));
    }

    public function test_isAdministrator_管理者であればtrueを返す(): void
    {
        $admin_id = UserAccountId::generate($this->generator);
        $members = Members::create($admin_id, $this->generator);

        $this->assertTrue($members->isAdministrator($admin_id));
    }

    public function test_isAdministrator_一般メンバーはfalseを返す(): void
    {
        $admin_id = UserAccountId::generate($this->generator);
        $member_id = UserAccountId::generate($this->generator);
        $members = Members::create($admin_id, $this->generator);

        $new_member = new Member(
            MemberId::generate($this->generator),
            $member_id,
            Role::MEMBER
        );
        $updated_members = $members->addMember($new_member);

        $this->assertFalse($updated_members->isAdministrator($member_id));
    }

    public function test_isAdministrator_メンバーでなければfalseを返す(): void
    {
        $admin_id = UserAccountId::generate($this->generator);
        $non_member_id = UserAccountId::generate($this->generator);
        $members = Members::create($admin_id, $this->generator);

        $this->assertFalse($members->isAdministrator($non_member_id));
    }

    public function test_addMember_メンバーを追加できる(): void
    {
        $admin_id = UserAccountId::generate($this->generator);
        $new_user_id = UserAccountId::generate($this->generator);
        $members = Members::create($admin_id, $this->generator);

        $new_member = new Member(
            MemberId::generate($this->generator),
            $new_user_id,
            Role::MEMBER
        );
        $updated_members = $members->addMember($new_member);

        $this->assertTrue($updated_members->isMember($new_user_id));
    }

    public function test_removeMemberByUserAccountId_メンバーを削除できる(): void
    {
        $admin_id = UserAccountId::generate($this->generator);
        $member_id = UserAccountId::generate($this->generator);
        $members = Members::create($admin_id, $this->generator);

        $new_member = new Member(
            MemberId::generate($this->generator),
            $member_id,
            Role::MEMBER
        );
        $updated_members = $members->addMember($new_member);
        $removed_members = $updated_members->removeMemberByUserAccountId($member_id);

        $this->assertFalse($removed_members->isMember($member_id));
    }

    public function test_removeMemberByUserAccountId_存在しないメンバーはエラー(): void
    {
        $admin_id = UserAccountId::generate($this->generator);
        $non_member_id = UserAccountId::generate($this->generator);
        $members = Members::create($admin_id, $this->generator);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Member not found');

        $members->removeMemberByUserAccountId($non_member_id);
    }

    public function test_toArray_配列に変換できる(): void
    {
        $admin_id = UserAccountId::generate($this->generator);
        $members = Members::create($admin_id, $this->generator);

        $array = $members->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('values', $array);
        $this->assertCount(1, $array['values']);
    }

    public function test_fromArray_配列から復元できる(): void
    {
        $admin_id = UserAccountId::generate($this->generator);
        $member_id = MemberId::generate($this->generator);

        $data = [
            'values' => [
                [
                    'id' => ['value' => $member_id->toString()],
                    'user_account_id' => ['value' => $admin_id->toString()],
                    'role' => 1, // Role::ADMINISTRATOR->value
                ],
            ],
        ];

        $members = Members::fromArray($data, $this->validator);

        $this->assertTrue($members->isMember($admin_id));
        $this->assertTrue($members->isAdministrator($admin_id));
    }

    public function test_toArray_fromArray_ラウンドトリップでデータが保持される(): void
    {
        $admin_id = UserAccountId::generate($this->generator);
        $original_members = Members::create($admin_id, $this->generator);

        $array = $original_members->toArray();
        $restored_members = Members::fromArray($array, $this->validator);

        $this->assertTrue($restored_members->isMember($admin_id));
        $this->assertTrue($restored_members->isAdministrator($admin_id));
    }
}
