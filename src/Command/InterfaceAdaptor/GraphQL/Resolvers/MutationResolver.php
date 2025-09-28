<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Resolvers;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\InputTypes\CreateGroupChatInputType;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\PayloadTypes\CreateGroupChatPayloadType;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Processor\GroupChatCommandProcessor;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class MutationResolver extends ObjectType {
    public function __construct(
        private readonly GroupChatCommandProcessor $command_processor
    ) {
        parent::__construct([
            'name' => 'Mutation',
            'description' => 'Root mutation type',
            'fields' => [
                'createGroupChat' => [
                    'type' => Type::nonNull(new CreateGroupChatPayloadType()),
                    'description' => 'Create a new group chat',
                    'args' => [
                        'input' => [
                            'type' => Type::nonNull(new CreateGroupChatInputType()),
                            'description' => 'Input for creating a group chat',
                        ],
                    ],
                    'resolve' => [$this, 'createGroupChat'],
                ],
            ],
        ]);
    }

    public function createGroupChat(mixed $root_value, array $args): array {
        $input = $args['input'];

        try {
            // 入力データの変換
            $executor_id = new \Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId($input['executorId']);
            $group_chat_name = new \Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName($input['name']);

            // グループチャット作成
            $event = $this->command_processor->createGroupChat($group_chat_name, $executor_id);

            // 作成されたグループチャットを取得
            $group_chat_id = $event->getAggregateId();
            $group_chat = $this->command_processor->repository->findById($group_chat_id);

            if ($group_chat === null) {
                throw new \RuntimeException('Failed to create group chat');
            }

            return [
                'groupChat' => $group_chat,
            ];

        } catch (\Exception $e) {
            throw new \GraphQL\Error\Error('Group chat creation failed: ' . $e->getMessage());
        }
    }

    public function deleteGroupChat(mixed $root_value, array $args): array {
        $input = $args['input'];

        // TODO: Implement actual group chat deletion logic
        throw new \RuntimeException('deleteGroupChat not implemented yet');
    }

    public function renameGroupChat(mixed $root_value, array $args): array {
        $input = $args['input'];

        // TODO: Implement actual group chat renaming logic
        throw new \RuntimeException('renameGroupChat not implemented yet');
    }

    public function addMember(mixed $root_value, array $args): array {
        $input = $args['input'];

        // TODO: Implement actual member addition logic
        throw new \RuntimeException('addMember not implemented yet');
    }

    public function removeMember(mixed $root_value, array $args): array {
        $input = $args['input'];

        // TODO: Implement actual member removal logic
        throw new \RuntimeException('removeMember not implemented yet');
    }

    public function postMessage(mixed $root_value, array $args): array {
        $input = $args['input'];

        // TODO: Implement actual message posting logic
        throw new \RuntimeException('postMessage not implemented yet');
    }

    public function editMessage(mixed $root_value, array $args): array {
        $input = $args['input'];

        // TODO: Implement actual message editing logic
        throw new \RuntimeException('editMessage not implemented yet');
    }

    public function deleteMessage(mixed $root_value, array $args): array {
        $input = $args['input'];

        // TODO: Implement actual message deletion logic
        throw new \RuntimeException('deleteMessage not implemented yet');
    }
}
