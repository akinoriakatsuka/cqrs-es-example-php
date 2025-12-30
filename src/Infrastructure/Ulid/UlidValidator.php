<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid;

interface UlidValidator
{
    /**
     * ULID文字列が有効かどうかを検証する
     *
     * @param string $value 検証するULID文字列
     * @return bool 有効な場合true
     */
    public function isValid(string $value): bool;
}
