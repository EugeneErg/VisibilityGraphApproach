<?php

declare(strict_types = 1);

namespace EugeneErg\src;

use EugeneErg\src\ValueObjects\Angle;
use EugeneErg\src\ValueObjects\Curve;
use EugeneErg\src\ValueObjects\IntersectType;
use EugeneErg\src\ValueObjects\Part;
use EugeneErg\src\ValueObjects\Point;
use EugeneErg\src\ValueObjects\Polygon;
use EugeneErg\src\ValueObjects\State;

final readonly class PolygonService
{
    public function getVisiblePolygonFromPoint(Polygon $polygon, Point $visor): Polygon
    {
        $state = null;
        $previousPart = null;

        foreach ($polygon as $point) {
            $currentPart = new Part(
                point: $point,
                angle: $this->getAngle($visor, $point),
                distance: $this->getDistance($visor, $point),
            );

            if ($previousPart === null) {
                //$currentRadar->setConnect(false);
                $previousPart = $currentPart;
                $state = new State(
                    visible: true,
                    firstPart: $previousPart,
                    lastPart: $previousPart,
                );

                continue;
            }

            $shiftAngle = $this->getRelativeAngle($previousPart->angle, $currentPart->angle);
            $state = $shiftAngle->isPositive()
                ? $this->addPart($currentPart, $previousPart, $state)
                : $this->removePart($currentPart, $previousPart, $state);
        }

        $result = [];

        /** @var Part|null $root */
        for (
            $root = $state?->firstPart;
            $root !== null;
            $root = $root->getNext()
        ) {
            $result[] = $root->point;
        }

        return new Polygon(...$result);
    }

    private function getAngle(Point $visorCoordinate, Point $vertexCoordinate): ?Angle
    {
        return $visorCoordinate->isEqual($vertexCoordinate)
            ? new Angle()
            : (new Angle(atan2(
                $visorCoordinate->y - $vertexCoordinate->y,
                $visorCoordinate->x - $vertexCoordinate->x,
            )))->modulo();
    }

    private function getDistance(Point $visorCoordinate, Point $vertexCoordinate): float
    {
        return ($visorCoordinate->y - $vertexCoordinate->y) ** 2 + ($visorCoordinate->x - $vertexCoordinate->x) ** 2;
    }

    private function getRelativeAngle(Angle $previousAngle, Angle $currentAngle): Angle
    {
        $result = $currentAngle->minus($previousAngle)->modulo();

        return $result->greaterThan(Angle::pi()) ? Angle::pi()->minus($result) : $result;
    }

    private function addPart(Part $currentPart, Part $previousPart, State $state): State
    {
        $intersectType = $this->getCurveIntersectType(
            new Curve($previousPart->angle, $currentPart->angle),
            new Curve($state->firstPart->angle, $state->lastPart->angle),
        );
    }

    private function getCurveIntersectType(Curve $newLineCurve, Curve $resultCurve): IntersectType
    {
        if ($newLineCurve->isEqual($resultCurve)) {
            return IntersectType::Equal;
        }

        if (
            $newLineCurve->from->isEqual($resultCurve->to)
            && $newLineCurve->to->isEqual($resultCurve->from)
        ) {
            return IntersectType::Circle;
        }

        $backCurveA = $toCurveA->minus($fromCurveA)->greaterThan(Angle::pi());//just line
        $backCurveB = $toCurveB->minus($fromCurveB)->greaterThan(Angle::pi());//can be real curve

        if ($backCurveA && $backCurveB) {



        }
    }

    private function removePart(Part $currentPart, Part $previousPart, mixed $state): State
    {
    }
}