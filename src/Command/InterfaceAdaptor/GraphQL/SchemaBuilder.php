<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Resolvers\MutationResolver;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Resolvers\QueryResolver;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Processor\GroupChatCommandProcessor;
use GraphQL\Type\Schema;

final class SchemaBuilder {
    public function __construct(
        private readonly GroupChatCommandProcessor $command_processor
    ) {
    }

    public function build(): Schema {
        return new Schema([
            'query' => new QueryResolver(),
            'mutation' => new MutationResolver($this->command_processor),
        ]);
    }
}
