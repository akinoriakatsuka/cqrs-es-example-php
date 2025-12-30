<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain\Events;

use App\Command\Domain\Events\GroupChatCreated;
use App\Command\Domain\Models\GroupChatId;
use App\Command\Domain\Models\GroupChatName;
use App\Command\Domain\Models\Members;
use App\Command\Domain\Models\UserAccountId;
use App\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use App\Infrastructure\Ulid\RobinvdvleutenUlidValidator;
use PHPUnit\Framework\TestCase;

class GroupChatCreatedTest extends TestCase
{
    private RobinvdvleutenUlidValidator $validator;
    private RobinvdvleutenUlidGenerator $generator;

    protected function setUp(): void
    {
        $this->validator = new RobinvdvleutenUlidValidator();
        $this->generator = new RobinvdvleutenUlidGenerator();
    }

    public function test_正常に生成できる(): void
    {
        $aggregate_id = GroupChatId::fromString('01H42K4ABWQ5V2XQEP3A48VE0Z', $this->validator);
        $name = new GroupChatName('Test Group');
        $executor_id = UserAccountId::fromString('01H42K4ABWQ5V2XQEP3A48VE1A', $this->validator);
        $members = Members::create($executor_id, $this->generator);

        $event = GroupChatCreated::create(
            $aggregate_id,
            $name,
            $members,
            1,
            $executor_id,
            $this->generator
        );

        $this->assertInstanceOf(GroupChatCreated::class, $event);
        $this->assertEquals($aggregate_id->toString(), $event->getAggregateId());
        $this->assertEquals('GroupChatCreated', $event->getTypeName());
        $this->assertTrue($event->isCreated());
    }

    public function test_toArrayでシリアライズできる(): void
    {
        $aggregate_id = GroupChatId::fromString('01H42K4ABWQ5V2XQEP3A48VE0Z', $this->validator);
        $name = new GroupChatName('Test Group');
        $executor_id = UserAccountId::fromString('01H42K4ABWQ5V2XQEP3A48VE1A', $this->validator);
        $members = Members::create($executor_id, $this->generator);

        $event = GroupChatCreated::create(
            $aggregate_id,
            $name,
            $members,
            1,
            $executor_id,
            $this->generator
        );

        $array = $event->toArray();

        $this->assertIsArray($array);
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
        $aggregate_id = GroupChatId::fromString('01H42K4ABWQ5V2XQEP3A48VE0Z', $this->validator);
        $name = new GroupChatName('Test Group');
        $executor_id = UserAccountId::fromString('01H42K4ABWQ5V2XQEP3A48VE1A', $this->validator);
        $members = Members::create($executor_id, $this->generator);

        $original_event = GroupChatCreated::create(
            $aggregate_id,
            $name,
            $members,
            1,
            $executor_id,
            $this->generator
        );

        $data = $original_event->toArray();
        $event = GroupChatCreated::fromArray($data, $this->validator);

        $this->assertInstanceOf(GroupChatCreated::class, $event);
        $this->assertEquals($aggregate_id->toString(), $event->getAggregateId());
    }

    public function test_ラウンドトリップでデータが保持される(): void
    {
        $aggregate_id = GroupChatId::fromString('01H42K4ABWQ5V2XQEP3A48VE0Z', $this->validator);
        $name = new GroupChatName('Test Group');
        $executor_id = UserAccountId::fromString('01H42K4ABWQ5V2XQEP3A48VE1A', $this->validator);
        $members = Members::create($executor_id, $this->generator);

        $original_event = GroupChatCreated::create(
            $aggregate_id,
            $name,
            $members,
            1,
            $executor_id,
            $this->generator
        );

        $array = $original_event->toArray();
        $restored_event = GroupChatCreated::fromArray($array, $this->validator);

        $this->assertEquals($original_event->getAggregateId(), $restored_event->getAggregateId());
        $this->assertEquals($original_event->getSeqNr(), $restored_event->getSeqNr());
        $this->assertEquals($original_event->getTypeName(), $restored_event->getTypeName());
    }
}
