<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository;

use PDO;

class GroupChatQueryRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * グループチャットをIDで取得（メンバーチェック付き）
     *
     * @param string $group_chat_id グループチャットID
     * @param string $user_account_id リクエスターのユーザーアカウントID
     * @return array|null グループチャット情報、またはnull（存在しない、またはメンバーでない場合）
     */
    public function findById(string $group_chat_id, string $user_account_id): ?array
    {
        $sql = <<<SQL
            SELECT gc.*
            FROM group_chats gc
            INNER JOIN members m ON gc.id = m.group_chat_id
            WHERE gc.id = :group_chat_id
              AND m.user_account_id = :user_account_id
              AND gc.disabled = 0
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
     * ユーザーが所属するグループチャット一覧を取得
     *
     * @param string $user_account_id ユーザーアカウントID
     * @return array グループチャットの配列
     */
    public function findByUserAccountId(string $user_account_id): array
    {
        $sql = <<<SQL
            SELECT gc.*
            FROM group_chats gc
            INNER JOIN members m ON gc.id = m.group_chat_id
            WHERE m.user_account_id = :user_account_id
              AND gc.disabled = 0
            ORDER BY gc.created_at ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'user_account_id' => $user_account_id,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
