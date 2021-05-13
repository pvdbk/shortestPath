<?php

namespace Dijkstra;

class Net
{
    private array $nodes;
    private array $lines;

    public function __construct()
    {
        $this->nodes = [];
        $this->lines = [];
    }

    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function getLines(): array
    {
        return $this->lines;
    }

    public function addNodes()
    {
        $nodes = func_get_args();
        foreach ($nodes as $node) {
            if (!($node instanceof Node)) {
                throw new \Exception('Only nodes');
            }
            if (in_array($node, $this->nodes)) {
                throw new \Exception('Duplicate node');
            }
        }
        $this->nodes = array_merge($this->nodes, $nodes);
    }

    public function addLines()
    {
        $lines = func_get_args();
        foreach ($lines as $line) {
            if (!($line instanceof Line)) {
                throw new \Exception('Only lines');
            } elseif (in_array($line, $this->lines)) {
                throw new \Exception('Duplicate line');
            } elseif (!in_array($line->getNode1(), $this->nodes) || !in_array($line->getNode2(), $this->nodes)) {
                throw new \Exception('Unknown node');
            }
        }
        $this->lines = array_merge($this->lines, $lines);
    }

    public function nullifyRouters()
    {
        $iMax = count($this->nodes);
        for ($i = 0; $i < $iMax; $i++) $this->nodes[$i]->setRouter(null);
    }
}
