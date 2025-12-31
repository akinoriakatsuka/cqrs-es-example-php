<?php

declare(strict_types=1);

namespace Tests\Query\Domain\ReadModel;

use Akinoriakatsuka\CqrsEsExamplePhp\Query\Domain\ReadModel\GroupChatReadModel;
use PHPUnit\Framework\TestCase;

final class GroupChatReadModelTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('fromArrayProvider')]
    public function testFromArray(array $data, GroupChatReadModel $expected): void
    {
        $read_model = GroupChatReadModel::fromArray($data);

        $this->assertSame($expected->id, $read_model->id);
        $this->assertSame($expected->name, $read_model->name);
        $this->assertSame($expected->owner_id, $read_model->owner_id);
        $this->assertSame($expected->created_at, $read_model->created_at);
        $this->assertSame($expected->updated_at, $read_model->updated_at);
        $this->assertSame($expected->disabled, $read_model->disabled);
    }

    public static function fromArrayProvider(): array
    {
        return [
            '正常なデータ' => [
                [
                    'id' => 'gc-123',
                    'name' => 'テストグループ',
                    'owner_id' => 'user-456',
                    'created_at' => '2024-01-01 00:00:00',
                    'updated_at' => '2024-01-02 00:00:00',
                    'disabled' => 0,
                ],
                new GroupChatReadModel(
                    id: 'gc-123',
                    name: 'テストグループ',
                    owner_id: 'user-456',
                    created_at: '2024-01-01 00:00:00',
                    updated_at: '2024-01-02 00:00:00',
                    disabled: 0
                ),
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('toArrayProvider')]
    public function testToArray(GroupChatReadModel $read_model, array $expected): void
    {
        $result = $read_model->toArray();

        $this->assertSame($expected, $result);
    }

    public static function toArrayProvider(): array
    {
        return [
            '正常な変換' => [
                new GroupChatReadModel(
                    id: 'gc-123',
                    name: 'テストグループ',
                    owner_id: 'user-456',
                    created_at: '2024-01-01 00:00:00',
                    updated_at: '2024-01-02 00:00:00',
                    disabled: 0
                ),
                [
                    'id' => 'gc-123',
                    'name' => 'テストグループ',
                    'owner_id' => 'user-456',
                    'created_at' => '2024-01-01 00:00:00',
                    'updated_at' => '2024-01-02 00:00:00',
                    'disabled' => 0,
                ],
            ],
        ];
    }
}
