<?php

declare(strict_types=1);

namespace App\Rmu;

/**
 * グループチャット読み取りモデルDAO（インターフェース）
 */
interface GroupChatDao
{
    /**
     * グループチャットを作成
     *
     * @param string $id グループチャットID
     * @param string $name グループチャット名
     * @param string $ownerId オーナー（ADMINISTRATOR）のユーザーアカウントID
     * @param string $createdAt 作成日時（Y-m-d H:i:s形式）
     * @return void
     */
    public function createGroupChat(
        string $id,
        string $name,
        string $ownerId,
        string $createdAt
    ): void;

    /**
     * グループチャット名を変更
     *
     * @param string $id グループチャットID
     * @param string $name 新しい名前
     * @return void
     */
    public function renameGroupChat(string $id, string $name): void;

    /**
     * グループチャットを削除（論理削除）
     *
     * @param string $id グループチャットID
     * @return void
     */
    public function deleteGroupChat(string $id): void;

    /**
     * メンバーを追加
     *
     * @param string $id メンバーID
     * @param string $groupChatId グループチャットID
     * @param string $userAccountId ユーザーアカウントID
     * @param string $role ロール（ADMINISTRATOR | MEMBER）
     * @param string $createdAt 作成日時（Y-m-d H:i:s形式）
     * @return void
     */
    public function addMember(
        string $id,
        string $groupChatId,
        string $userAccountId,
        string $role,
        string $createdAt
    ): void;

    /**
     * メンバーを削除
     *
     * @param string $groupChatId グループチャットID
     * @param string $userAccountId ユーザーアカウントID
     * @return void
     */
    public function removeMember(string $groupChatId, string $userAccountId): void;

    /**
     * メッセージを投稿
     *
     * @param string $id メッセージID
     * @param string $groupChatId グループチャットID
     * @param string $userAccountId ユーザーアカウントID
     * @param string $text メッセージテキスト
     * @param string $createdAt 作成日時（Y-m-d H:i:s形式）
     * @return void
     */
    public function postMessage(
        string $id,
        string $groupChatId,
        string $userAccountId,
        string $text,
        string $createdAt
    ): void;

    /**
     * メッセージを編集
     *
     * @param string $id メッセージID
     * @param string $text 新しいテキスト
     * @return void
     */
    public function editMessage(string $id, string $text): void;

    /**
     * メッセージを削除（論理削除）
     *
     * @param string $id メッセージID
     * @return void
     */
    public function deleteMessage(string $id): void;
}
