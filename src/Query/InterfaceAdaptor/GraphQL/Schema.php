<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL;

use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL\Types\GroupChatType;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL\Types\MemberType;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL\Types\MessageType;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL\Types\QueryType;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository\GroupChatQueryRepository;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository\MemberQueryRepository;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository\MessageQueryRepository;
use GraphQL\Type\Schema as GraphQLSchema;
use PDO;

class Schema
{
    public static function build(PDO $pdo): GraphQLSchema
    {
        // リポジトリをインスタンス化
        $group_chat_repo = new GroupChatQueryRepository($pdo);
        $member_repo = new MemberQueryRepository($pdo);
        $message_repo = new MessageQueryRepository($pdo);

        // Typeをインスタンス化
        $group_chat_type = new GroupChatType();
        $member_type = new MemberType();
        $message_type = new MessageType();

        // QueryTypeにリポジトリとTypeを注入
        $query_type = new QueryType(
            $group_chat_repo,
            $member_repo,
            $message_repo,
            $group_chat_type,
            $member_type,
            $message_type
        );

        return new GraphQLSchema([
            'query' => $query_type,
        ]);
    }
}
