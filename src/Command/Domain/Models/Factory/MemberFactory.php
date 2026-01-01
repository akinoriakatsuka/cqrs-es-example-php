<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Member;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Role;

final readonly class MemberFactory
{
    public function __construct(
        private UserAccountIdFactory $userAccountIdFactory,
        private MemberIdFactory $memberIdFactory
    ) {
    }

    public function fromArray(array $data): Member
    {
        return new Member(
            $this->memberIdFactory->fromArray($data['id'] ?? []),
            $this->userAccountIdFactory->fromArray($data['user_account_id'] ?? []),
            Role::from((int)$data['role'])
        );
    }
}
