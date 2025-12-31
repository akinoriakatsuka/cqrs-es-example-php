<?php

declare(strict_types=1);

namespace Tests\Integration\Query;

use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository\MessageQueryRepository;
use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\GroupChatDaoImpl;
use Tests\Integration\IntegrationTestCase;

class MessageQueryRepositoryTest extends IntegrationTestCase
{
    private MessageQueryRepository $repository;
    private GroupChatDaoImpl $dao;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new MessageQueryRepository($this->pdo);
        $this->dao = new GroupChatDaoImpl($this->pdo);
    }

    public function test_findById_メンバーの場合はメッセージを取得できる(): void
    {
        // グループチャット、メンバー、メッセージを作成
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $user_account_id = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $message_id = '01H42K4ABWQ5V2XQEP3A48VE2A';

        $this->dao->createGroupChat($group_chat_id, 'Test Group Chat', 'owner-id', '2024-01-01 00:00:00');
        $this->dao->addMember('member-1', $group_chat_id, $user_account_id, 'MEMBER', '2024-01-01 00:00:00');
        $this->dao->postMessage($message_id, $group_chat_id, $user_account_id, 'Hello World', '2024-01-01 00:00:00');

        // 取得
        $message = $this->repository->findById($message_id, $user_account_id);

        $this->assertIsArray($message);
        $this->assertEquals($message_id, $message['id']);
        $this->assertEquals($group_chat_id, $message['group_chat_id']);
        $this->assertEquals($user_account_id, $message['user_account_id']);
        $this->assertEquals('Hello World', $message['text']);
        $this->assertEquals(0, $message['disabled']);
    }

    public function test_findById_メンバーでない場合はnullを返す(): void
    {
        // グループチャット、メンバー、メッセージを作成
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $member_user_id = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $non_member_user_id = '01H42K4ABWQ5V2XQEP3A48VE2A';
        $message_id = '01H42K4ABWQ5V2XQEP3A48VE3A';

        $this->dao->createGroupChat($group_chat_id, 'Test Group Chat', 'owner-id', '2024-01-01 00:00:00');
        $this->dao->addMember('member-1', $group_chat_id, $member_user_id, 'MEMBER', '2024-01-01 00:00:00');
        $this->dao->postMessage($message_id, $group_chat_id, $member_user_id, 'Hello World', '2024-01-01 00:00:00');

        // メンバーでないユーザーで取得を試みる
        $message = $this->repository->findById($message_id, $non_member_user_id);

        $this->assertNull($message);
    }

    public function test_findById_削除済みメッセージはnullを返す(): void
    {
        // グループチャット、メンバー、メッセージを作成
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $user_account_id = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $message_id = '01H42K4ABWQ5V2XQEP3A48VE2A';

        $this->dao->createGroupChat($group_chat_id, 'Test Group Chat', 'owner-id', '2024-01-01 00:00:00');
        $this->dao->addMember('member-1', $group_chat_id, $user_account_id, 'MEMBER', '2024-01-01 00:00:00');
        $this->dao->postMessage($message_id, $group_chat_id, $user_account_id, 'Hello World', '2024-01-01 00:00:00');

        // メッセージを削除
        $this->dao->deleteMessage($message_id);

        // 取得
        $message = $this->repository->findById($message_id, $user_account_id);

        $this->assertNull($message);
    }

    public function test_findById_存在しないメッセージはnullを返す(): void
    {
        $message = $this->repository->findById('non-existent-id', 'user-id');

        $this->assertNull($message);
    }

    public function test_findByGroupChatId_メンバーの場合はメッセージ一覧を取得できる(): void
    {
        // グループチャット、メンバー、複数のメッセージを作成
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $user_account_id = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $message_id_1 = '01H42K4ABWQ5V2XQEP3A48VE2A';
        $message_id_2 = '01H42K4ABWQ5V2XQEP3A48VE3A';
        $message_id_3 = '01H42K4ABWQ5V2XQEP3A48VE4A';

        $this->dao->createGroupChat($group_chat_id, 'Test Group Chat', 'owner-id', '2024-01-01 00:00:00');
        $this->dao->addMember('member-1', $group_chat_id, $user_account_id, 'MEMBER', '2024-01-01 00:00:00');
        $this->dao->postMessage($message_id_1, $group_chat_id, $user_account_id, 'Message 1', '2024-01-01 01:00:00');
        $this->dao->postMessage($message_id_2, $group_chat_id, $user_account_id, 'Message 2', '2024-01-01 02:00:00');
        $this->dao->postMessage($message_id_3, $group_chat_id, $user_account_id, 'Message 3', '2024-01-01 03:00:00');

        // 取得
        $messages = $this->repository->findByGroupChatId($group_chat_id, $user_account_id);

        $this->assertCount(3, $messages);
        $this->assertEquals('Message 1', $messages[0]['text']);
        $this->assertEquals('Message 2', $messages[1]['text']);
        $this->assertEquals('Message 3', $messages[2]['text']);
    }

    public function test_findByGroupChatId_メンバーでない場合は空配列を返す(): void
    {
        // グループチャット、メンバー、メッセージを作成
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $member_user_id = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $non_member_user_id = '01H42K4ABWQ5V2XQEP3A48VE2A';
        $message_id = '01H42K4ABWQ5V2XQEP3A48VE3A';

        $this->dao->createGroupChat($group_chat_id, 'Test Group Chat', 'owner-id', '2024-01-01 00:00:00');
        $this->dao->addMember('member-1', $group_chat_id, $member_user_id, 'MEMBER', '2024-01-01 00:00:00');
        $this->dao->postMessage($message_id, $group_chat_id, $member_user_id, 'Hello World', '2024-01-01 00:00:00');

        // メンバーでないユーザーで取得を試みる
        $messages = $this->repository->findByGroupChatId($group_chat_id, $non_member_user_id);

        $this->assertEmpty($messages);
    }

    public function test_findByGroupChatId_削除済みメッセージは含まれない(): void
    {
        // グループチャット、メンバー、複数のメッセージを作成
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $user_account_id = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $message_id_1 = '01H42K4ABWQ5V2XQEP3A48VE2A';
        $message_id_2 = '01H42K4ABWQ5V2XQEP3A48VE3A';
        $message_id_3 = '01H42K4ABWQ5V2XQEP3A48VE4A';

        $this->dao->createGroupChat($group_chat_id, 'Test Group Chat', 'owner-id', '2024-01-01 00:00:00');
        $this->dao->addMember('member-1', $group_chat_id, $user_account_id, 'MEMBER', '2024-01-01 00:00:00');
        $this->dao->postMessage($message_id_1, $group_chat_id, $user_account_id, 'Message 1', '2024-01-01 01:00:00');
        $this->dao->postMessage($message_id_2, $group_chat_id, $user_account_id, 'Message 2', '2024-01-01 02:00:00');
        $this->dao->postMessage($message_id_3, $group_chat_id, $user_account_id, 'Message 3', '2024-01-01 03:00:00');

        // メッセージ2を削除
        $this->dao->deleteMessage($message_id_2);

        // 取得
        $messages = $this->repository->findByGroupChatId($group_chat_id, $user_account_id);

        $this->assertCount(2, $messages);
        $this->assertEquals('Message 1', $messages[0]['text']);
        $this->assertEquals('Message 3', $messages[1]['text']);
    }

    public function test_findByGroupChatId_created_at昇順で取得できる(): void
    {
        // グループチャット、メンバー、複数のメッセージを作成（日付順をランダムに）
        $group_chat_id = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $user_account_id = '01H42K4ABWQ5V2XQEP3A48VE1A';
        $message_id_1 = '01H42K4ABWQ5V2XQEP3A48VE2A';
        $message_id_2 = '01H42K4ABWQ5V2XQEP3A48VE3A';
        $message_id_3 = '01H42K4ABWQ5V2XQEP3A48VE4A';

        $this->dao->createGroupChat($group_chat_id, 'Test Group Chat', 'owner-id', '2024-01-01 00:00:00');
        $this->dao->addMember('member-1', $group_chat_id, $user_account_id, 'MEMBER', '2024-01-01 00:00:00');
        $this->dao->postMessage($message_id_2, $group_chat_id, $user_account_id, 'Message 2', '2024-01-01 02:00:00');
        $this->dao->postMessage($message_id_1, $group_chat_id, $user_account_id, 'Message 1', '2024-01-01 01:00:00');
        $this->dao->postMessage($message_id_3, $group_chat_id, $user_account_id, 'Message 3', '2024-01-01 03:00:00');

        // 取得
        $messages = $this->repository->findByGroupChatId($group_chat_id, $user_account_id);

        $this->assertCount(3, $messages);
        $this->assertEquals('Message 1', $messages[0]['text']);
        $this->assertEquals('Message 2', $messages[1]['text']);
        $this->assertEquals('Message 3', $messages[2]['text']);
    }

    public function test_findByGroupChatId_グループチャットが存在しない場合は空配列を返す(): void
    {
        $messages = $this->repository->findByGroupChatId('non-existent-group-id', 'user-id');

        $this->assertEmpty($messages);
    }
}
