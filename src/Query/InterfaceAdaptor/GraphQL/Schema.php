<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL;

use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL\Types\GroupChatType;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL\Types\MemberType;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL\Types\MessageType;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL\Types\QueryType;
use GraphQL\Type\Schema as GraphQLSchema;
use PDO;

class Schema
{
    public static function build(PDO $pdo): GraphQLSchema
    {
        $group_chat_type = new GroupChatType();
        $member_type = new MemberType();
        $message_type = new MessageType();
        $query_type = new QueryType($pdo, $group_chat_type, $member_type, $message_type);

        return new GraphQLSchema([
            'query' => $query_type,
        ]);
    }
}
