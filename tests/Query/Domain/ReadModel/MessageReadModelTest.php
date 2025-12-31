<?php

declare(strict_types=1);

namespace Tests\Query\Domain\ReadModel;

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

    #[\PHPUnit\Framework\Attributes\DataProvider('toArrayProvider')]
    public function testToArray(MessageReadModel $read_model, array $expected): void
    {
        $result = $read_model->toArray();

        $this->assertSame($expected, $result);
    }

    public static function toArrayProvider(): array
    {
        return [
            '正常な変換' => [
                new MessageReadModel(
                    id: 'msg-123',
                    group_chat_id: 'gc-456',
                    user_account_id: 'user-789',
                    text: 'テストメッセージ',
                    created_at: '2024-01-01 00:00:00',
                    updated_at: '2024-01-02 00:00:00',
                    disabled: 0
                ),
                [
                    'id' => 'msg-123',
                    'group_chat_id' => 'gc-456',
                    'user_account_id' => 'user-789',
                    'text' => 'テストメッセージ',
                    'created_at' => '2024-01-01 00:00:00',
                    'updated_at' => '2024-01-02 00:00:00',
                    'disabled' => 0,
                ],
            ],
        ];
    }
}
