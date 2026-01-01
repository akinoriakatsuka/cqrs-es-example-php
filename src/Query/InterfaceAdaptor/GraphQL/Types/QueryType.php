<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL\Types;

use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository\GroupChatQueryRepositoryInterface;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository\MemberQueryRepositoryInterface;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository\MessageQueryRepositoryInterface;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class QueryType extends ObjectType
{
    public function __construct(
        GroupChatQueryRepositoryInterface $group_chat_repo,
        MemberQueryRepositoryInterface $member_repo,
        MessageQueryRepositoryInterface $message_repo,
        GroupChatType $group_chat_type,
        MemberType $member_type,
        MessageType $message_type
    ) {

        parent::__construct([
            'name' => 'Query',
            'fields' => [
                'getGroupChat' => [
                    'type' => $group_chat_type,
                    'description' => 'グループチャットを取得',
                    'args' => [
                        'groupChatId' => [
                            'type' => Type::id() |> Type::nonNull(...),
                            'description' => 'グループチャットID',
                        ],
                        'userAccountId' => [
                            'type' => Type::id() |> Type::nonNull(...),
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
                    'type' => $group_chat_type
                        |> Type::nonNull(...)
                        |> Type::listOf(...)
                        |> Type::nonNull(...),
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
                            'type' => Type::id() |> Type::nonNull(...),
                            'description' => 'グループチャットID',
                        ],
                        'userAccountId' => [
                            'type' => Type::id() |> Type::nonNull(...),
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
                    'type' => $member_type
                        |> Type::nonNull(...)
                        |> Type::listOf(...)
                        |> Type::nonNull(...),
                    'description' => 'メンバー一覧を取得',
                    'args' => [
                        'groupChatId' => [
                            'type' => Type::id() |> Type::nonNull(...),
                            'description' => 'グループチャットID',
                        ],
                        'userAccountId' => [
                            'type' => Type::id() |> Type::nonNull(...),
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
                    'type' => $message_type
                        |> Type::nonNull(...)
                        |> Type::listOf(...)
                        |> Type::nonNull(...),
                    'description' => 'メッセージ一覧を取得',
                    'args' => [
                        'groupChatId' => [
                            'type' => Type::id() |> Type::nonNull(...),
                            'description' => 'グループチャットID',
                        ],
                        'userAccountId' => [
                            'type' => Type::id() |> Type::nonNull(...),
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
