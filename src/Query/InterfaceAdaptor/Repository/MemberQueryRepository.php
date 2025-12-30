<?php

declare(strict_types=1);

namespace App\Query\InterfaceAdaptor\Repository;

use PDO;

class MemberQueryRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * グループチャットの特定メンバーを取得
     *
     * @param string $group_chat_id グループチャットID
     * @param string $user_account_id 取得対象のユーザーアカウントID
     * @return array|null メンバー情報、またはnull（存在しない場合）
     */
    public function findByGroupChatIdAndUserAccountId(
        string $group_chat_id,
        string $user_account_id
    ): ?array {
        $sql = <<<SQL
            SELECT m.*
            FROM members m
            WHERE m.group_chat_id = :group_chat_id
              AND m.user_account_id = :user_account_id
            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'group_chat_id' => $group_chat_id,
            'user_account_id' => $user_account_id,
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * グループチャットのメンバー一覧を取得（権限チェック付き）
     *
     * @param string $group_chat_id グループチャットID
     * @param string $requester_user_account_id リクエスターのユーザーアカウントID
     * @return array メンバーの配列（リクエスターがメンバーでない場合は空配列）
     */
    public function findByGroupChatId(
        string $group_chat_id,
        string $requester_user_account_id
    ): array {
        // リクエスターがメンバーかチェック
        $check_sql = <<<SQL
            SELECT COUNT(*) as count
            FROM members
            WHERE group_chat_id = :group_chat_id
              AND user_account_id = :requester_user_account_id
        SQL;

        $check_stmt = $this->pdo->prepare($check_sql);
        $check_stmt->execute([
            'group_chat_id' => $group_chat_id,
            'requester_user_account_id' => $requester_user_account_id,
        ]);

        $is_member = (int)$check_stmt->fetchColumn() > 0;

        if (!$is_member) {
            return [];
        }

        // メンバー一覧を取得
        $sql = <<<SQL
            SELECT m.*
            FROM members m
            WHERE m.group_chat_id = :group_chat_id
            ORDER BY m.created_at ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'group_chat_id' => $group_chat_id,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
