<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL\Types;

use Akinoriakatsuka\CqrsEsExamplePhp\Query\Domain\ReadModel\MessageReadModel;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class MessageType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Message',
            'description' => 'メッセージ',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'メッセージID',
                ],
                'groupChatId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'グループチャットID',
                    'resolve' => fn (MessageReadModel $root) => $root->group_chat_id,
                ],
                'userAccountId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'ユーザーアカウントID',
                    'resolve' => fn (MessageReadModel $root) => $root->user_account_id,
                ],
                'text' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'メッセージテキスト',
                ],
                'createdAt' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => '作成日時',
                    'resolve' => fn (MessageReadModel $root) => $root->created_at,
                ],
                'updatedAt' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => '更新日時',
                    'resolve' => fn (MessageReadModel $root) => $root->updated_at,
                ],
            ],
        ]);
    }
}
