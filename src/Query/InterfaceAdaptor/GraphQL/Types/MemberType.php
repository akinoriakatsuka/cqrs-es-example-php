<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL\Types;

use Akinoriakatsuka\CqrsEsExamplePhp\Query\Domain\ReadModel\MemberReadModel;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class MemberType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Member',
            'description' => 'メンバー',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'メンバーID',
                    'resolve' => fn (MemberReadModel $root) => $root->id,
                ],
                'groupChatId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'グループチャットID',
                    'resolve' => fn (MemberReadModel $root) => $root->group_chat_id,
                ],
                'userAccountId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'ユーザーアカウントID',
                    'resolve' => fn (MemberReadModel $root) => $root->user_account_id,
                ],
                'role' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'ロール',
                    'resolve' => fn (MemberReadModel $root) => $root->role,
                ],
                'createdAt' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => '作成日時',
                    'resolve' => fn (MemberReadModel $root) => $root->created_at,
                ],
                'updatedAt' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => '更新日時',
                    'resolve' => fn (MemberReadModel $root) => $root->updated_at,
                ],
            ],
        ]);
    }
}
