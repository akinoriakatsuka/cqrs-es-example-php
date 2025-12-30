<?php

declare(strict_types=1);

namespace App\Rmu;

use PDO;

/**
 * グループチャット読み取りモデルDAO（実装）
 */
class GroupChatDaoImpl implements GroupChatDao
{
    public function __construct(private PDO $pdo) {}

    /**
     * グループチャットを作成
     */
    public function createGroupChat(
        string $id,
        string $name,
        string $ownerId,
        string $createdAt
    ): void {
        // 既存レコードを確認
        $stmt = $this->pdo->prepare('SELECT id FROM group_chats WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $exists = $stmt->fetchColumn();

        if ($exists === false) {
            // 存在しない場合はINSERT
            $stmt = $this->pdo->prepare(
                'INSERT INTO group_chats (id, disabled, name, owner_id, created_at, updated_at)
                 VALUES (:id, 0, :name, :owner_id, :created_at, :updated_at)'
            );

            $stmt->execute([
                'id' => $id,
                'name' => $name,
                'owner_id' => $ownerId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        } else {
            // 存在する場合はUPDATE
            $stmt = $this->pdo->prepare(
                'UPDATE group_chats SET name = :name, owner_id = :owner_id, updated_at = :updated_at WHERE id = :id'
            );

            $stmt->execute([
                'id' => $id,
                'name' => $name,
                'owner_id' => $ownerId,
                'updated_at' => $createdAt,
            ]);
        }
    }

    public function renameGroupChat(string $id, string $name): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE group_chats SET name = :name, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'name' => $name,
        ]);
    }

    public function deleteGroupChat(string $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE group_chats SET disabled = 1, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    public function addMember(
        string $id,
        string $groupChatId,
        string $userAccountId,
        string $role,
        string $createdAt
    ): void {
        // 既存レコードを確認（idでチェック）
        $stmt = $this->pdo->prepare('SELECT id FROM members WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $exists = $stmt->fetchColumn();

        if ($exists === false) {
            // 存在しない場合はINSERT
            $stmt = $this->pdo->prepare(
                'INSERT INTO members (id, group_chat_id, user_account_id, role, created_at, updated_at)
                 VALUES (:id, :group_chat_id, :user_account_id, :role, :created_at, :updated_at)'
            );

            $stmt->execute([
                'id' => $id,
                'group_chat_id' => $groupChatId,
                'user_account_id' => $userAccountId,
                'role' => $role,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        } else {
            // 存在する場合はUPDATE
            $stmt = $this->pdo->prepare(
                'UPDATE members SET role = :role, updated_at = :updated_at WHERE id = :id'
            );

            $stmt->execute([
                'id' => $id,
                'role' => $role,
                'updated_at' => $createdAt,
            ]);
        }
    }

    public function removeMember(string $groupChatId, string $userAccountId): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM members WHERE group_chat_id = :group_chat_id AND user_account_id = :user_account_id'
        );
        $stmt->execute([
            'group_chat_id' => $groupChatId,
            'user_account_id' => $userAccountId,
        ]);
    }

    public function postMessage(
        string $id,
        string $groupChatId,
        string $userAccountId,
        string $text,
        string $createdAt
    ): void {
        // 既存レコードを確認
        $stmt = $this->pdo->prepare('SELECT id FROM messages WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $exists = $stmt->fetchColumn();

        if ($exists === false) {
            // 存在しない場合はINSERT
            $stmt = $this->pdo->prepare(
                'INSERT INTO messages (id, disabled, group_chat_id, user_account_id, text, created_at, updated_at)
                 VALUES (:id, 0, :group_chat_id, :user_account_id, :text, :created_at, :updated_at)'
            );

            $stmt->execute([
                'id' => $id,
                'group_chat_id' => $groupChatId,
                'user_account_id' => $userAccountId,
                'text' => $text,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        } else {
            // 存在する場合はUPDATE
            $stmt = $this->pdo->prepare(
                'UPDATE messages SET text = :text, updated_at = :updated_at WHERE id = :id'
            );

            $stmt->execute([
                'id' => $id,
                'text' => $text,
                'updated_at' => $createdAt,
            ]);
        }
    }

    public function editMessage(string $id, string $text): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE messages SET text = :text, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'text' => $text,
        ]);
    }

    public function deleteMessage(string $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE messages SET disabled = 1, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }
}
