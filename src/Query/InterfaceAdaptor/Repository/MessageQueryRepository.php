<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository;

use PDO;

class MessageQueryRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * メッセージをIDで取得（メンバーチェック付き）
     *
     * @param string $message_id メッセージID
     * @param string $user_account_id リクエスターのユーザーアカウントID
     * @return array|null メッセージ情報、またはnull（存在しない、メンバーでない、削除済みの場合）
     */
    public function findById(string $message_id, string $user_account_id): ?array
    {
        $sql = <<<SQL
            SELECT msg.*
            FROM messages msg
            INNER JOIN members m ON msg.group_chat_id = m.group_chat_id
            WHERE msg.id = :message_id
              AND m.user_account_id = :user_account_id
              AND msg.disabled = 0
            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'message_id' => $message_id,
            'user_account_id' => $user_account_id,
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * グループチャットのメッセージ一覧を取得（メンバーチェック付き）
     *
     * @param string $group_chat_id グループチャットID
     * @param string $user_account_id リクエスターのユーザーアカウントID
     * @return array メッセージの配列（リクエスターがメンバーでない場合は空配列）
     */
    public function findByGroupChatId(string $group_chat_id, string $user_account_id): array
    {
        // リクエスターがメンバーかチェック
        $check_sql = <<<SQL
            SELECT COUNT(*) as count
            FROM members
            WHERE group_chat_id = :group_chat_id
              AND user_account_id = :user_account_id
        SQL;

        $check_stmt = $this->pdo->prepare($check_sql);
        $check_stmt->execute([
            'group_chat_id' => $group_chat_id,
            'user_account_id' => $user_account_id,
        ]);

        $is_member = (int)$check_stmt->fetchColumn() > 0;

        if (!$is_member) {
            return [];
        }

        // メッセージ一覧を取得
        $sql = <<<SQL
            SELECT msg.*
            FROM messages msg
            WHERE msg.group_chat_id = :group_chat_id
              AND msg.disabled = 0
            ORDER BY msg.created_at ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'group_chat_id' => $group_chat_id,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
