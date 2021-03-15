<?php

namespace Dijkstra;

class Node
{
    private int $x;
    private int $y;
    private float $distance;
    private ?Node $router;

    public function __construct(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function getDistance(): float
    {
        return $this->distance;
    }

    public function getRouter(): ?Node
    {
        return $this->router;
    }

    public function setDistance(float $distance)
    {
        $this->distance = $distance;
    }

    public function setRouter(?Node $router)
    {
        $this->router = $router;
    }
}
