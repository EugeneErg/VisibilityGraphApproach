<?php

declare(strict_types = 1);

namespace EugeneErg\VisibilityGraphApproach\ValueObjects;

use EugeneErg\VisibilityGraphApproach\ShortestPathService;
use EugeneErg\VisibilityGraphApproach\VisibleVertexService;
use Exception;

final readonly class VisGraph
{
    public function __construct(private Graph $graph, private Graph $visGraph)
    {
    }

    public static function build(array $input, int $workers = 1): self
    {
        $graph = new Graph($input);
        $visGraph = new Graph([]);
        $points = $graph->getPoints();
        $batchSize = 10;

        if ($workers === 1) {
            foreach (array_chunk($points, $batchSize) as $batch) {
                foreach (self::_visGraph($graph, $batch) as $edge) {
                    $visGraph->addEdge($edge);
                }
            }
        } else {
            $batches = [];

            foreach (array_chunk($points, $batchSize) as $batch) {
                $batches[] = [$graph, $batch];
            }

            $results = [];

            foreach ($batches as $batch) {
                $results[] = self::visGraphWrapper($batch);
            }

            foreach ($results as $result) {
                foreach ($result as $edge) {
                    $visGraph->addEdge($edge);
                }
            }
        }

        return new self($graph, $visGraph);
    }

    public function findVisible(Point $point): array
    {
        return VisibleVertexService::visibleVertices($point, $this->graph);
    }

    /**
     * @param Point[] $points
     * @return void
     */
    public function update(array $points, ?Point $origin = null, ?Point $destination = null)
    {
        foreach ($points as $p) {
            foreach (VisibleVertexService::visibleVertices($p, $this->graph, $origin, $destination) as $v) {
                $this->visGraph->addEdge(new Edge($p, $v));
            }
        }
    }

    /**
     * @return Point[]
     *
     * @throws Exception
     */
    public function shortestPath(Point $origin, Point $destination): array
    {
        $originExists = $this->visGraph->contains($origin);
        $destExists = $this->visGraph->contains($destination);

        if ($originExists && $destExists) {
            return ShortestPathService::shortestPath($this->visGraph, $origin, $destination);
        }

        $orgn = $originExists ? null : $origin;
        $dest = $destExists ? null : $destination;

        $addToVisg = new Graph();

        if (!$originExists) {
            foreach (VisibleVertexService::visibleVertices($origin, $this->graph, $dest) as $v) {
                $addToVisg->addEdge(new Edge($origin, $v));
            }
        }

        if (!$destExists) {
            foreach (VisibleVertexService::visibleVertices($destination, $this->graph, $orgn) as $v) {
                $addToVisg->addEdge(new Edge($destination, $v));
            }
        }

        return ShortestPathService::shortestPath($this->visGraph, $origin, $destination, $addToVisg);
    }

    public function pointInPolygon(Point $point)
    {
        // Return polygon_id if point is in a polygon, -1 otherwise
        return VisibleVertexService::pointInPolygon($point, $this->graph);
    }

    public function closestPoint($point, $polygonId, $length = 0.001)
    {
        // Return closest Point outside polygon from point
        // Note: Assumes point is inside the polygon, no check is performed
        return VisibleVertexService::closestPoint($point, $this->graph, $polygonId, $length);
    }


    private static function visGraphWrapper($args): array
    {
        try {
            return $this->_visGraph($args[0], $args[1]);
        } catch (Exception $e) {
            // Handle exception or interrupt signal
            return [];
        }
    }

    /**
     * @param Graph $graph
     * @param Point[] $points
     *
     * @return Edge[]
     */
    private static function _visGraph(Graph $graph, array $points): array
    {
        $visibleEdges = [];

        foreach ($points as $p1) {
            foreach (VisibleVertexService::visibleVertices($p1, $graph, scanFull: false) as $p2) {
                $visibleEdges[] = new Edge($p1, $p2);
            }
        }

        return $visibleEdges;
    }
}