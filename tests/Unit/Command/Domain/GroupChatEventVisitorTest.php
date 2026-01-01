<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatCreated;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatDeleted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberAdded;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberRemoved;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessageDeleted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessageEdited;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessagePosted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatRenamed;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChatEventVisitor;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChatEventVisitorImpl;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Member;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Members;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Message;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Messages;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Role;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidValidator;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the GroupChatEventVisitor implementation
 * Verifies that the Visitor pattern correctly applies events to aggregates
 */
class GroupChatEventVisitorTest extends TestCase
{
    private RobinvdvleutenUlidGenerator $generator;
    private RobinvdvleutenUlidValidator $validator;
    private GroupChatIdFactory $group_chat_id_factory;
    private UserAccountIdFactory $user_account_id_factory;
    private MemberIdFactory $member_id_factory;
    private MessageIdFactory $message_id_factory;
    private GroupChatEventVisitor $visitor;

    protected function setUp(): void
    {
        $this->generator = new RobinvdvleutenUlidGenerator();
        $this->validator = new RobinvdvleutenUlidValidator();
        $this->group_chat_id_factory = new GroupChatIdFactory($this->generator, $this->validator);
        $this->user_account_id_factory = new UserAccountIdFactory($this->generator, $this->validator);
        $this->member_id_factory = new MemberIdFactory($this->generator, $this->validator);
        $this->message_id_factory = new MessageIdFactory($this->generator, $this->validator);
        $this->visitor = new GroupChatEventVisitorImpl();
    }

    /**
     * @test
     */
    public function test_visitorImplementsInterface(): void
    {
        $this->assertInstanceOf(GroupChatEventVisitor::class, $this->visitor);
    }

    /**
     * @test
     */
    public function test_acceptMethodDelegatesCorrectlyForGroupChatCreated(): void
    {
        // Arrange
        $id = $this->group_chat_id_factory->create();
        $name = new GroupChatName('Test Group');
        $executor_id = $this->user_account_id_factory->create();
        $members = Members::create($executor_id, $this->member_id_factory);

        $event = GroupChatCreated::create($id, $name, $members, 1, $executor_id);

        // Create initial aggregate (won't be used for Created event, but needed for accept method)
        $initial_aggregate = GroupChat::fromSnapshot(
            $this->group_chat_id_factory->create(),
            new GroupChatName('Initial'),
            Members::create($this->user_account_id_factory->create(), $this->member_id_factory),
            Messages::create(),
            0,
            0,
            false
        );

        // Act
        $result = $event->accept($this->visitor, $initial_aggregate);

        // Assert
        $this->assertEquals($id->toString(), $result->getId()->toString());
        $this->assertEquals($name->toString(), $result->getName()->toString());
        $this->assertEquals(1, $result->getVersion());
    }

    /**
     * @test
     */
    public function test_acceptMethodDelegatesCorrectlyForGroupChatRenamed(): void
    {
        // Arrange
        $id = $this->group_chat_id_factory->create();
        $old_name = new GroupChatName('Old Name');
        $new_name = new GroupChatName('New Name');
        $executor_id = $this->user_account_id_factory->create();

        $aggregate = GroupChat::fromSnapshot(
            $id,
            $old_name,
            Members::create($executor_id, $this->member_id_factory),
            Messages::create(),
            1,
            1,
            false
        );

        $event = GroupChatRenamed::create($id, $new_name, 2, $executor_id);

        // Act
        $result = $event->accept($this->visitor, $aggregate);

        // Assert
        $this->assertEquals($id->toString(), $result->getId()->toString());
        $this->assertEquals($new_name->toString(), $result->getName()->toString());
        $this->assertEquals(2, $result->getVersion());
    }

    /**
     * @test
     */
    public function test_acceptMethodDelegatesCorrectlyForGroupChatDeleted(): void
    {
        // Arrange
        $id = $this->group_chat_id_factory->create();
        $name = new GroupChatName('Test Group');
        $executor_id = $this->user_account_id_factory->create();

        $aggregate = GroupChat::fromSnapshot(
            $id,
            $name,
            Members::create($executor_id, $this->member_id_factory),
            Messages::create(),
            1,
            1,
            false
        );

        $event = GroupChatDeleted::create($id, 2, $executor_id);

        // Act
        $result = $event->accept($this->visitor, $aggregate);

        // Assert
        $this->assertEquals($id->toString(), $result->getId()->toString());
        $this->assertTrue($result->isDeleted());
        $this->assertEquals(2, $result->getVersion());
    }

    /**
     * @test
     */
    public function test_extensibilityWithCustomVisitor(): void
    {
        // This test demonstrates that new visitor implementations can be created
        // without modifying the existing code (Open/Closed Principle)

        // Create a custom visitor that counts events
        $counting_visitor = new class implements GroupChatEventVisitor {
            public int $visit_count = 0;

            public function visitCreated(GroupChatCreated $event, GroupChat $aggregate): GroupChat
            {
                $this->visit_count++;
                return $aggregate;
            }

            public function visitRenamed(GroupChatRenamed $event, GroupChat $aggregate): GroupChat
            {
                $this->visit_count++;
                return $aggregate;
            }

            public function visitDeleted(GroupChatDeleted $event, GroupChat $aggregate): GroupChat
            {
                $this->visit_count++;
                return $aggregate;
            }

            public function visitMemberAdded(GroupChatMemberAdded $event, GroupChat $aggregate): GroupChat
            {
                $this->visit_count++;
                return $aggregate;
            }

            public function visitMemberRemoved(GroupChatMemberRemoved $event, GroupChat $aggregate): GroupChat
            {
                $this->visit_count++;
                return $aggregate;
            }

            public function visitMessagePosted(GroupChatMessagePosted $event, GroupChat $aggregate): GroupChat
            {
                $this->visit_count++;
                return $aggregate;
            }

            public function visitMessageEdited(GroupChatMessageEdited $event, GroupChat $aggregate): GroupChat
            {
                $this->visit_count++;
                return $aggregate;
            }

            public function visitMessageDeleted(GroupChatMessageDeleted $event, GroupChat $aggregate): GroupChat
            {
                $this->visit_count++;
                return $aggregate;
            }
        };

        // Arrange
        $id = $this->group_chat_id_factory->create();
        $executor_id = $this->user_account_id_factory->create();
        $aggregate = GroupChat::fromSnapshot(
            $id,
            new GroupChatName('Test'),
            Members::create($executor_id, $this->member_id_factory),
            Messages::create(),
            1,
            1,
            false
        );

        $event = GroupChatRenamed::create($id, new GroupChatName('New'), 2, $executor_id);

        // Act
        $event->accept($counting_visitor, $aggregate);

        // Assert
        $this->assertEquals(1, $counting_visitor->visit_count);
    }
}