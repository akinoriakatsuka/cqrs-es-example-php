<?php

declare(strict_types=1);

namespace Tests\Integration\Query;

use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository\GroupChatQueryRepository;
use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\GroupChatDaoImpl;
use Tests\Integration\IntegrationTestCase;

class GroupChatQueryRepositoryTest extends IntegrationTestCase
{
    private GroupChatQueryRepository $repository;
    private GroupChatDaoImpl $dao;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new GroupChatQueryRepository($this->pdo);
        $this->dao = new GroupChatDaoImpl($this->pdo);
    }

    public function test_findById_メンバーの場合はグループチャットを取得できる(): void
    {
        // グループチャットとメンバーを作成
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $user_account_id = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $member_id = '01H42K4ABWQ5V2XQEP3A48VE1B';

        $this->dao->createGroupChat($group_chat_id, 'Test Group Chat', 'owner-id', '2024-01-01 00:00:00');
        $this->dao->addMember($member_id, $group_chat_id, $user_account_id, 'MEMBER', '2024-01-01 00:00:00');

        // 取得
        $group_chat = $this->repository->findById($group_chat_id, $user_account_id);

        $this->assertIsArray($group_chat);
        $this->assertEquals($group_chat_id, $group_chat['id']);
        $this->assertEquals('Test Group Chat', $group_chat['name']);
        $this->assertEquals(0, $group_chat['disabled']);
    }

    public function test_findById_メンバーでない場合はnullを返す(): void
    {
        // グループチャットを作成（別ユーザーをメンバーに追加）
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $other_user_id = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $member_id = '01H42K4ABWQ5V2XQEP3A48VE1B';

        $this->dao->createGroupChat($group_chat_id, 'Test Group Chat', 'owner-id', '2024-01-01 00:00:00');
        $this->dao->addMember($member_id, $group_chat_id, $other_user_id, 'MEMBER', '2024-01-01 00:00:00');

        // 異なるユーザーで取得を試みる
        $not_member_user_id = '01H42K4ABWQ5V2XQEP3A48VE2A';
        $group_chat = $this->repository->findById($group_chat_id, $not_member_user_id);

        $this->assertNull($group_chat);
    }

    public function test_findById_削除済みグループチャットはnullを返す(): void
    {
        // グループチャットとメンバーを作成
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $user_account_id = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $member_id = '01H42K4ABWQ5V2XQEP3A48VE1B';

        $this->dao->createGroupChat($group_chat_id, 'Test Group Chat', 'owner-id', '2024-01-01 00:00:00');
        $this->dao->addMember($member_id, $group_chat_id, $user_account_id, 'MEMBER', '2024-01-01 00:00:00');

        // グループチャットを削除
        $this->dao->deleteGroupChat($group_chat_id);

        // 取得
        $group_chat = $this->repository->findById($group_chat_id, $user_account_id);

        $this->assertNull($group_chat);
    }

    public function test_findById_存在しないグループチャットはnullを返す(): void
    {
        $group_chat = $this->repository->findById('non-existent-id', 'user-id');

        $this->assertNull($group_chat);
    }

    public function test_findByUserAccountId_所属するグループチャットを取得できる(): void
    {
        $user_account_id = '01H42K4ABWQ5V2XQEP3A48VE1A';

        // 複数のグループチャットを作成
        $group_chat_id_1 = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $group_chat_id_2 = '01H42K4ABWQ5V2XQEP3A48VE1Z';
        $group_chat_id_3 = '01H42K4ABWQ5V2XQEP3A48VE2Z';

        $this->dao->createGroupChat($group_chat_id_1, 'Group 1', 'owner-id', '2024-01-01 00:00:00');
        $this->dao->createGroupChat($group_chat_id_2, 'Group 2', 'owner-id', '2024-01-02 00:00:00');
        $this->dao->createGroupChat($group_chat_id_3, 'Group 3', 'owner-id', '2024-01-03 00:00:00');

        // ユーザーをグループ1と2のメンバーに追加
        $this->dao->addMember('member-1', $group_chat_id_1, $user_account_id, 'MEMBER', '2024-01-01 00:00:00');
        $this->dao->addMember('member-2', $group_chat_id_2, $user_account_id, 'MEMBER', '2024-01-02 00:00:00');

        // 取得
        $group_chats = $this->repository->findByUserAccountId($user_account_id);

        $this->assertCount(2, $group_chats);
        $this->assertEquals('Group 1', $group_chats[0]['name']);
        $this->assertEquals('Group 2', $group_chats[1]['name']);
    }

    public function test_findByUserAccountId_所属するグループがない場合は空配列を返す(): void
    {
        $group_chats = $this->repository->findByUserAccountId('non-member-user-id');

        $this->assertEmpty($group_chats);
    }

    public function test_findByUserAccountId_削除済みグループチャットは含まれない(): void
    {
        $user_account_id = '01H42K4ABWQ5V2XQEP3A48VE1A';

        // グループチャットを作成
        $group_chat_id_1 = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $group_chat_id_2 = '01H42K4ABWQ5V2XQEP3A48VE1Z';

        $this->dao->createGroupChat($group_chat_id_1, 'Group 1', 'owner-id', '2024-01-01 00:00:00');
        $this->dao->createGroupChat($group_chat_id_2, 'Group 2', 'owner-id', '2024-01-02 00:00:00');

        // ユーザーを両方のメンバーに追加
        $this->dao->addMember('member-1', $group_chat_id_1, $user_account_id, 'MEMBER', '2024-01-01 00:00:00');
        $this->dao->addMember('member-2', $group_chat_id_2, $user_account_id, 'MEMBER', '2024-01-02 00:00:00');

        // グループ1を削除
        $this->dao->deleteGroupChat($group_chat_id_1);

        // 取得
        $group_chats = $this->repository->findByUserAccountId($user_account_id);

        $this->assertCount(1, $group_chats);
        $this->assertEquals('Group 2', $group_chats[0]['name']);
    }

    public function test_findByUserAccountId_created_at昇順で取得できる(): void
    {
        $user_account_id = '01H42K4ABWQ5V2XQEP3A48VE1A';

        // グループチャットを作成（日付順をランダムに）
        $group_chat_id_1 = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $group_chat_id_2 = '01H42K4ABWQ5V2XQEP3A48VE1Z';
        $group_chat_id_3 = '01H42K4ABWQ5V2XQEP3A48VE2Z';

        $this->dao->createGroupChat($group_chat_id_2, 'Group 2', 'owner-id', '2024-01-02 00:00:00');
        $this->dao->createGroupChat($group_chat_id_1, 'Group 1', 'owner-id', '2024-01-01 00:00:00');
        $this->dao->createGroupChat($group_chat_id_3, 'Group 3', 'owner-id', '2024-01-03 00:00:00');

        // ユーザーを全てのメンバーに追加
        $this->dao->addMember('member-1', $group_chat_id_1, $user_account_id, 'MEMBER', '2024-01-01 00:00:00');
        $this->dao->addMember('member-2', $group_chat_id_2, $user_account_id, 'MEMBER', '2024-01-02 00:00:00');
        $this->dao->addMember('member-3', $group_chat_id_3, $user_account_id, 'MEMBER', '2024-01-03 00:00:00');

        // 取得
        $group_chats = $this->repository->findByUserAccountId($user_account_id);

        $this->assertCount(3, $group_chats);
        $this->assertEquals('Group 1', $group_chats[0]['name']);
        $this->assertEquals('Group 2', $group_chats[1]['name']);
        $this->assertEquals('Group 3', $group_chats[2]['name']);
    }
}
