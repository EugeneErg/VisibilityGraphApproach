<?php

declare(strict_types = 1);

namespace EugeneErg\src\ValueObjects;

final readonly class Curve
{
    public function __construct(public Angle $from, public Angle $to)
    {
    }

    public function isEqual(Curve $other): bool
    {
        return $this->from->isEqual($other->from) && $this->to->isEqual($other->to);
    }
}