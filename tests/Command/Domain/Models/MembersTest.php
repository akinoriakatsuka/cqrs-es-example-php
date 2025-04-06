<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain\Models;

use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Members;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Member;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberRole;

class MembersTest extends TestCase {
    public function testCreate(): void {
        $userAccountId = new UserAccountId();
        $members = Members::create($userAccountId);

        $this->assertCount(1, $members->getValues());

        $member = $members->getValues()[0];
        $this->assertTrue($member->getUserAccountId()->equals($userAccountId));
        $this->assertSame(MemberRole::ADMIN_ROLE, $member->getRole());
    }

    public function testGetValues(): void {
        $userAccountId = new UserAccountId();
        $members = Members::create($userAccountId);

        $values = $members->getValues();
        $this->assertCount(1, $values);
    }

    public function testAddMember(): void {
        $adminId = new UserAccountId();
        $members = Members::create($adminId);

        $newUserId = new UserAccountId();
        $updatedMembers = $members->addMember($newUserId);

        // Original members should be unchanged
        $this->assertCount(1, $members->getValues());

        // New members collection should have 2 members
        $this->assertCount(2, $updatedMembers->getValues());

        // Check the new member
        $newMember = $updatedMembers->findByUserAccountId($newUserId);
        $this->assertNotNull($newMember);
        $this->assertTrue($newMember->getUserAccountId()->equals($newUserId));
        $this->assertSame(MemberRole::MEMBER_ROLE, $newMember->getRole());
    }

    public function testRemoveMember(): void {
        // Arrange
        $adminId = new UserAccountId();
        $members = Members::create($adminId);

        $memberId = new UserAccountId();
        $membersWithTwo = $members->addMember($memberId);

        // Act
        $updatedMembers = $membersWithTwo->removeMember($memberId);

        // Assert
        // Original members should be unchanged
        $this->assertCount(2, $membersWithTwo->getValues());

        // New members collection should have 1 member (admin only)
        $this->assertCount(1, $updatedMembers->getValues());

        // Admin should still be present
        $adminMember = $updatedMembers->findByUserAccountId($adminId);
        $this->assertNotNull($adminMember);

        // Removed member should not be present
        $removedMember = $updatedMembers->findByUserAccountId($memberId);
        $this->assertNull($removedMember);
    }

    public function testRemoveMemberWithNonExistentId(): void {
        // Arrange
        $adminId = new UserAccountId();
        $members = Members::create($adminId);

        // Act & Assert
        $nonExistentId = new UserAccountId();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Member not found with user account ID: " . $nonExistentId->getValue());

        $members->removeMember($nonExistentId);
    }

    public function testFindByUserAccountId(): void {
        $adminId = new UserAccountId();
        $members = Members::create($adminId);

        $newUserId = new UserAccountId();
        $updatedMembers = $members->addMember($newUserId);

        // Should find existing members
        $foundAdmin = $updatedMembers->findByUserAccountId($adminId);
        $this->assertNotNull($foundAdmin);
        $this->assertTrue($foundAdmin->getUserAccountId()->equals($adminId));

        $foundMember = $updatedMembers->findByUserAccountId($newUserId);
        $this->assertNotNull($foundMember);
        $this->assertTrue($foundMember->getUserAccountId()->equals($newUserId));

        // Should return null for non-existent member
        $nonExistentId = new UserAccountId();
        $notFound = $updatedMembers->findByUserAccountId($nonExistentId);
        $this->assertNull($notFound);
    }
}
