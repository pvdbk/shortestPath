<?php

namespace Dijkstra;

class Line
{
    private Node $node1;
    private Node $node2;
    private float $length;
    private float $accLength;

    public function __construct(Node $node1, Node $node2, float $length)
    {
        $this->node1 = $node1;
        $this->node2 = $node2;
        $this->length = $length;
    }

    public function getNode1(): Node
    {
        return $this->node1;
    }

    public function getNode2(): Node
    {
        return $this->node2;
    }

    public function getLength(): float
    {
        return $this->length;
    }

    public function getAccLength(): float
    {
        return $this->accLength;
    }

    public function setAccLength(float $accLength)
    {
        $this->accLength = $accLength;
    }
}
