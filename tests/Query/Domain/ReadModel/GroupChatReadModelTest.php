<?php

declare(strict_types=1);

namespace Tests\Query\Domain\ReadModel;

use Akinoriakatsuka\CqrsEsExamplePhp\Query\Domain\Exception\InvalidReadModelDataException;
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

    #[\PHPUnit\Framework\Attributes\DataProvider('fromArrayValidationErrorProvider')]
    public function testFromArrayValidationError(array $data, string $expected_field): void
    {
        $this->expectException(InvalidReadModelDataException::class);
        $this->expectExceptionMessage(sprintf('Required field "%s" is missing or empty', $expected_field));

        GroupChatReadModel::fromArray($data);
    }

    public static function fromArrayValidationErrorProvider(): array
    {
        $valid_data = [
            'id' => 'gc-123',
            'name' => 'テストグループ',
            'owner_id' => 'user-456',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-02 00:00:00',
            'disabled' => 0,
        ];

        return [
            'idが未設定' => [
                array_diff_key($valid_data, ['id' => null]),
                'id',
            ],
            'idが空文字' => [
                array_merge($valid_data, ['id' => '']),
                'id',
            ],
            'nameが未設定' => [
                array_diff_key($valid_data, ['name' => null]),
                'name',
            ],
            'nameが空文字' => [
                array_merge($valid_data, ['name' => '']),
                'name',
            ],
            'owner_idが未設定' => [
                array_diff_key($valid_data, ['owner_id' => null]),
                'owner_id',
            ],
            'owner_idが空文字' => [
                array_merge($valid_data, ['owner_id' => '']),
                'owner_id',
            ],
            'created_atが未設定' => [
                array_diff_key($valid_data, ['created_at' => null]),
                'created_at',
            ],
            'created_atが空文字' => [
                array_merge($valid_data, ['created_at' => '']),
                'created_at',
            ],
            'updated_atが未設定' => [
                array_diff_key($valid_data, ['updated_at' => null]),
                'updated_at',
            ],
            'updated_atが空文字' => [
                array_merge($valid_data, ['updated_at' => '']),
                'updated_at',
            ],
            'disabledが未設定' => [
                array_diff_key($valid_data, ['disabled' => null]),
                'disabled',
            ],
        ];
    }
}
