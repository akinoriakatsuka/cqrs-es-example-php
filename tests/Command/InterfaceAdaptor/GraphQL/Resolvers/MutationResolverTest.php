<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\InterfaceAdaptor\GraphQL\Resolvers;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatCreated;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Resolvers\MutationResolver;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Processor\GroupChatCommandProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MutationResolverTest extends TestCase {
    private MutationResolver $mutation_resolver;
    private MockObject $command_processor_mock;

    protected function setUp(): void {
        $this->command_processor_mock = $this->createMock(GroupChatCommandProcessor::class);
        $this->mutation_resolver = new MutationResolver($this->command_processor_mock);
    }

    public function testCreateGroupChat(): void {
        $group_chat_id = new GroupChatId('test-id');
        $group_chat_name = new GroupChatName('Test Chat');
        $executor_id = new UserAccountId('executor-id');

        $event = new GroupChatCreated(
            'event-id',
            $group_chat_id,
            $group_chat_name,
            1,
            new \DateTimeImmutable()
        );

        $this->command_processor_mock
            ->expects($this->once())
            ->method('createGroupChat')
            ->with(
                $this->callback(function ($name) {
                    return $name instanceof GroupChatName && $name->getValue() === 'Test Chat';
                }),
                $this->callback(function ($executor) {
                    return $executor instanceof UserAccountId && $executor->getValue() === 'executor-id';
                })
            )
            ->willReturn($event);

        $result = $this->mutation_resolver->createGroupChat(null, [
            'name' => 'Test Chat',
            'executorId' => 'executor-id',
        ]);

        $this->assertEquals('test-id', $result['id']);
        $this->assertEquals('Test Chat', $result['name']);
        $this->assertEquals(1, $result['version']);
        $this->assertFalse($result['isDeleted']);
    }
}
