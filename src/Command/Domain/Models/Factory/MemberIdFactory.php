<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberId;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\Ulid;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidValidator;
use InvalidArgumentException;

final readonly class MemberIdFactory
{
    private const string TYPE_PREFIX = 'Member';

    public function __construct(
        private UlidGenerator $generator,
        private UlidValidator $validator
    ) {
    }

    public function create(): MemberId
    {
        $ulid = Ulid::generate($this->generator);
        return MemberId::from($ulid);
    }

    public function fromString(string $value): MemberId
    {
        // プレフィックスが付いている場合は削除
        $value = $this->removePrefix($value);

        // バリデーションとUlidオブジェクト生成
        $ulid = Ulid::fromString($value, $this->validator);

        // ドメインモデル生成
        return MemberId::from($ulid);
    }

    public function fromArray(array $data): MemberId
    {
        // 配列から値を取得
        if (!isset($data['value'])) {
            throw new InvalidArgumentException('value is required');
        }

        return $this->fromString($data['value']);
    }

    private function removePrefix(string $value): string
    {
        if (str_starts_with($value, self::TYPE_PREFIX . '-')) {
            return substr($value, strlen(self::TYPE_PREFIX) + 1);
        }
        return $value;
    }
}
