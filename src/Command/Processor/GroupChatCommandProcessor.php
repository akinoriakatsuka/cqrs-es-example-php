<?php

declare(strict_types=1);

namespace App\Command\Processor;

use App\Command\Domain\GroupChat;
use App\Command\Domain\Models\GroupChatId;
use App\Command\Domain\Models\GroupChatName;
use App\Command\Domain\Models\MemberId;
use App\Command\Domain\Models\MessageId;
use App\Command\Domain\Models\Role;
use App\Command\Domain\Models\UserAccountId;
use App\Command\InterfaceAdaptor\Repository\GroupChatRepository;
use App\Infrastructure\Ulid\UlidGenerator;
use App\Infrastructure\Ulid\UlidValidator;

class GroupChatCommandProcessor
{
    public function __construct(
        private GroupChatRepository $repository,
        private UlidValidator $validator,
        private UlidGenerator $generator
    ) {
    }

    public function createGroupChat(
        string $name,
        string $executor_id
    ): string {
        // プレフィックスを削除
        $executor_id_ulid = preg_replace('/^UserAccount-/', '', $executor_id);

        // 値オブジェクト生成
        $id = GroupChatId::generate($this->generator);
        $group_chat_name = new GroupChatName($name);
        $executor_user_account_id = UserAccountId::fromString($executor_id_ulid, $this->validator);

        // 集約作成
        $pair = GroupChat::create(
            $id,
            $group_chat_name,
            $executor_user_account_id,
            $this->generator
        );

        // 保存
        $this->repository->store($pair->getEvent(), $pair->getGroupChat());

        return $id->toString();
    }

    public function renameGroupChat(
        string $group_chat_id,
        string $name,
        string $executor_id
    ): void {
        // プレフィックスを削除
        $executor_id_ulid = preg_replace('/^UserAccount-/', '', $executor_id);

        // 値オブジェクト生成
        $id = GroupChatId::fromString($group_chat_id, $this->validator);
        $group_chat_name = new GroupChatName($name);
        $executor_user_account_id = UserAccountId::fromString($executor_id_ulid, $this->validator);

        // 集約取得
        $group_chat = $this->repository->findById($id);
        if ($group_chat === null) {
            throw new \DomainException('Group chat not found');
        }

        // リネーム
        $pair = $group_chat->rename(
            $group_chat_name,
            $executor_user_account_id,
            $this->generator
        );

        // 保存
        $this->repository->store($pair->getEvent(), $pair->getGroupChat());
    }

    public function deleteGroupChat(
        string $group_chat_id,
        string $executor_id
    ): void {
        // プレフィックスを削除
        $executor_id_ulid = preg_replace('/^UserAccount-/', '', $executor_id);

        // 値オブジェクト生成
        $id = GroupChatId::fromString($group_chat_id, $this->validator);
        $executor_user_account_id = UserAccountId::fromString($executor_id_ulid, $this->validator);

        // 集約取得
        $group_chat = $this->repository->findById($id);
        if ($group_chat === null) {
            throw new \DomainException('Group chat not found');
        }

        // 削除
        $pair = $group_chat->delete(
            $executor_user_account_id,
            $this->generator
        );

        // 保存
        $this->repository->store($pair->getEvent(), $pair->getGroupChat());
    }

    public function addMember(
        string $group_chat_id,
        string $user_account_id,
        string $role_string,
        string $executor_id
    ): void {
        // プレフィックスを削除
        $user_account_id_ulid = preg_replace('/^UserAccount-/', '', $user_account_id);
        $executor_id_ulid = preg_replace('/^UserAccount-/', '', $executor_id);

        // 値オブジェクト生成
        $id = GroupChatId::fromString($group_chat_id, $this->validator);
        $member_id = MemberId::generate($this->generator);
        $user_account_id_obj = UserAccountId::fromString($user_account_id_ulid, $this->validator);
        $executor_user_account_id = UserAccountId::fromString($executor_id_ulid, $this->validator);

        // ロールを文字列から変換
        $role = match (strtoupper($role_string)) {
            'ADMINISTRATOR' => Role::ADMINISTRATOR,
            'MEMBER' => Role::MEMBER,
            default => throw new \InvalidArgumentException("Invalid role: {$role_string}"),
        };

        // 集約取得
        $group_chat = $this->repository->findById($id);
        if ($group_chat === null) {
            throw new \DomainException('Group chat not found');
        }

        // メンバー追加
        $pair = $group_chat->addMember(
            $member_id,
            $user_account_id_obj,
            $role,
            $executor_user_account_id,
            $this->generator
        );

        // 保存
        $this->repository->store($pair->getEvent(), $pair->getGroupChat());
    }

    public function removeMember(
        string $group_chat_id,
        string $user_account_id,
        string $executor_id
    ): void {
        // プレフィックスを削除
        $user_account_id_ulid = preg_replace('/^UserAccount-/', '', $user_account_id);
        $executor_id_ulid = preg_replace('/^UserAccount-/', '', $executor_id);

        // 値オブジェクト生成
        $id = GroupChatId::fromString($group_chat_id, $this->validator);
        $user_account_id_obj = UserAccountId::fromString($user_account_id_ulid, $this->validator);
        $executor_user_account_id = UserAccountId::fromString($executor_id_ulid, $this->validator);

        // 集約取得
        $group_chat = $this->repository->findById($id);
        if ($group_chat === null) {
            throw new \DomainException('Group chat not found');
        }

        // メンバー削除
        $pair = $group_chat->removeMember(
            $user_account_id_obj,
            $executor_user_account_id,
            $this->generator
        );

        // 保存
        $this->repository->store($pair->getEvent(), $pair->getGroupChat());
    }

    public function postMessage(
        string $group_chat_id,
        string $content,
        string $executor_id
    ): string {
        // プレフィックスを削除
        $executor_id_ulid = preg_replace('/^UserAccount-/', '', $executor_id);

        // 値オブジェクト生成
        $id = GroupChatId::fromString($group_chat_id, $this->validator);
        $message_id = MessageId::generate($this->generator);
        $sender_id = UserAccountId::fromString($executor_id_ulid, $this->validator);

        // 集約取得
        $group_chat = $this->repository->findById($id);
        if ($group_chat === null) {
            throw new \DomainException('Group chat not found');
        }

        // メッセージ投稿
        $pair = $group_chat->postMessage(
            $message_id,
            $content,
            $sender_id,
            $this->generator
        );

        // 保存
        $this->repository->store($pair->getEvent(), $pair->getGroupChat());

        return $message_id->toString();
    }

    public function editMessage(
        string $group_chat_id,
        string $message_id,
        string $content,
        string $executor_id
    ): void {
        // プレフィックスを削除
        $executor_id_ulid = preg_replace('/^UserAccount-/', '', $executor_id);

        // 値オブジェクト生成
        $id = GroupChatId::fromString($group_chat_id, $this->validator);
        $message_id_obj = MessageId::fromString($message_id, $this->validator);
        $executor_user_account_id = UserAccountId::fromString($executor_id_ulid, $this->validator);

        // 集約取得
        $group_chat = $this->repository->findById($id);
        if ($group_chat === null) {
            throw new \DomainException('Group chat not found');
        }

        // メッセージ編集
        $pair = $group_chat->editMessage(
            $message_id_obj,
            $content,
            $executor_user_account_id,
            $this->generator
        );

        // 保存
        $this->repository->store($pair->getEvent(), $pair->getGroupChat());
    }

    public function deleteMessage(
        string $group_chat_id,
        string $message_id,
        string $executor_id
    ): void {
        // プレフィックスを削除
        $executor_id_ulid = preg_replace('/^UserAccount-/', '', $executor_id);

        // 値オブジェクト生成
        $id = GroupChatId::fromString($group_chat_id, $this->validator);
        $message_id_obj = MessageId::fromString($message_id, $this->validator);
        $executor_user_account_id = UserAccountId::fromString($executor_id_ulid, $this->validator);

        // 集約取得
        $group_chat = $this->repository->findById($id);
        if ($group_chat === null) {
            throw new \DomainException('Group chat not found');
        }

        // メッセージ削除
        $pair = $group_chat->deleteMessage(
            $message_id_obj,
            $executor_user_account_id,
            $this->generator
        );

        // 保存
        $this->repository->store($pair->getEvent(), $pair->getGroupChat());
    }
}
