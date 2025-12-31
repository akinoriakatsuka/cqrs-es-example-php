<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatDeleted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidValidator;
use PHPUnit\Framework\TestCase;

class GroupChatDeletedTest extends TestCase
{
    private RobinvdvleutenUlidValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new RobinvdvleutenUlidValidator();
    }

    public function test_正常に生成できる(): void
    {
        $aggregate_id = GroupChatId::fromString('01H42K4ABWQ5V2XQEP3A48VE0Z', $this->validator);
        $executor_id = UserAccountId::fromString('01H42K4ABWQ5V2XQEP3A48VE1A', $this->validator);

        $event = GroupChatDeleted::create(
            $aggregate_id,
            1,
            $executor_id
        );

        $this->assertInstanceOf(GroupChatDeleted::class, $event);
        $this->assertEquals($aggregate_id->toString(), $event->getAggregateId());
        $this->assertEquals('GroupChatDeleted', $event->getTypeName());
        $this->assertFalse($event->isCreated());
        $this->assertEquals(1, $event->getSeqNr());
    }

    public function test_toArrayでシリアライズできる(): void
    {
        $aggregate_id = GroupChatId::fromString('01H42K4ABWQ5V2XQEP3A48VE0Z', $this->validator);
        $executor_id = UserAccountId::fromString('01H42K4ABWQ5V2XQEP3A48VE1A', $this->validator);

        $event = GroupChatDeleted::create(
            $aggregate_id,
            1,
            $executor_id
        );

        $array = $event->toArray();

        $this->assertEquals('GroupChatDeleted', $array['type_name']);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('aggregate_id', $array);
        $this->assertArrayHasKey('executor_id', $array);
        $this->assertEquals(1, $array['seq_nr']);
        $this->assertArrayHasKey('occurred_at', $array);
    }

    public function test_fromArrayでデシリアライズできる(): void
    {
        $aggregate_id = GroupChatId::fromString('01H42K4ABWQ5V2XQEP3A48VE0Z', $this->validator);
        $executor_id = UserAccountId::fromString('01H42K4ABWQ5V2XQEP3A48VE1A', $this->validator);

        $original_event = GroupChatDeleted::create(
            $aggregate_id,
            1,
            $executor_id
        );

        $data = $original_event->toArray();
        $event = GroupChatDeleted::fromArray($data, $this->validator);

        $this->assertInstanceOf(GroupChatDeleted::class, $event);
        $this->assertEquals($aggregate_id->toString(), $event->getAggregateId());
    }

    public function test_ラウンドトリップでデータが保持される(): void
    {
        $aggregate_id = GroupChatId::fromString('01H42K4ABWQ5V2XQEP3A48VE0Z', $this->validator);
        $executor_id = UserAccountId::fromString('01H42K4ABWQ5V2XQEP3A48VE1A', $this->validator);

        $original_event = GroupChatDeleted::create(
            $aggregate_id,
            1,
            $executor_id
        );

        $array = $original_event->toArray();
        $restored_event = GroupChatDeleted::fromArray($array, $this->validator);

        $this->assertEquals($original_event->getAggregateId(), $restored_event->getAggregateId());
        $this->assertEquals($original_event->getSeqNr(), $restored_event->getSeqNr());
        $this->assertEquals($original_event->getTypeName(), $restored_event->getTypeName());
        $this->assertEquals($original_event->getId(), $restored_event->getId());
        $this->assertEquals($original_event->getOccurredAt(), $restored_event->getOccurredAt());
    }
}
