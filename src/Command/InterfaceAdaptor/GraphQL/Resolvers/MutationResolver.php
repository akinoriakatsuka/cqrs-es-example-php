<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Resolvers;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Processor\GroupChatCommandProcessor;

class MutationResolver {
    public function __construct(
        private readonly GroupChatCommandProcessor $command_processor
    ) {
    }

    /**
     * @param array<string, mixed> $args
     * @return array<string, mixed>
     */
    public function createGroupChat(mixed $root_value, array $args): array {
        if (!is_string($args['name'])) {
            throw new \InvalidArgumentException('Name must be a string');
        }
        if (!is_string($args['executorId'])) {
            throw new \InvalidArgumentException('ExecutorId must be a string');
        }

        $group_chat_name = new GroupChatName($args['name']);
        $executor_id = new UserAccountId($args['executorId']);

        $event = $this->command_processor->createGroupChat($group_chat_name, $executor_id);

        return [
            'id' => $event->getAggregateId()->getValue(),
            'name' => $args['name'],
            'version' => 1,
            'isDeleted' => false,
        ];
    }
}
