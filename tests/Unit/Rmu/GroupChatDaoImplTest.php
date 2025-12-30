<?php

declare(strict_types=1);

namespace Tests\Unit\Rmu;

use App\Rmu\GroupChatDaoImpl;
use PDO;
use PHPUnit\Framework\TestCase;

class GroupChatDaoImplTest extends TestCase
{
    private PDO $pdo;
    private GroupChatDaoImpl $dao;

    protected function setUp(): void
    {
        // インメモリSQLiteを使用
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // テーブル作成
        $this->pdo->exec('
            CREATE TABLE group_chats (
                id TEXT PRIMARY KEY,
                disabled INTEGER NOT NULL DEFAULT 0,
                name TEXT NOT NULL,
                owner_id TEXT NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL
            )
        ');

        $this->pdo->exec('
            CREATE TABLE members (
                id TEXT PRIMARY KEY,
                group_chat_id TEXT NOT NULL,
                user_account_id TEXT NOT NULL,
                role TEXT NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE(group_chat_id, user_account_id)
            )
        ');

        $this->pdo->exec('
            CREATE TABLE messages (
                id TEXT PRIMARY KEY,
                disabled INTEGER NOT NULL DEFAULT 0,
                group_chat_id TEXT NOT NULL,
                user_account_id TEXT NOT NULL,
                text TEXT NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL
            )
        ');

        $this->dao = new GroupChatDaoImpl($this->pdo);
    }

    protected function tearDown(): void
    {
        unset($this->pdo);
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
}
