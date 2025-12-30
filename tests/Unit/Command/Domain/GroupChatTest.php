<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain;

use App\Command\Domain\GroupChat;
use App\Command\Domain\Events\GroupChatCreated;
use App\Command\Domain\Models\GroupChatId;
use App\Command\Domain\Models\GroupChatName;
use App\Command\Domain\Models\UserAccountId;
use App\Infrastructure\Ulid\RobinvdvleutenUlidValidator;
use App\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use PHPUnit\Framework\TestCase;

class GroupChatTest extends TestCase
{
    private RobinvdvleutenUlidValidator $validator;
    private RobinvdvleutenUlidGenerator $generator;

    protected function setUp(): void
    {
        $this->validator = new RobinvdvleutenUlidValidator();
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
