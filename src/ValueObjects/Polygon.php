<?php

declare(strict_types = 1);

namespace EugeneErg\src\ValueObjects;

final readonly class Polygon
{
    /** @var Point[] */
    public array $points;

    public function __construct(Point ...$points)
    {
        $this->points = $points;
    }
}