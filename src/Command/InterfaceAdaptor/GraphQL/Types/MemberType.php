<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Member;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class MemberType extends ObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'Member',
            'description' => 'A group chat member',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The unique identifier of the member',
                    'resolve' => static fn (Member $member): string => $member->getId()->getValue(),
                ],
                'userAccountId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The user account ID of the member',
                    'resolve' => static fn (Member $member): string => $member->getUserAccountId()->getValue(),
                ],
                'role' => [
                    'type' => Type::nonNull(TypeRegistry::memberRoleType()),
                    'description' => 'The role of the member',
                    'resolve' => static fn (Member $member): string => $member->getRole()->value,
                ],
                'joinedAt' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'When the member joined the group chat',
                    'resolve' => static fn (Member $member): string => $member->getJoinedAt()->format('c'),
                ],
            ],
        ]);
    }
}
