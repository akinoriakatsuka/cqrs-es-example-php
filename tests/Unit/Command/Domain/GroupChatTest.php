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
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Member;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Members;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Message;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Messages;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Role;
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

    public function test_rename_正常にリネームできる(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Old Name');
        $executor_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $executor_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $new_name = new GroupChatName('New Name');
        $result_pair = $group_chat->rename($new_name, $executor_id, $this->generator);

        $this->assertEquals('New Name', $result_pair->getGroupChat()->getName()->toString());
        $this->assertEquals(2, $result_pair->getGroupChat()->getSeqNr());
    }

    public function test_rename_削除済みのグループチャットはリネームできない(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $executor_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $executor_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $deleted_pair = $group_chat->delete($executor_id, $this->generator);
        $deleted_group_chat = $deleted_pair->getGroupChat();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('The group chat is deleted');

        $new_name = new GroupChatName('New Name');
        $deleted_group_chat->rename($new_name, $executor_id, $this->generator);
    }

    public function test_rename_メンバーでない場合はエラー(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $executor_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $executor_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $non_member_id = UserAccountId::generate($this->generator);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('The executorId is not the member of the group chat');

        $new_name = new GroupChatName('New Name');
        $group_chat->rename($new_name, $non_member_id, $this->generator);
    }

    public function test_rename_管理者でない場合はエラー(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $admin_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $admin_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        // Add a regular member
        $member_id = UserAccountId::generate($this->generator);
        $add_member_pair = $group_chat->addMember(
            MemberId::generate($this->generator),
            $member_id,
            Role::MEMBER,
            $admin_id,
            $this->generator
        );
        $updated_group_chat = $add_member_pair->getGroupChat();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('The executorId is not an administrator of the group chat');

        $new_name = new GroupChatName('New Name');
        $updated_group_chat->rename($new_name, $member_id, $this->generator);
    }

    public function test_rename_同じ名前ではエラー(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $executor_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $executor_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('The name is already the same as the current name');

        $same_name = new GroupChatName('Test Group');
        $group_chat->rename($same_name, $executor_id, $this->generator);
    }

    public function test_delete_正常に削除できる(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $executor_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $executor_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $result_pair = $group_chat->delete($executor_id, $this->generator);

        $this->assertTrue($result_pair->getGroupChat()->isDeleted());
        $this->assertEquals(2, $result_pair->getGroupChat()->getSeqNr());
    }

    public function test_delete_既に削除済みの場合はエラー(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $executor_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $executor_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $deleted_pair = $group_chat->delete($executor_id, $this->generator);
        $deleted_group_chat = $deleted_pair->getGroupChat();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('The group chat is deleted');

        $deleted_group_chat->delete($executor_id, $this->generator);
    }

    public function test_addMember_正常にメンバーを追加できる(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $admin_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $admin_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $new_user_id = UserAccountId::generate($this->generator);
        $result_pair = $group_chat->addMember(
            MemberId::generate($this->generator),
            $new_user_id,
            Role::MEMBER,
            $admin_id,
            $this->generator
        );

        $this->assertTrue($result_pair->getGroupChat()->isMember($new_user_id));
        $this->assertFalse($result_pair->getGroupChat()->isAdministrator($new_user_id));
    }

    public function test_addMember_既存メンバーは追加できない(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $admin_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $admin_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('The userAccountId is already the member of the group chat');

        $group_chat->addMember(
            MemberId::generate($this->generator),
            $admin_id,
            Role::MEMBER,
            $admin_id,
            $this->generator
        );
    }

    public function test_removeMember_正常にメンバーを削除できる(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $admin_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $admin_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $new_user_id = UserAccountId::generate($this->generator);
        $add_pair = $group_chat->addMember(
            MemberId::generate($this->generator),
            $new_user_id,
            Role::MEMBER,
            $admin_id,
            $this->generator
        );

        $result_pair = $add_pair->getGroupChat()->removeMember($new_user_id, $admin_id, $this->generator);

        $this->assertFalse($result_pair->getGroupChat()->isMember($new_user_id));
    }

    public function test_removeMember_存在しないメンバーは削除できない(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $admin_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $admin_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $non_member_id = UserAccountId::generate($this->generator);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('The userAccountId is not a member of the group chat');

        $group_chat->removeMember($non_member_id, $admin_id, $this->generator);
    }

    public function test_postMessage_正常にメッセージを投稿できる(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $sender_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $sender_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $message_id = MessageId::generate($this->generator);
        $result_pair = $group_chat->postMessage($message_id, 'Hello, World!', $sender_id, $this->generator);

        $message = $result_pair->getGroupChat()->getMessages()->findById($message_id);
        $this->assertNotNull($message);
        $this->assertEquals('Hello, World!', $message->getText());
    }

    public function test_postMessage_メンバーでない場合はエラー(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $admin_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $admin_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $non_member_id = UserAccountId::generate($this->generator);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('The senderId is not a member of the group chat');

        $message_id = MessageId::generate($this->generator);
        $group_chat->postMessage($message_id, 'Hello!', $non_member_id, $this->generator);
    }

    public function test_editMessage_正常にメッセージを編集できる(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $sender_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $sender_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $message_id = MessageId::generate($this->generator);
        $post_pair = $group_chat->postMessage($message_id, 'Original Text', $sender_id, $this->generator);

        $result_pair = $post_pair->getGroupChat()->editMessage($message_id, 'Edited Text', $sender_id, $this->generator);

        $message = $result_pair->getGroupChat()->getMessages()->findById($message_id);
        $this->assertNotNull($message);
        $this->assertEquals('Edited Text', $message->getText());
    }

    public function test_deleteMessage_正常にメッセージを削除できる(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $sender_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $sender_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $message_id = MessageId::generate($this->generator);
        $post_pair = $group_chat->postMessage($message_id, 'To be deleted', $sender_id, $this->generator);

        $result_pair = $post_pair->getGroupChat()->deleteMessage($message_id, $sender_id, $this->generator);

        $message = $result_pair->getGroupChat()->getMessages()->findById($message_id);
        $this->assertNull($message);
    }

    public function test_getters_各種ゲッターが正しく動作する(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $executor_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $executor_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $this->assertEquals($id->toString(), $group_chat->getId()->toString());
        $this->assertEquals('Test Group', $group_chat->getName()->toString());
        $this->assertEquals(1, $group_chat->getSeqNr());
        $this->assertEquals(1, $group_chat->getVersion());
        $this->assertFalse($group_chat->isDeleted());
        $this->assertInstanceOf(Members::class, $group_chat->getMembers());
        $this->assertInstanceOf(Messages::class, $group_chat->getMessages());
    }

    public function test_fromSnapshot_スナップショットから復元できる(): void
    {

        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Snapshot Group');
        $executor_id = UserAccountId::generate($this->generator);
        $members = Members::create($executor_id, $this->generator);
        $messages = Messages::create();

        $group_chat = GroupChat::fromSnapshot($id, $name, $members, $messages, 5, 3, false);

        $this->assertEquals($id->toString(), $group_chat->getId()->toString());
        $this->assertEquals('Snapshot Group', $group_chat->getName()->toString());
        $this->assertEquals(5, $group_chat->getSeqNr());
        $this->assertEquals(3, $group_chat->getVersion());
        $this->assertFalse($group_chat->isDeleted());
    }

    public function test_fromEvent_イベントから復元できる(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Event Group');
        $executor_id = UserAccountId::generate($this->generator);

        $members = Members::create($executor_id, $this->generator);
        $event = GroupChatCreated::create($id, $name, $members, 1, $executor_id, $this->generator);

        $group_chat = GroupChat::fromEvent($event);

        $this->assertEquals($id->toString(), $group_chat->getId()->toString());
        $this->assertEquals('Event Group', $group_chat->getName()->toString());
        $this->assertEquals(1, $group_chat->getSeqNr());
        $this->assertEquals(1, $group_chat->getVersion());
        $this->assertFalse($group_chat->isDeleted());
    }

    public function test_applyEvent_GroupChatCreatedイベントを適用できる(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $executor_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $executor_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $new_id = GroupChatId::generate($this->generator);
        $new_name = new GroupChatName('New Group');
        $new_members = Members::create($executor_id, $this->generator);
        $created_event = GroupChatCreated::create($new_id, $new_name, $new_members, 1, $executor_id, $this->generator);

        $new_group_chat = $group_chat->applyEvent($created_event);

        $this->assertEquals($new_id->toString(), $new_group_chat->getId()->toString());
        $this->assertEquals('New Group', $new_group_chat->getName()->toString());
    }

    public function test_applyEvent_GroupChatRenamedイベントを適用できる(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Old Name');
        $executor_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $executor_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $new_name = new GroupChatName('Renamed');
        $renamed_event = GroupChatRenamed::create($id, $new_name, 2, $executor_id, $this->generator);

        $new_group_chat = $group_chat->applyEvent($renamed_event);

        $this->assertEquals('Renamed', $new_group_chat->getName()->toString());
        $this->assertEquals(2, $new_group_chat->getSeqNr());
        $this->assertEquals(2, $new_group_chat->getVersion());
    }

    public function test_applyEvent_GroupChatDeletedイベントを適用できる(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $executor_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $executor_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $deleted_event = GroupChatDeleted::create($id, 2, $executor_id, $this->generator);

        $new_group_chat = $group_chat->applyEvent($deleted_event);

        $this->assertTrue($new_group_chat->isDeleted());
        $this->assertEquals(2, $new_group_chat->getSeqNr());
        $this->assertEquals(2, $new_group_chat->getVersion());
    }

    public function test_applyEvent_GroupChatMemberAddedイベントを適用できる(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $admin_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $admin_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $new_user_id = UserAccountId::generate($this->generator);
        $new_member = new Member(MemberId::generate($this->generator), $new_user_id, Role::MEMBER);
        $member_added_event = GroupChatMemberAdded::create($id, $new_member, 2, $admin_id, $this->generator);

        $new_group_chat = $group_chat->applyEvent($member_added_event);

        $this->assertTrue($new_group_chat->isMember($new_user_id));
    }

    public function test_applyEvent_GroupChatMemberRemovedイベントを適用できる(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $admin_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $admin_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $user_id = UserAccountId::generate($this->generator);
        $add_pair = $group_chat->addMember(
            MemberId::generate($this->generator),
            $user_id,
            Role::MEMBER,
            $admin_id,
            $this->generator
        );
        $updated_group_chat = $add_pair->getGroupChat();

        $member_removed_event = GroupChatMemberRemoved::create($id, $user_id, 3, $admin_id, $this->generator);

        $new_group_chat = $updated_group_chat->applyEvent($member_removed_event);

        $this->assertFalse($new_group_chat->isMember($user_id));
    }

    public function test_applyEvent_GroupChatMessagePostedイベントを適用できる(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $sender_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $sender_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $message_id = MessageId::generate($this->generator);
        $message = new Message($message_id, 'Hello', $sender_id);
        $message_posted_event = GroupChatMessagePosted::create($id, $message, 2, $sender_id, $this->generator);

        $new_group_chat = $group_chat->applyEvent($message_posted_event);

        $found_message = $new_group_chat->getMessages()->findById($message_id);
        $this->assertNotNull($found_message);
        $this->assertEquals('Hello', $found_message->getText());
    }

    public function test_applyEvent_GroupChatMessageEditedイベントを適用できる(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $sender_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $sender_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $message_id = MessageId::generate($this->generator);
        $post_pair = $group_chat->postMessage($message_id, 'Original', $sender_id, $this->generator);
        $updated_group_chat = $post_pair->getGroupChat();

        $edited_message = new Message($message_id, 'Edited', $sender_id);
        $message_edited_event = GroupChatMessageEdited::create($id, $edited_message, 3, $sender_id, $this->generator);

        $new_group_chat = $updated_group_chat->applyEvent($message_edited_event);

        $found_message = $new_group_chat->getMessages()->findById($message_id);
        $this->assertNotNull($found_message);
        $this->assertEquals('Edited', $found_message->getText());
    }

    public function test_applyEvent_GroupChatMessageDeletedイベントを適用できる(): void
    {
        $id = GroupChatId::generate($this->generator);
        $name = new GroupChatName('Test Group');
        $sender_id = UserAccountId::generate($this->generator);

        $pair = GroupChat::create($id, $name, $sender_id, $this->generator);
        $group_chat = $pair->getGroupChat();

        $message_id = MessageId::generate($this->generator);
        $post_pair = $group_chat->postMessage($message_id, 'To be deleted', $sender_id, $this->generator);
        $updated_group_chat = $post_pair->getGroupChat();

        $message_deleted_event = GroupChatMessageDeleted::create($id, $message_id, 3, $sender_id, $this->generator);

        $new_group_chat = $updated_group_chat->applyEvent($message_deleted_event);

        $found_message = $new_group_chat->getMessages()->findById($message_id);
        $this->assertNull($found_message);
    }
}
