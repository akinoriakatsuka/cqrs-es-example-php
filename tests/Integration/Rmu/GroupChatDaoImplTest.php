<?php

declare(strict_types=1);

namespace Tests\Integration\Rmu;

use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\GroupChatDaoImpl;
use PDO;
use Tests\Integration\IntegrationTestCase;

class GroupChatDaoImplTest extends IntegrationTestCase
{
    private GroupChatDaoImpl $dao;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dao = new GroupChatDaoImpl($this->pdo);
    }

    public function test_createGroupChat_新しいグループチャットを挿入できる(): void
    {
        $id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $name = 'Test Group Chat';
        $ownerId = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $createdAt = '2024-01-01 00:00:00';

        $this->dao->createGroupChat($id, $name, $ownerId, $createdAt);

        // データベースから確認
        $stmt = $this->pdo->prepare('SELECT * FROM group_chats WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($row);
        $this->assertEquals($id, $row['id']);
        $this->assertEquals($name, $row['name']);
        $this->assertEquals($ownerId, $row['owner_id']);
        $this->assertEquals(0, $row['disabled']);
        $this->assertEquals($createdAt, $row['created_at']);
        $this->assertEquals($createdAt, $row['updated_at']);
    }

    public function test_createGroupChat_既存のグループチャットを更新できる(): void
    {
        $id = '01H42K4ABWQ5V2XQEP3A48VE0Z';

        // 初回挿入
        $this->dao->createGroupChat($id, 'Old Name', 'old-owner-id', '2024-01-01 00:00:00');

        // 2回目の挿入（同じID）
        $this->dao->createGroupChat($id, 'New Name', 'new-owner-id', '2024-01-02 00:00:00');

        // レコードは1件のまま
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM group_chats');
        $count = $stmt->fetchColumn();
        $this->assertEquals(1, $count);

        // 更新されていることを確認
        $stmt = $this->pdo->prepare('SELECT * FROM group_chats WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('New Name', $row['name']);
        $this->assertEquals('new-owner-id', $row['owner_id']);
        $this->assertEquals('2024-01-02 00:00:00', $row['updated_at']);
    }

    public function test_renameGroupChat_グループチャット名を変更できる(): void
    {
        $id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $this->dao->createGroupChat($id, 'Old Name', 'owner-id', '2024-01-01 00:00:00');

        $this->dao->renameGroupChat($id, 'New Name');

        $stmt = $this->pdo->prepare('SELECT name FROM group_chats WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $name = $stmt->fetchColumn();

        $this->assertEquals('New Name', $name);
    }

    public function test_deleteGroupChat_グループチャットを削除できる(): void
    {
        $id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $this->dao->createGroupChat($id, 'Test Group', 'owner-id', '2024-01-01 00:00:00');

        $this->dao->deleteGroupChat($id);

        $stmt = $this->pdo->prepare('SELECT disabled FROM group_chats WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $disabled = $stmt->fetchColumn();

        $this->assertEquals(1, $disabled);
    }

    public function test_addMember_新しいメンバーを追加できる(): void
    {
        // 先にgroup_chatを作成（外部キー制約のため）
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $this->dao->createGroupChat($group_chat_id, 'Test Group', 'owner-id', '2024-01-01 00:00:00');

        $id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $user_account_id = '01H42K4ABWQ5V2XQEP3A48VE1B';
        $role = 'MEMBER';
        $created_at = '2024-01-01 00:00:00';

        $this->dao->addMember($id, $group_chat_id, $user_account_id, $role, $created_at);

        $stmt = $this->pdo->prepare('SELECT * FROM members WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($row);
        $this->assertEquals($id, $row['id']);
        $this->assertEquals($group_chat_id, $row['group_chat_id']);
        $this->assertEquals($user_account_id, $row['user_account_id']);
        $this->assertEquals($role, $row['role']);
    }

    public function test_addMember_既存のメンバーを更新できる(): void
    {
        // 先にgroup_chatを作成（外部キー制約のため）
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $this->dao->createGroupChat($group_chat_id, 'Test Group', 'owner-id', '2024-01-01 00:00:00');

        $id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $user_account_id = '01H42K4ABWQ5V2XQEP3A48VE1B';

        $this->dao->addMember($id, $group_chat_id, $user_account_id, 'MEMBER', '2024-01-01 00:00:00');
        $this->dao->addMember($id, $group_chat_id, $user_account_id, 'ADMINISTRATOR', '2024-01-02 00:00:00');

        $stmt = $this->pdo->prepare('SELECT role FROM members WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $role = $stmt->fetchColumn();

        $this->assertEquals('ADMINISTRATOR', $role);
    }

    public function test_removeMember_メンバーを削除できる(): void
    {
        // 先にgroup_chatを作成（外部キー制約のため）
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $this->dao->createGroupChat($group_chat_id, 'Test Group', 'owner-id', '2024-01-01 00:00:00');

        $id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $user_account_id = '01H42K4ABWQ5V2XQEP3A48VE1B';

        $this->dao->addMember($id, $group_chat_id, $user_account_id, 'MEMBER', '2024-01-01 00:00:00');
        $this->dao->removeMember($group_chat_id, $user_account_id);

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM members WHERE group_chat_id = :group_chat_id AND user_account_id = :user_account_id');
        $stmt->execute(['group_chat_id' => $group_chat_id, 'user_account_id' => $user_account_id]);
        $count = $stmt->fetchColumn();

        $this->assertEquals(0, $count);
    }

    public function test_postMessage_新しいメッセージを投稿できる(): void
    {
        // 先にgroup_chatを作成（外部キー制約のため）
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $this->dao->createGroupChat($group_chat_id, 'Test Group', 'owner-id', '2024-01-01 00:00:00');

        $id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $user_account_id = '01H42K4ABWQ5V2XQEP3A48VE1B';
        $text = 'Hello World';
        $created_at = '2024-01-01 00:00:00';

        $this->dao->postMessage($id, $group_chat_id, $user_account_id, $text, $created_at);

        $stmt = $this->pdo->prepare('SELECT * FROM messages WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($row);
        $this->assertEquals($id, $row['id']);
        $this->assertEquals($group_chat_id, $row['group_chat_id']);
        $this->assertEquals($user_account_id, $row['user_account_id']);
        $this->assertEquals($text, $row['text']);
        $this->assertEquals(0, $row['disabled']);
    }

    public function test_postMessage_既存のメッセージを更新できる(): void
    {
        // 先にgroup_chatを作成（外部キー制約のため）
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $this->dao->createGroupChat($group_chat_id, 'Test Group', 'owner-id', '2024-01-01 00:00:00');

        $id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $user_account_id = '01H42K4ABWQ5V2XQEP3A48VE1B';

        $this->dao->postMessage($id, $group_chat_id, $user_account_id, 'Old Text', '2024-01-01 00:00:00');
        $this->dao->postMessage($id, $group_chat_id, $user_account_id, 'New Text', '2024-01-02 00:00:00');

        $stmt = $this->pdo->prepare('SELECT text FROM messages WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $text = $stmt->fetchColumn();

        $this->assertEquals('New Text', $text);
    }

    public function test_editMessage_メッセージを編集できる(): void
    {
        // 先にgroup_chatを作成（外部キー制約のため）
        $group_chat_id = 'group-id';
        $this->dao->createGroupChat($group_chat_id, 'Test Group', 'owner-id', '2024-01-01 00:00:00');

        $id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $this->dao->postMessage($id, $group_chat_id, 'user-id', 'Original Text', '2024-01-01 00:00:00');

        $this->dao->editMessage($id, 'Edited Text');

        $stmt = $this->pdo->prepare('SELECT text FROM messages WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $text = $stmt->fetchColumn();

        $this->assertEquals('Edited Text', $text);
    }

    public function test_deleteMessage_メッセージを削除できる(): void
    {
        // 先にgroup_chatを作成（外部キー制約のため）
        $group_chat_id = 'group-id';
        $this->dao->createGroupChat($group_chat_id, 'Test Group', 'owner-id', '2024-01-01 00:00:00');

        $id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $this->dao->postMessage($id, $group_chat_id, 'user-id', 'Test Message', '2024-01-01 00:00:00');

        $this->dao->deleteMessage($id);

        $stmt = $this->pdo->prepare('SELECT disabled FROM messages WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $disabled = $stmt->fetchColumn();

        $this->assertEquals(1, $disabled);
    }
}
