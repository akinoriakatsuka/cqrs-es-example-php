<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Query\Domain\ReadModel;

use Akinoriakatsuka\CqrsEsExamplePhp\Query\Domain\Exception\InvalidReadModelDataException;

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
     * @throws InvalidReadModelDataException
     */
    public static function fromArray(array $data): self
    {
        self::validateRequiredField($data, 'id');
        self::validateRequiredField($data, 'group_chat_id');
        self::validateRequiredField($data, 'user_account_id');
        self::validateRequiredField($data, 'text');
        self::validateRequiredField($data, 'created_at');
        self::validateRequiredField($data, 'updated_at');
        self::validateRequiredField($data, 'disabled');

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

    /**
     * 必須フィールドのバリデーション
     *
     * @param array<string, mixed> $data
     * @param string $field_name
     * @throws InvalidReadModelDataException
     */
    private static function validateRequiredField(array $data, string $field_name): void
    {
        if (!isset($data[$field_name]) || (is_string($data[$field_name]) && $data[$field_name] === '')) {
            throw InvalidReadModelDataException::missingRequiredField($field_name, self::class);
        }
    }
}
