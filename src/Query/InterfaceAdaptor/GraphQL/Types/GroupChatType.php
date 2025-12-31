<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL\Types;

use Akinoriakatsuka\CqrsEsExamplePhp\Query\Domain\ReadModel\GroupChatReadModel;
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
                    'resolve' => fn (GroupChatReadModel $root) => $root->id,
                ],
                'name' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'グループチャット名',
                    'resolve' => fn (GroupChatReadModel $root) => $root->name,
                ],
                'ownerId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'オーナーID',
                    'resolve' => fn (GroupChatReadModel $root) => $root->owner_id,
                ],
                'createdAt' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => '作成日時',
                    'resolve' => fn (GroupChatReadModel $root) => $root->created_at,
                ],
                'updatedAt' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => '更新日時',
                    'resolve' => fn (GroupChatReadModel $root) => $root->updated_at,
                ],
            ],
        ]);
    }
}
