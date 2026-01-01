<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository;

use Akinoriakatsuka\CqrsEsExamplePhp\Query\Domain\ReadModel\MessageReadModel;

interface MessageQueryRepositoryInterface
{
    /**
     * メッセージをIDで取得（メンバーチェック付き）
     *
     * @param string $message_id メッセージID
     * @param string $user_account_id リクエスターのユーザーアカウントID
     * @return MessageReadModel|null メッセージ情報、またはnull（存在しない、メンバーでない、削除済みの場合）
     */
    public function findById(string $message_id, string $user_account_id): ?MessageReadModel;

    /**
     * グループチャットのメッセージ一覧を取得（メンバーチェック付き）
     *
     * @param string $group_chat_id グループチャットID
     * @param string $user_account_id リクエスターのユーザーアカウントID
     * @return MessageReadModel[] メッセージの配列（リクエスターがメンバーでない場合は空配列）
     */
    public function findByGroupChatId(string $group_chat_id, string $user_account_id): array;
}
