<?php

declare(strict_types = 1);

namespace EugeneErg\src\ValueObjects;

final readonly class State
{
    public function __construct(
        public bool $visible,
        public Part $firstPart,
        public Part $lastPart,
    ) {
    }
}