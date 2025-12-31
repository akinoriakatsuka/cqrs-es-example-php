<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Query\Domain\ReadModel;

/**
 * GroupChat のReadModel（Query側専用DTO）
 */
final class GroupChatReadModel
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $owner_id,
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
            name: (string)$data['name'],
            owner_id: (string)$data['owner_id'],
            created_at: (string)$data['created_at'],
            updated_at: (string)$data['updated_at'],
            disabled: (int)$data['disabled']
        );
    }

    /**
     * GraphQL用の配列に変換
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'owner_id' => $this->owner_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'disabled' => $this->disabled,
        ];
    }
}
