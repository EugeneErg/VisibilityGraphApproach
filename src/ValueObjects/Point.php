<?php

declare(strict_types = 1);

namespace EugeneErg\src\ValueObjects;

final readonly class Point
{
    public function __construct(public float $x, public float $y)
    {
    }

    public function isEqual(self $other): bool
    {
        return $this->x === $other->x && $this->y === $other->y;
    }
}