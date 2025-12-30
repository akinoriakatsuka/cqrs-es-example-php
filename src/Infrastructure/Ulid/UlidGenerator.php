<?php

declare(strict_types=1);

namespace App\Infrastructure\Ulid;

interface UlidGenerator
{
    /**
     * 新しいULIDを生成する
     *
     * @return string 生成されたULID文字列
     */
    public function generate(): string;
}
