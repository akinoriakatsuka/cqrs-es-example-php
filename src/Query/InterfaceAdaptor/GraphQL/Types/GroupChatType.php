<?php

declare(strict_types=1);

namespace App\Query\InterfaceAdaptor\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class GroupChatType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'GroupChat',
            'description' => 'グループチャット',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'グループチャットID',
                ],
                'name' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'グループチャット名',
                ],
                'ownerId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'オーナーID',
                    'resolve' => fn($root) => $root['owner_id'] ?? null,
                ],
                'createdAt' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => '作成日時',
                    'resolve' => fn($root) => $root['created_at'] ?? null,
                ],
                'updatedAt' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => '更新日時',
                    'resolve' => fn($root) => $root['updated_at'] ?? null,
                ],
            ],
        ]);
    }
}
