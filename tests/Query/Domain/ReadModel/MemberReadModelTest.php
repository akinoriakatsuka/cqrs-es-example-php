<?php

declare(strict_types=1);

namespace Tests\Query\Domain\ReadModel;

use Akinoriakatsuka\CqrsEsExamplePhp\Query\Domain\ReadModel\MemberReadModel;
use PHPUnit\Framework\TestCase;

final class MemberReadModelTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('fromArrayProvider')]
    public function testFromArray(array $data, MemberReadModel $expected): void
    {
        $read_model = MemberReadModel::fromArray($data);

        $this->assertSame($expected->id, $read_model->id);
        $this->assertSame($expected->group_chat_id, $read_model->group_chat_id);
        $this->assertSame($expected->user_account_id, $read_model->user_account_id);
        $this->assertSame($expected->role, $read_model->role);
        $this->assertSame($expected->created_at, $read_model->created_at);
        $this->assertSame($expected->updated_at, $read_model->updated_at);
    }

    public static function fromArrayProvider(): array
    {
        return [
            '正常なデータ' => [
                [
                    'id' => 'member-123',
                    'group_chat_id' => 'gc-456',
                    'user_account_id' => 'user-789',
                    'role' => 'admin',
                    'created_at' => '2024-01-01 00:00:00',
                    'updated_at' => '2024-01-02 00:00:00',
                ],
                new MemberReadModel(
                    id: 'member-123',
                    group_chat_id: 'gc-456',
                    user_account_id: 'user-789',
                    role: 'admin',
                    created_at: '2024-01-01 00:00:00',
                    updated_at: '2024-01-02 00:00:00'
                ),
            ],
        ];
    }
}
