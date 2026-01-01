<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository;

use Akinoriakatsuka\CqrsEsExamplePhp\Query\Domain\ReadModel\GroupChatReadModel;

interface GroupChatQueryRepositoryInterface
{
    /**
     * グループチャットをIDで取得（メンバーチェック付き）
     *
     * @param string $group_chat_id グループチャットID
     * @param string $user_account_id リクエスターのユーザーアカウントID
     * @return GroupChatReadModel|null グループチャット情報、またはnull（存在しない、またはメンバーでない場合）
     */
    public function findById(string $group_chat_id, string $user_account_id): ?GroupChatReadModel;

    /**
     * ユーザーが所属するグループチャット一覧を取得
     *
     * @param string $user_account_id ユーザーアカウントID
     * @return GroupChatReadModel[] グループチャットの配列
     */
    public function findByUserAccountId(string $user_account_id): array;
}
