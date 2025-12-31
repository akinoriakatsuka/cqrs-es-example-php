<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Query\Domain\ReadModel;

/**
 * Member のReadModel（Query側専用DTO）
 */
final class MemberReadModel
{
    public function __construct(
        public readonly string $id,
        public readonly string $group_chat_id,
        public readonly string $user_account_id,
        public readonly string $role,
        public readonly string $created_at,
        public readonly string $updated_at
    ) {
    }

    /**
     * データベースの配列からReadModelを生成
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string)$data['id'],
            group_chat_id: (string)$data['group_chat_id'],
            user_account_id: (string)$data['user_account_id'],
            role: (string)$data['role'],
            created_at: (string)$data['created_at'],
            updated_at: (string)$data['updated_at']
        );
    }
}
