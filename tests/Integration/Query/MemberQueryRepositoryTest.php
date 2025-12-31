<?php

declare(strict_types=1);

namespace Tests\Integration\Query;

use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository\MemberQueryRepository;
use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\GroupChatDaoImpl;
use Tests\Integration\IntegrationTestCase;

class MemberQueryRepositoryTest extends IntegrationTestCase
{
    private MemberQueryRepository $repository;
    private GroupChatDaoImpl $dao;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new MemberQueryRepository($this->pdo);
        $this->dao = new GroupChatDaoImpl($this->pdo);
    }

    public function test_findByGroupChatIdAndUserAccountId_メンバーが存在する場合は取得できる(): void
    {
        // グループチャットとメンバーを作成
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $user_account_id = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $member_id = '01H42K4ABWQ5V2XQEP3A48VE1B';

        $this->dao->createGroupChat($group_chat_id, 'Test Group Chat', 'owner-id', '2024-01-01 00:00:00');
        $this->dao->addMember($member_id, $group_chat_id, $user_account_id, 'MEMBER', '2024-01-01 00:00:00');

        // 取得
        $member = $this->repository->findByGroupChatIdAndUserAccountId($group_chat_id, $user_account_id);

        $this->assertIsArray($member);
        $this->assertEquals($member_id, $member['id']);
        $this->assertEquals($group_chat_id, $member['group_chat_id']);
        $this->assertEquals($user_account_id, $member['user_account_id']);
        $this->assertEquals('MEMBER', $member['role']);
    }

    public function test_findByGroupChatIdAndUserAccountId_メンバーが存在しない場合はnullを返す(): void
    {
        // グループチャットを作成（メンバーは追加しない）
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $this->dao->createGroupChat($group_chat_id, 'Test Group Chat', 'owner-id', '2024-01-01 00:00:00');

        // 取得
        $member = $this->repository->findByGroupChatIdAndUserAccountId($group_chat_id, 'non-existent-user-id');

        $this->assertNull($member);
    }

    public function test_findByGroupChatId_メンバーの場合は一覧を取得できる(): void
    {
        // グループチャットと複数のメンバーを作成
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $requester_user_id = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $user_2_id = '01H42K4ABWQ5V2XQEP3A48VE2A';
        $user_3_id = '01H42K4ABWQ5V2XQEP3A48VE3A';

        $this->dao->createGroupChat($group_chat_id, 'Test Group Chat', 'owner-id', '2024-01-01 00:00:00');
        $this->dao->addMember('member-1', $group_chat_id, $requester_user_id, 'ADMINISTRATOR', '2024-01-01 00:00:00');
        $this->dao->addMember('member-2', $group_chat_id, $user_2_id, 'MEMBER', '2024-01-02 00:00:00');
        $this->dao->addMember('member-3', $group_chat_id, $user_3_id, 'MEMBER', '2024-01-03 00:00:00');

        // 取得
        $members = $this->repository->findByGroupChatId($group_chat_id, $requester_user_id);

        $this->assertCount(3, $members);
        $this->assertEquals($requester_user_id, $members[0]['user_account_id']);
        $this->assertEquals($user_2_id, $members[1]['user_account_id']);
        $this->assertEquals($user_3_id, $members[2]['user_account_id']);
    }

    public function test_findByGroupChatId_メンバーでない場合は空配列を返す(): void
    {
        // グループチャットとメンバーを作成
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $member_user_id = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $non_member_user_id = '01H42K4ABWQ5V2XQEP3A48VE2A';

        $this->dao->createGroupChat($group_chat_id, 'Test Group Chat', 'owner-id', '2024-01-01 00:00:00');
        $this->dao->addMember('member-1', $group_chat_id, $member_user_id, 'MEMBER', '2024-01-01 00:00:00');

        // メンバーでないユーザーで取得を試みる
        $members = $this->repository->findByGroupChatId($group_chat_id, $non_member_user_id);

        $this->assertEmpty($members);
    }

    public function test_findByGroupChatId_created_at昇順で取得できる(): void
    {
        // グループチャットと複数のメンバーを作成（日付順をランダムに）
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $user_1_id = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $user_2_id = '01H42K4ABWQ5V2XQEP3A48VE2A';
        $user_3_id = '01H42K4ABWQ5V2XQEP3A48VE3A';

        $this->dao->createGroupChat($group_chat_id, 'Test Group Chat', 'owner-id', '2024-01-01 00:00:00');
        $this->dao->addMember('member-2', $group_chat_id, $user_2_id, 'MEMBER', '2024-01-02 00:00:00');
        $this->dao->addMember('member-1', $group_chat_id, $user_1_id, 'MEMBER', '2024-01-01 00:00:00');
        $this->dao->addMember('member-3', $group_chat_id, $user_3_id, 'MEMBER', '2024-01-03 00:00:00');

        // 取得
        $members = $this->repository->findByGroupChatId($group_chat_id, $user_1_id);

        $this->assertCount(3, $members);
        $this->assertEquals($user_1_id, $members[0]['user_account_id']);
        $this->assertEquals($user_2_id, $members[1]['user_account_id']);
        $this->assertEquals($user_3_id, $members[2]['user_account_id']);
    }

    public function test_findByGroupChatId_グループチャットが存在しない場合は空配列を返す(): void
    {
        $members = $this->repository->findByGroupChatId('non-existent-group-id', 'user-id');

        $this->assertEmpty($members);
    }
}
