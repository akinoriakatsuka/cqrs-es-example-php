<?php

declare(strict_types=1);

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\Factory\GroupChatCreatedFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\Factory\GroupChatDeletedFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\Factory\GroupChatMemberAddedFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\Factory\GroupChatMemberRemovedFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\Factory\GroupChatMessageDeletedFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\Factory\GroupChatMessageEditedFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\Factory\GroupChatMessagePostedFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\Factory\GroupChatRenamedFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory\GroupChatIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory\MemberFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory\MemberIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory\MembersFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory\MessageFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory\MessageIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory\MessagesFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory\UserAccountIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Schema;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\Repository\GroupChatRepository;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Processor\GroupChatCommandProcessor;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore\DynamoDBEventStore;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore\EventStore as EventStoreInterface;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore\EventConverter;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore\EventSerializer;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore\GroupChatEventAdapter;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore\SnapshotConverter;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore\SnapshotSerializer;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidValidator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidValidator;
use Aws\DynamoDb\DynamoDbClient;
use DI\ContainerBuilder;
use J5ik2o\EventStoreAdapterPhp\EventStore;
use J5ik2o\EventStoreAdapterPhp\EventStoreFactory;
use Psr\Container\ContainerInterface;
use function DI\autowire;
use function DI\get;

return function (ContainerBuilder $container_builder) {
    $container_builder->addDefinitions([
        // 環境変数の設定
        'aws.region' => getenv('AWS_REGION') ?: 'ap-northeast-1',
        'aws.dynamodb.endpoint' => getenv('AWS_DYNAMODB_ENDPOINT_URL') ?: '',
        'aws.dynamodb.access_key' => getenv('AWS_DYNAMODB_ACCESS_KEY_ID') ?: '',
        'aws.dynamodb.secret_key' => getenv('AWS_DYNAMODB_SECRET_ACCESS_KEY') ?: '',
        'persistence.journal.table_name' => getenv('PERSISTENCE_JOURNAL_TABLE_NAME') ?: 'journal',
        'persistence.snapshot.table_name' => getenv('PERSISTENCE_SNAPSHOT_TABLE_NAME') ?: 'snapshot',
        'persistence.journal.aid_index_name' => getenv('PERSISTENCE_JOURNAL_AID_INDEX_NAME') ?: 'journal-aid-index',
        'persistence.snapshot.aid_index_name' => getenv('PERSISTENCE_SNAPSHOT_AID_INDEX_NAME') ?: 'snapshot-aid-index',
        'persistence.shard_count' => (int)(getenv('PERSISTENCE_SHARD_COUNT') ?: 10),

        // DynamoDBクライアント
        DynamoDbClient::class => function (ContainerInterface $c) {
            $config = [
                'region' => $c->get('aws.region'),
                'version' => 'latest',
            ];

            $endpoint = $c->get('aws.dynamodb.endpoint');
            if ($endpoint !== '') {
                $config['endpoint'] = $endpoint;
            }

            $access_key = $c->get('aws.dynamodb.access_key');
            $secret_key = $c->get('aws.dynamodb.secret_key');
            if ($access_key !== '' && $secret_key !== '') {
                $config['credentials'] = [
                    'key' => $access_key,
                    'secret' => $secret_key,
                ];
            }

            return new DynamoDbClient($config);
        },

        // Infrastructure
        RobinvdvleutenUlidValidator::class => autowire(),
        RobinvdvleutenUlidGenerator::class => autowire(),
        UlidValidator::class => get(RobinvdvleutenUlidValidator::class),
        UlidGenerator::class => get(RobinvdvleutenUlidGenerator::class),

        // ID Factories
        GroupChatIdFactory::class => autowire(),
        UserAccountIdFactory::class => autowire(),
        MemberIdFactory::class => autowire(),
        MessageIdFactory::class => autowire(),

        // Model Factories
        MessageFactory::class => autowire(),
        MemberFactory::class => autowire(),
        MembersFactory::class => autowire(),
        MessagesFactory::class => autowire(),

        // Event Factories
        GroupChatCreatedFactory::class => autowire(),
        GroupChatDeletedFactory::class => autowire(),
        GroupChatRenamedFactory::class => autowire(),
        GroupChatMemberAddedFactory::class => autowire(),
        GroupChatMemberRemovedFactory::class => autowire(),
        GroupChatMessagePostedFactory::class => autowire(),
        GroupChatMessageEditedFactory::class => autowire(),
        GroupChatMessageDeletedFactory::class => autowire(),

        // Event Store Components
        EventSerializer::class => autowire(),
        EventConverter::class => autowire(),
        SnapshotSerializer::class => autowire(),
        SnapshotConverter::class => autowire(),

        // EventStore
        EventStore::class => function (ContainerInterface $c) {
            $event_converter = $c->get(EventConverter::class);
            $snapshot_converter = $c->get(SnapshotConverter::class);
            $validator = $c->get(RobinvdvleutenUlidValidator::class);

            $event_converter_callable = function (array $data) use ($event_converter, $validator) {
                return new GroupChatEventAdapter(
                    $event_converter->convert($data),
                    $validator
                );
            };

            $snapshot_converter_callable = function (array $data) use ($snapshot_converter) {
                return $snapshot_converter->convert($data);
            };

            $j5_event_store = EventStoreFactory::create(
                $c->get(DynamoDbClient::class),
                $c->get('persistence.journal.table_name'),
                $c->get('persistence.snapshot.table_name'),
                $c->get('persistence.journal.aid_index_name'),
                $c->get('persistence.snapshot.aid_index_name'),
                $c->get('persistence.shard_count'),
                $event_converter_callable,
                $snapshot_converter_callable
            );

            return $j5_event_store
                ->withEventSerializer($c->get(EventSerializer::class))
                ->withSnapshotSerializer($c->get(SnapshotSerializer::class));
        },

        DynamoDBEventStore::class => function (ContainerInterface $c) {
            return new DynamoDBEventStore(
                $c->get(EventStore::class),
                $c->get(RobinvdvleutenUlidValidator::class)
            );
        },
        EventStoreInterface::class => get(DynamoDBEventStore::class),

        // Repository
        GroupChatRepository::class => function (ContainerInterface $c) {
            return new GroupChatRepository(
                $c->get(DynamoDBEventStore::class)
            );
        },

        // Command Processor
        GroupChatCommandProcessor::class => autowire(),

        // GraphQL Schema
        'GraphQLSchema' => function (ContainerInterface $c) {
            return Schema::build($c->get(GroupChatCommandProcessor::class));
        },
    ]);
};