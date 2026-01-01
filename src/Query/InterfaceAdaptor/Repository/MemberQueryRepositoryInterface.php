<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository;

use Akinoriakatsuka\CqrsEsExamplePhp\Query\Domain\ReadModel\MemberReadModel;

interface MemberQueryRepositoryInterface
{
    /**
     * グループチャットの特定メンバーを取得
     *
     * @param string $group_chat_id グループチャットID
     * @param string $user_account_id 取得対象のユーザーアカウントID
     * @return MemberReadModel|null メンバー情報、またはnull（存在しない場合）
     */
    public function findByGroupChatIdAndUserAccountId(
        string $group_chat_id,
        string $user_account_id
    ): ?MemberReadModel;

    /**
     * グループチャットのメンバー一覧を取得（権限チェック付き）
     *
     * @param string $group_chat_id グループチャットID
     * @param string $requester_user_account_id リクエスターのユーザーアカウントID
     * @return MemberReadModel[] メンバーの配列（リクエスターがメンバーでない場合は空配列）
     */
    public function findByGroupChatId(
        string $group_chat_id,
        string $requester_user_account_id
    ): array;
}
