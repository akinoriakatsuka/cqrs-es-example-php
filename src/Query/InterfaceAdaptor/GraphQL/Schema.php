<?php

declare(strict_types=1);

namespace App\Query\InterfaceAdaptor\GraphQL;

use App\Query\InterfaceAdaptor\GraphQL\Types\GroupChatType;
use App\Query\InterfaceAdaptor\GraphQL\Types\MemberType;
use App\Query\InterfaceAdaptor\GraphQL\Types\MessageType;
use App\Query\InterfaceAdaptor\GraphQL\Types\QueryType;
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
