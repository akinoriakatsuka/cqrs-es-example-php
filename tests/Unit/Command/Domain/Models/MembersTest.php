<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain\Models;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Member;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Members;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Role;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidValidator;
use PHPUnit\Framework\TestCase;

class MembersTest extends TestCase
{
    private RobinvdvleutenUlidGenerator $generator;
    private RobinvdvleutenUlidValidator $validator;
    private UserAccountIdFactory $user_account_id_factory;
    private MemberIdFactory $member_id_factory;

    protected function setUp(): void
    {
        $this->generator = new RobinvdvleutenUlidGenerator();
        $this->validator = new RobinvdvleutenUlidValidator();
        $this->user_account_id_factory = new UserAccountIdFactory($this->generator, $this->validator);
        $this->member_id_factory = new MemberIdFactory($this->generator, $this->validator);
    }

    public function test_create_初期メンバーとして管理者が追加される(): void
    {
        $executor_id = $this->user_account_id_factory->create();

        $members = Members::create($executor_id, $this->member_id_factory);

        $this->assertTrue($members->isMember($executor_id));
        $this->assertTrue($members->isAdministrator($executor_id));
    }

    public function test_isMember_メンバーであればtrueを返す(): void
    {
        $executor_id = $this->user_account_id_factory->create();
        $members = Members::create($executor_id, $this->member_id_factory);

        $this->assertTrue($members->isMember($executor_id));
    }

    public function test_isMember_メンバーでなければfalseを返す(): void
    {
        $executor_id = $this->user_account_id_factory->create();
        $other_user_id = $this->user_account_id_factory->create();
        $members = Members::create($executor_id, $this->member_id_factory);

        $this->assertFalse($members->isMember($other_user_id));
    }

    public function test_isAdministrator_管理者であればtrueを返す(): void
    {
        $admin_id = $this->user_account_id_factory->create();
        $members = Members::create($admin_id, $this->member_id_factory);

        $this->assertTrue($members->isAdministrator($admin_id));
    }

    public function test_isAdministrator_一般メンバーはfalseを返す(): void
    {
        $admin_id = $this->user_account_id_factory->create();
        $member_id = $this->user_account_id_factory->create();
        $members = Members::create($admin_id, $this->member_id_factory);

        $new_member = new Member(
            $this->member_id_factory->create(),
            $member_id,
            Role::MEMBER
        );
        $updated_members = $members->addMember($new_member);

        $this->assertFalse($updated_members->isAdministrator($member_id));
    }

    public function test_isAdministrator_メンバーでなければfalseを返す(): void
    {
        $admin_id = $this->user_account_id_factory->create();
        $non_member_id = $this->user_account_id_factory->create();
        $members = Members::create($admin_id, $this->member_id_factory);

        $this->assertFalse($members->isAdministrator($non_member_id));
    }

    public function test_addMember_メンバーを追加できる(): void
    {
        $admin_id = $this->user_account_id_factory->create();
        $new_user_id = $this->user_account_id_factory->create();
        $members = Members::create($admin_id, $this->member_id_factory);

        $new_member = new Member(
            $this->member_id_factory->create(),
            $new_user_id,
            Role::MEMBER
        );
        $updated_members = $members->addMember($new_member);

        $this->assertTrue($updated_members->isMember($new_user_id));
    }

    public function test_removeMemberByUserAccountId_メンバーを削除できる(): void
    {
        $admin_id = $this->user_account_id_factory->create();
        $member_id = $this->user_account_id_factory->create();
        $members = Members::create($admin_id, $this->member_id_factory);

        $new_member = new Member(
            $this->member_id_factory->create(),
            $member_id,
            Role::MEMBER
        );
        $updated_members = $members->addMember($new_member);
        $removed_members = $updated_members->removeMemberByUserAccountId($member_id);

        $this->assertFalse($removed_members->isMember($member_id));
    }

    public function test_removeMemberByUserAccountId_存在しないメンバーはエラー(): void
    {
        $admin_id = $this->user_account_id_factory->create();
        $non_member_id = $this->user_account_id_factory->create();
        $members = Members::create($admin_id, $this->member_id_factory);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Member not found');

        $members->removeMemberByUserAccountId($non_member_id);
    }

    public function test_toArray_配列に変換できる(): void
    {
        $admin_id = $this->user_account_id_factory->create();
        $members = Members::create($admin_id, $this->member_id_factory);

        $array = $members->toArray();

        $this->assertArrayHasKey('values', $array);
        $this->assertCount(1, $array['values']);
    }

    public function test_fromArray_配列から復元できる(): void
    {
        $admin_id = $this->user_account_id_factory->create();
        $member_id = $this->member_id_factory->create();

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
        $admin_id = $this->user_account_id_factory->create();
        $original_members = Members::create($admin_id, $this->member_id_factory);

        $array = $original_members->toArray();
        $restored_members = Members::fromArray($array, $this->validator);

        $this->assertTrue($restored_members->isMember($admin_id));
        $this->assertTrue($restored_members->isAdministrator($admin_id));
    }
}
