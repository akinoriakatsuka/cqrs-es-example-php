<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Common;

/**
 * Pair represents a tuple of two values.
 * @template T1
 * @template T2
 */
class Pair
{
    /**
     * @param T1 $first
     * @param T2 $second
     */
    public function __construct(
        private readonly mixed $first,
        private readonly mixed $second
    ) {
    }

    /**
     * @return T1
     */
    public function first(): mixed
    {
        return $this->first;
    }

    /**
     * @return T2
     */
    public function second(): mixed
    {
        return $this->second;
    }

    /**
     * @template U1
     * @template U2
     * @param U1 $first
     * @param U2 $second
     * @return self<U1, U2>
     */
    public static function of(mixed $first, mixed $second): self
    {
        return new self($first, $second);
    }
}
