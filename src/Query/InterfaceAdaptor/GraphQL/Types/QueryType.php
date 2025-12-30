<?php

declare(strict_types=1);

namespace App\Query\InterfaceAdaptor\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use App\Query\InterfaceAdaptor\Repository\GroupChatQueryRepository;
use App\Query\InterfaceAdaptor\Repository\MemberQueryRepository;
use App\Query\InterfaceAdaptor\Repository\MessageQueryRepository;
use PDO;

class QueryType extends ObjectType
{
    public function __construct(
        PDO $pdo,
        GroupChatType $group_chat_type,
        MemberType $member_type,
        MessageType $message_type
    ) {
        $group_chat_repo = new GroupChatQueryRepository($pdo);
        $member_repo = new MemberQueryRepository($pdo);
        $message_repo = new MessageQueryRepository($pdo);

        parent::__construct([
            'name' => 'Query',
            'fields' => [
                'getGroupChat' => [
                    'type' => $group_chat_type,
                    'description' => 'グループチャットを取得',
                    'args' => [
                        'groupChatId' => [
                            'type' => Type::nonNull(Type::id()),
                            'description' => 'グループチャットID',
                        ],
                        'userAccountId' => [
                            'type' => Type::nonNull(Type::id()),
                            'description' => 'ユーザーアカウントID',
                        ],
                    ],
                    'resolve' => function ($root, $args) use ($group_chat_repo) {
                        return $group_chat_repo->findById(
                            $args['groupChatId'],
                            $args['userAccountId']
                        );
                    },
                ],
                'getGroupChats' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull($group_chat_type))),
                    'description' => 'グループチャット一覧を取得',
                    'args' => [
                        'userAccountId' => [
                            'type' => Type::nonNull(Type::id()),
                            'description' => 'ユーザーアカウントID',
                        ],
                    ],
                    'resolve' => function ($root, $args) use ($group_chat_repo) {
                        return $group_chat_repo->findByUserAccountId($args['userAccountId']);
                    },
                ],
                'getMember' => [
                    'type' => $member_type,
                    'description' => 'メンバーを取得',
                    'args' => [
                        'groupChatId' => [
                            'type' => Type::nonNull(Type::id()),
                            'description' => 'グループチャットID',
                        ],
                        'userAccountId' => [
                            'type' => Type::nonNull(Type::id()),
                            'description' => 'ユーザーアカウントID',
                        ],
                    ],
                    'resolve' => function ($root, $args) use ($member_repo) {
                        return $member_repo->findByGroupChatIdAndUserAccountId(
                            $args['groupChatId'],
                            $args['userAccountId']
                        );
                    },
                ],
                'getMembers' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull($member_type))),
                    'description' => 'メンバー一覧を取得',
                    'args' => [
                        'groupChatId' => [
                            'type' => Type::nonNull(Type::id()),
                            'description' => 'グループチャットID',
                        ],
                        'userAccountId' => [
                            'type' => Type::nonNull(Type::id()),
                            'description' => 'ユーザーアカウントID',
                        ],
                    ],
                    'resolve' => function ($root, $args) use ($member_repo) {
                        return $member_repo->findByGroupChatId(
                            $args['groupChatId'],
                            $args['userAccountId']
                        );
                    },
                ],
                'getMessage' => [
                    'type' => $message_type,
                    'description' => 'メッセージを取得',
                    'args' => [
                        'messageId' => [
                            'type' => Type::nonNull(Type::id()),
                            'description' => 'メッセージID',
                        ],
                        'userAccountId' => [
                            'type' => Type::nonNull(Type::id()),
                            'description' => 'ユーザーアカウントID',
                        ],
                    ],
                    'resolve' => function ($root, $args) use ($message_repo) {
                        return $message_repo->findById(
                            $args['messageId'],
                            $args['userAccountId']
                        );
                    },
                ],
                'getMessages' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull($message_type))),
                    'description' => 'メッセージ一覧を取得',
                    'args' => [
                        'groupChatId' => [
                            'type' => Type::nonNull(Type::id()),
                            'description' => 'グループチャットID',
                        ],
                        'userAccountId' => [
                            'type' => Type::nonNull(Type::id()),
                            'description' => 'ユーザーアカウントID',
                        ],
                    ],
                    'resolve' => function ($root, $args) use ($message_repo) {
                        return $message_repo->findByGroupChatId(
                            $args['groupChatId'],
                            $args['userAccountId']
                        );
                    },
                ],
            ],
        ]);
    }
}
