<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Processor;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory\GroupChatIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory\MemberIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory\MessageIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory\UserAccountIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Role;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\Repository\GroupChatRepository;

class GroupChatCommandProcessor
{
    public function __construct(
        private GroupChatRepository $repository,
        private GroupChatIdFactory $group_chat_id_factory,
        private UserAccountIdFactory $user_account_id_factory,
        private MemberIdFactory $member_id_factory,
        private MessageIdFactory $message_id_factory
    ) {
    }

    public function createGroupChat(
        string $name,
        string $executor_id
    ): string {
        // 値オブジェクト生成
        $id = $this->group_chat_id_factory->create();
        $group_chat_name = new GroupChatName($name);
        $executor_user_account_id = $this->user_account_id_factory->fromString($executor_id);

        // 集約作成
        $pair = GroupChat::create(
            $id,
            $group_chat_name,
            $executor_user_account_id,
            $this->member_id_factory
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
        // 値オブジェクト生成
        $id = $this->group_chat_id_factory->fromString($group_chat_id);
        $group_chat_name = new GroupChatName($name);
        $executor_user_account_id = $this->user_account_id_factory->fromString($executor_id);

        // 集約取得
        $group_chat = $this->repository->findById($id);
        if ($group_chat === null) {
            throw new \DomainException('Group chat not found');
        }

        // リネーム
        $pair = $group_chat->rename(
            $group_chat_name,
            $executor_user_account_id
        );

        // 保存
        $this->repository->store($pair->getEvent(), $pair->getGroupChat());
    }

    public function deleteGroupChat(
        string $group_chat_id,
        string $executor_id
    ): void {
        // 値オブジェクト生成
        $id = $this->group_chat_id_factory->fromString($group_chat_id);
        $executor_user_account_id = $this->user_account_id_factory->fromString($executor_id);

        // 集約取得
        $group_chat = $this->repository->findById($id);
        if ($group_chat === null) {
            throw new \DomainException('Group chat not found');
        }

        // 削除
        $pair = $group_chat->delete($executor_user_account_id);

        // 保存
        $this->repository->store($pair->getEvent(), $pair->getGroupChat());
    }

    public function addMember(
        string $group_chat_id,
        string $user_account_id,
        string $role_string,
        string $executor_id
    ): void {
        // 値オブジェクト生成
        $id = $this->group_chat_id_factory->fromString($group_chat_id);
        $member_id = $this->member_id_factory->create();
        $user_account_id_obj = $this->user_account_id_factory->fromString($user_account_id);
        $executor_user_account_id = $this->user_account_id_factory->fromString($executor_id);

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
            $executor_user_account_id
        );

        // 保存
        $this->repository->store($pair->getEvent(), $pair->getGroupChat());
    }

    public function removeMember(
        string $group_chat_id,
        string $user_account_id,
        string $executor_id
    ): void {
        // 値オブジェクト生成
        $id = $this->group_chat_id_factory->fromString($group_chat_id);
        $user_account_id_obj = $this->user_account_id_factory->fromString($user_account_id);
        $executor_user_account_id = $this->user_account_id_factory->fromString($executor_id);

        // 集約取得
        $group_chat = $this->repository->findById($id);
        if ($group_chat === null) {
            throw new \DomainException('Group chat not found');
        }

        // メンバー削除
        $pair = $group_chat->removeMember(
            $user_account_id_obj,
            $executor_user_account_id
        );

        // 保存
        $this->repository->store($pair->getEvent(), $pair->getGroupChat());
    }

    public function postMessage(
        string $group_chat_id,
        string $content,
        string $executor_id
    ): string {
        // 値オブジェクト生成
        $id = $this->group_chat_id_factory->fromString($group_chat_id);
        $message_id = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->fromString($executor_id);

        // 集約取得
        $group_chat = $this->repository->findById($id);
        if ($group_chat === null) {
            throw new \DomainException('Group chat not found');
        }

        // メッセージ投稿
        $pair = $group_chat->postMessage(
            $message_id,
            $content,
            $sender_id
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
        // 値オブジェクト生成
        $id = $this->group_chat_id_factory->fromString($group_chat_id);
        $message_id_obj = $this->message_id_factory->fromString($message_id);
        $executor_user_account_id = $this->user_account_id_factory->fromString($executor_id);

        // 集約取得
        $group_chat = $this->repository->findById($id);
        if ($group_chat === null) {
            throw new \DomainException('Group chat not found');
        }

        // メッセージ編集
        $pair = $group_chat->editMessage(
            $message_id_obj,
            $content,
            $executor_user_account_id
        );

        // 保存
        $this->repository->store($pair->getEvent(), $pair->getGroupChat());
    }

    public function deleteMessage(
        string $group_chat_id,
        string $message_id,
        string $executor_id
    ): void {
        // 値オブジェクト生成
        $id = $this->group_chat_id_factory->fromString($group_chat_id);
        $message_id_obj = $this->message_id_factory->fromString($message_id);
        $executor_user_account_id = $this->user_account_id_factory->fromString($executor_id);

        // 集約取得
        $group_chat = $this->repository->findById($id);
        if ($group_chat === null) {
            throw new \DomainException('Group chat not found');
        }

        // メッセージ削除
        $pair = $group_chat->deleteMessage(
            $message_id_obj,
            $executor_user_account_id
        );

        // 保存
        $this->repository->store($pair->getEvent(), $pair->getGroupChat());
    }
}
