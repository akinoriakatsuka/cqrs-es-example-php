<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatCreated;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatCreatedFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Members;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MembersFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidValidator;
use PHPUnit\Framework\TestCase;

class GroupChatCreatedTest extends TestCase
{
    private RobinvdvleutenUlidValidator $validator;
    private RobinvdvleutenUlidGenerator $generator;
    private GroupChatIdFactory $group_chat_id_factory;
    private MemberIdFactory $member_id_factory;
    private UserAccountIdFactory $user_account_id_factory;
    private GroupChatCreatedFactory $group_chat_created_factory;

    protected function setUp(): void
    {
        $this->validator = new RobinvdvleutenUlidValidator();
        $this->generator = new RobinvdvleutenUlidGenerator();
        $this->group_chat_id_factory = new GroupChatIdFactory($this->generator, $this->validator);
        $this->member_id_factory = new MemberIdFactory($this->generator, $this->validator);
        $this->user_account_id_factory = new UserAccountIdFactory($this->generator, $this->validator);
        $member_factory = new MemberFactory($this->user_account_id_factory, $this->member_id_factory);
        $members_factory = new MembersFactory($member_factory);
        $this->group_chat_created_factory = new GroupChatCreatedFactory(
            $this->group_chat_id_factory,
            $this->user_account_id_factory,
            $members_factory
        );
    }

    public function test_正常に生成できる(): void
    {
        $aggregate_id = $this->group_chat_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE0Z');
        $name = new GroupChatName('Test Group');
        $executor_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1A');
        $members = Members::create($executor_id, $this->member_id_factory);

        $event = GroupChatCreated::create(
            $aggregate_id,
            $name,
            $members,
            1,
            $executor_id
        );

        $this->assertInstanceOf(GroupChatCreated::class, $event);
        $this->assertEquals($aggregate_id->toString(), $event->getAggregateId());
        $this->assertEquals('GroupChatCreated', $event->getTypeName());
        $this->assertTrue($event->isCreated());
    }

    public function test_toArrayでシリアライズできる(): void
    {
        $aggregate_id = $this->group_chat_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE0Z');
        $name = new GroupChatName('Test Group');
        $executor_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1A');
        $members = Members::create($executor_id, $this->member_id_factory);

        $event = GroupChatCreated::create(
            $aggregate_id,
            $name,
            $members,
            1,
            $executor_id
        );

        $array = $event->toArray();

        $this->assertEquals('GroupChatCreated', $array['type_name']);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('aggregate_id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('members', $array);
        $this->assertArrayHasKey('executor_id', $array);
        $this->assertEquals(1, $array['seq_nr']);
    }

    public function test_fromArrayでデシリアライズできる(): void
    {
        $aggregate_id = $this->group_chat_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE0Z');
        $name = new GroupChatName('Test Group');
        $executor_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1A');
        $members = Members::create($executor_id, $this->member_id_factory);

        $original_event = GroupChatCreated::create(
            $aggregate_id,
            $name,
            $members,
            1,
            $executor_id
        );

        $data = $original_event->toArray();
        $event = $this->group_chat_created_factory->fromArray($data);

        $this->assertInstanceOf(GroupChatCreated::class, $event);
        $this->assertEquals($aggregate_id->toString(), $event->getAggregateId());
    }

    public function test_ラウンドトリップでデータが保持される(): void
    {
        $aggregate_id = $this->group_chat_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE0Z');
        $name = new GroupChatName('Test Group');
        $executor_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1A');
        $members = Members::create($executor_id, $this->member_id_factory);

        $original_event = GroupChatCreated::create(
            $aggregate_id,
            $name,
            $members,
            1,
            $executor_id
        );

        $array = $original_event->toArray();
        $restored_event = $this->group_chat_created_factory->fromArray($array);

        $this->assertEquals($original_event->getAggregateId(), $restored_event->getAggregateId());
        $this->assertEquals($original_event->getSeqNr(), $restored_event->getSeqNr());
        $this->assertEquals($original_event->getTypeName(), $restored_event->getTypeName());
    }
}
