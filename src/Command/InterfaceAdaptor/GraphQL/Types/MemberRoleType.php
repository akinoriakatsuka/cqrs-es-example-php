<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberRole;
use GraphQL\Type\Definition\EnumType;

final class MemberRoleType extends EnumType {
    public function __construct() {
        parent::__construct([
            'name' => 'MemberRole',
            'description' => 'Group chat member role',
            'values' => [
                'MEMBER' => [
                    'value' => MemberRole::MEMBER_ROLE,
                    'description' => 'Regular member',
                ],
                'ADMIN' => [
                    'value' => MemberRole::ADMIN_ROLE,
                    'description' => 'Administrator member',
                ],
            ],
        ]);
    }
}
