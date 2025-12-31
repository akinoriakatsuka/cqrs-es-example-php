<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Query\Domain\ReadModel;

/**
 * Message のReadModel（Query側専用DTO）
 */
final class MessageReadModel
{
    public function __construct(
        public readonly string $id,
        public readonly string $group_chat_id,
        public readonly string $user_account_id,
        public readonly string $text,
        public readonly string $created_at,
        public readonly string $updated_at,
        public readonly int $disabled
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
            text: (string)$data['text'],
            created_at: (string)$data['created_at'],
            updated_at: (string)$data['updated_at'],
            disabled: (int)$data['disabled']
        );
    }
}
