<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatRenamed;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidValidator;
use PHPUnit\Framework\TestCase;

class GroupChatRenamedTest extends TestCase
{
    private RobinvdvleutenUlidValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new RobinvdvleutenUlidValidator();
    }

    public function test_正常に生成できる(): void
    {
        $aggregate_id = GroupChatId::fromString('01H42K4ABWQ5V2XQEP3A48VE0Z', $this->validator);
        $name = new GroupChatName('New Name');
        $executor_id = UserAccountId::fromString('01H42K4ABWQ5V2XQEP3A48VE1A', $this->validator);

        $event = GroupChatRenamed::create(
            $aggregate_id,
            $name,
            2,
            $executor_id
        );

        $this->assertInstanceOf(GroupChatRenamed::class, $event);
        $this->assertEquals($aggregate_id->toString(), $event->getAggregateId());
        $this->assertEquals('GroupChatRenamed', $event->getTypeName());
        $this->assertFalse($event->isCreated());
    }

    public function test_toArrayでシリアライズできる(): void
    {
        $aggregate_id = GroupChatId::fromString('01H42K4ABWQ5V2XQEP3A48VE0Z', $this->validator);
        $name = new GroupChatName('New Name');
        $executor_id = UserAccountId::fromString('01H42K4ABWQ5V2XQEP3A48VE1A', $this->validator);

        $event = GroupChatRenamed::create(
            $aggregate_id,
            $name,
            2,
            $executor_id
        );

        $array = $event->toArray();

        $this->assertEquals('GroupChatRenamed', $array['type_name']);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('aggregate_id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('executor_id', $array);
        $this->assertEquals(2, $array['seq_nr']);
    }

    public function test_fromArrayでデシリアライズできる(): void
    {
        $aggregate_id = GroupChatId::fromString('01H42K4ABWQ5V2XQEP3A48VE0Z', $this->validator);
        $name = new GroupChatName('New Name');
        $executor_id = UserAccountId::fromString('01H42K4ABWQ5V2XQEP3A48VE1A', $this->validator);

        $original_event = GroupChatRenamed::create(
            $aggregate_id,
            $name,
            2,
            $executor_id
        );

        $data = $original_event->toArray();
        $event = GroupChatRenamed::fromArray($data, $this->validator);

        $this->assertInstanceOf(GroupChatRenamed::class, $event);
        $this->assertEquals($aggregate_id->toString(), $event->getAggregateId());
    }

    public function test_ラウンドトリップでデータが保持される(): void
    {
        $aggregate_id = GroupChatId::fromString('01H42K4ABWQ5V2XQEP3A48VE0Z', $this->validator);
        $name = new GroupChatName('New Name');
        $executor_id = UserAccountId::fromString('01H42K4ABWQ5V2XQEP3A48VE1A', $this->validator);

        $original_event = GroupChatRenamed::create(
            $aggregate_id,
            $name,
            2,
            $executor_id
        );

        $array = $original_event->toArray();
        $restored_event = GroupChatRenamed::fromArray($array, $this->validator);

        $this->assertEquals($original_event->getAggregateId(), $restored_event->getAggregateId());
        $this->assertEquals($original_event->getSeqNr(), $restored_event->getSeqNr());
        $this->assertEquals($original_event->getTypeName(), $restored_event->getTypeName());
    }
}
