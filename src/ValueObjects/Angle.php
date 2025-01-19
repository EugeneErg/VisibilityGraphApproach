<?php

declare(strict_types = 1);

namespace EugeneErg\src\ValueObjects;

final readonly class Angle
{
    public function __construct(private float $value = 0)
    {
    }

    public static function pi(float $value = 1): self
    {
        return new Angle(M_PI * $value);
    }

    public static function degrees(float $value = 0): self
    {
        return new Angle(deg2rad($value));
    }

    public static function radian(float $value = 0): self
    {
        return new Angle($value);
    }

    public static function percent(float $value = 0): self
    {
        return new Angle($value * M_PI / 50);
    }

    public static function asin(float $num): self
    {
        if (asin($num) === NAN) {
            throw new \Exception((string) $num);
        }

        return new self(asin($num));
    }

    public static function max(Angle ...$angles): ?self
    {
        return array_reduce(
            $angles,
            fn (?Angle $result, Angle $next): Angle =>
            $result === null || $next->greaterThan($result) ? $next : $result,
        );
    }

    public function greaterThan(Angle $angle): bool
    {
        return $this->value > $angle->value;
    }

    public function lessThanOrEqual(Angle $angle): bool
    {
        return $this->value <= $angle->value;
    }

    public function greaterThanOrEqual(Angle $angle): bool
    {
        return $this->value >= $angle->value;
    }

    public function modulo(): Angle
    {
        $result = fmod($this->value, M_PI * 2);

        return new Angle($result + ($result < 0 ? (M_PI * 2) : 0));
    }

    public function absolute(): Angle
    {
        return new Angle(abs($this->value));
    }

    public function minus(Angle $angle): Angle
    {
        return new Angle($this->value - $angle->value);
    }

    public function plus(Angle $angle): Angle
    {
        return new Angle($this->value + $angle->value);
    }

    public function getPi(): float
    {
        return $this->value / M_PI;
    }

    public function getDegrees(): float
    {
        return rad2deg($this->value);
    }

    public function getRadian(): float
    {
        return $this->value;
    }

    public function getPercent(): float
    {
        return $this->value * 50 / M_PI;
    }

    public function isPositive(): bool
    {
        return $this->value > 0;
    }

    private function hasFlag(int $expected, int $value): bool
    {
        return ($expected & $value) === $expected;
    }

    public function isEqual(Angle $angle, float $delta = 0.00000001): bool
    {
        return $this->minus($angle)->absolute() < new Angle($delta);
    }

    public function divided(float $divider): Angle
    {
        return new Angle($this->value / $divider);
    }

    public function times(float $multiplier): Angle
    {
        return new Angle($this->value * $multiplier);
    }

    public function sin(): float
    {
        return sin($this->value);
    }

    public function cos(): float
    {
        return cos($this->value);
    }
}