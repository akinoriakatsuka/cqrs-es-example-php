<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Query\Domain\Exception;

use InvalidArgumentException;

/**
 * ReadModelのデータが不正な場合にスローされる例外
 */
final class InvalidReadModelDataException extends InvalidArgumentException
{
    public static function missingRequiredField(string $field_name, string $model_class): self
    {
        return new self(
            sprintf('Required field "%s" is missing or empty in %s', $field_name, $model_class)
        );
    }

    public static function invalidFieldType(string $field_name, string $expected_type, mixed $actual_value, string $model_class): self
    {
        $actual_type = get_debug_type($actual_value);
        return new self(
            sprintf(
                'Field "%s" must be of type %s, %s given in %s',
                $field_name,
                $expected_type,
                $actual_type,
                $model_class
            )
        );
    }
}
