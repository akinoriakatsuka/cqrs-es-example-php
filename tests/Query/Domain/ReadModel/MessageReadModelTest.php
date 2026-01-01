<?php

declare(strict_types=1);

namespace Tests\Query\Domain\ReadModel;

use Akinoriakatsuka\CqrsEsExamplePhp\Query\Domain\Exception\InvalidReadModelDataException;
use Akinoriakatsuka\CqrsEsExamplePhp\Query\Domain\ReadModel\MessageReadModel;
use PHPUnit\Framework\TestCase;

final class MessageReadModelTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('fromArrayProvider')]
    public function testFromArray(array $data, MessageReadModel $expected): void
    {
        $read_model = MessageReadModel::fromArray($data);

        $this->assertSame($expected->id, $read_model->id);
        $this->assertSame($expected->group_chat_id, $read_model->group_chat_id);
        $this->assertSame($expected->user_account_id, $read_model->user_account_id);
        $this->assertSame($expected->text, $read_model->text);
        $this->assertSame($expected->created_at, $read_model->created_at);
        $this->assertSame($expected->updated_at, $read_model->updated_at);
        $this->assertSame($expected->disabled, $read_model->disabled);
    }

    public static function fromArrayProvider(): array
    {
        return [
            '正常なデータ' => [
                [
                    'id' => 'msg-123',
                    'group_chat_id' => 'gc-456',
                    'user_account_id' => 'user-789',
                    'text' => 'テストメッセージ',
                    'created_at' => '2024-01-01 00:00:00',
                    'updated_at' => '2024-01-02 00:00:00',
                    'disabled' => 0,
                ],
                new MessageReadModel(
                    id: 'msg-123',
                    group_chat_id: 'gc-456',
                    user_account_id: 'user-789',
                    text: 'テストメッセージ',
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

        MessageReadModel::fromArray($data);
    }

    public static function fromArrayValidationErrorProvider(): array
    {
        $valid_data = [
            'id' => 'msg-123',
            'group_chat_id' => 'gc-456',
            'user_account_id' => 'user-789',
            'text' => 'テストメッセージ',
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
            'group_chat_idが未設定' => [
                array_diff_key($valid_data, ['group_chat_id' => null]),
                'group_chat_id',
            ],
            'group_chat_idが空文字' => [
                array_merge($valid_data, ['group_chat_id' => '']),
                'group_chat_id',
            ],
            'user_account_idが未設定' => [
                array_diff_key($valid_data, ['user_account_id' => null]),
                'user_account_id',
            ],
            'user_account_idが空文字' => [
                array_merge($valid_data, ['user_account_id' => '']),
                'user_account_id',
            ],
            'textが未設定' => [
                array_diff_key($valid_data, ['text' => null]),
                'text',
            ],
            'textが空文字' => [
                array_merge($valid_data, ['text' => '']),
                'text',
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
