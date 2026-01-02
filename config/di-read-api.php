<?php

declare(strict_types=1);

use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL\Schema;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL\Types\GroupChatType;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL\Types\MemberType;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL\Types\MessageType;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\GraphQL\Types\QueryType;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository\GroupChatQueryRepository;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository\GroupChatQueryRepositoryInterface;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository\MemberQueryRepository;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository\MemberQueryRepositoryInterface;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository\MessageQueryRepository;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\InterfaceAdaptor\Repository\MessageQueryRepositoryInterface;
use DI\ContainerBuilder;
use PDO;
use Psr\Container\ContainerInterface;
use function DI\autowire;
use function DI\get;

return function (ContainerBuilder $container_builder) {
    $container_builder->addDefinitions([
        // 環境変数の設定
        'db.host' => getenv('DB_HOST') ?: 'localhost',
        'db.port' => getenv('DB_PORT') ?: '3306',
        'db.database' => getenv('DB_DATABASE') ?: 'ceer',
        'db.username' => getenv('DB_USERNAME') ?: 'root',
        'db.password' => getenv('DB_PASSWORD') ?: 'passwd',

        // PDO接続
        PDO::class => function (ContainerInterface $c) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $c->get('db.host'),
                $c->get('db.port'),
                $c->get('db.database')
            );

            return new PDO($dsn, $c->get('db.username'), $c->get('db.password'), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        },

        // Repository Interfaces
        GroupChatQueryRepositoryInterface::class => get(GroupChatQueryRepository::class),
        MemberQueryRepositoryInterface::class => get(MemberQueryRepository::class),
        MessageQueryRepositoryInterface::class => get(MessageQueryRepository::class),

        // Repositories
        GroupChatQueryRepository::class => autowire(),
        MemberQueryRepository::class => autowire(),
        MessageQueryRepository::class => autowire(),

        // GraphQL Types
        GroupChatType::class => autowire(),
        MemberType::class => autowire(),
        MessageType::class => autowire(),
        QueryType::class => autowire(),

        // GraphQL Schema
        'GraphQLSchema' => function (ContainerInterface $c) {
            return Schema::build($c->get(PDO::class));
        },
    ]);
};