<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL\Types;

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
                    'resolve' => fn ($root) => $root['group_chat_id'] ?? null,
                ],
                'userAccountId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'ユーザーアカウントID',
                    'resolve' => fn ($root) => $root['user_account_id'] ?? null,
                ],
                'text' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'メッセージテキスト',
                ],
                'createdAt' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => '作成日時',
                    'resolve' => fn ($root) => $root['created_at'] ?? null,
                ],
                'updatedAt' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => '更新日時',
                    'resolve' => fn ($root) => $root['updated_at'] ?? null,
                ],
            ],
        ]);
    }
}
