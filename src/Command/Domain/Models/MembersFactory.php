<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

final readonly class MembersFactory
{
    public function __construct(
        private MemberFactory $memberFactory
    ) {
    }

    public function fromArray(array $data): Members
    {
        // values配列があることを前提に
        $values = $data['values'] ?? [];
        $members = array_map(
            fn ($member_data) => $this->memberFactory->fromArray($member_data),
            $values
        );
        return Members::fromMembers($members);
    }
}
