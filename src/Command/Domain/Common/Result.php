<?php

declare(strict_types=1);

namespace App\Command\Domain\Common;

use Exception;

/**
 * Result represents a value that is either a success (Ok) or a failure (Err).
 *
 * @template T
 */
final class Result
{
    private bool $is_ok;

    /**
     * @param T|null $value
     */
    private function __construct(
        private readonly mixed $value,
        private readonly ?Exception $error
    ) {
        $this->is_ok = $error === null;
    }

    /**
     * Creates a successful Result.
     *
     * @template U
     * @param U $value
     * @return self<U>
     */
    public static function ok(mixed $value): self
    {
        return new self($value, null);
    }

    /**
     * Creates a failed Result.
     *
     * @template U
     * @param Exception $error
     * @return self<U>
     */
    public static function err(Exception $error): self
    {
        return new self(null, $error);
    }

    /**
     * Returns true if this is a successful Result.
     */
    public function isOk(): bool
    {
        return $this->is_ok;
    }

    /**
     * Returns true if this is a failed Result.
     */
    public function isErr(): bool
    {
        return !$this->is_ok;
    }

    /**
     * Returns the success value.
     * Throws an exception if this is a failed Result.
     *
     * @return T
     * @throws Exception
     */
    public function unwrap(): mixed
    {
        if ($this->is_ok) {
            return $this->value;
        }

        throw $this->error;
    }

    /**
     * Returns the error.
     * Throws an exception if this is a successful Result.
     *
     * @throws Exception
     */
    public function unwrapErr(): Exception
    {
        if (!$this->is_ok) {
            return $this->error;
        }

        throw new Exception('Called unwrapErr on an Ok value');
    }

    /**
     * Returns the success value or a default value if this is a failed Result.
     *
     * @param T $default
     * @return T
     */
    public function unwrapOr(mixed $default): mixed
    {
        return $this->is_ok ? $this->value : $default;
    }
}
