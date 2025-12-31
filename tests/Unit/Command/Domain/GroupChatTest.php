<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatCreated;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use PHPUnit\Framework\TestCase;

class GroupChatTest extends TestCase
{
    private RobinvdvleutenUlidGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new RobinvdvleutenUlidGenerator();
    }

    public function test_create_GroupChatが作成される(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $executor_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $executor_id, $this->generator);

        $this->assertInstanceOf(GroupChat::class, $pair->getGroupChat());
        $this->assertEquals($id->toString(), $pair->getGroupChat()->getId()->toString());
    }

    public function test_create_GroupChatCreatedイベントが記録される(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $executor_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $executor_id, $this->generator);

        $this->assertInstanceOf(GroupChatCreated::class, $pair->getEvent());
        $this->assertEquals($id->toString(), $pair->getEvent()->getAggregateId());
    }

    public function test_create_executorがADMINISTRATORとして追加される(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $executor_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $executor_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $this->assertTrue($group_chat->isMember($executor_id));
        $this->assertTrue($group_chat->isAdministrator($executor_id));
    }
}
