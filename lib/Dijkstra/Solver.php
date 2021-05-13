<?php

namespace Dijkstra;

class Solver
{
    private Net $net;
    private array $taken;
    private array $remained;

    public function __construct(Net $net)
    {
        $this->net = $net;
    }

    private function takeMoreLines(Node $node)
    {
        $i = 0;
        while ($i < count($this->remained)) {
            $line = $this->remained[$i];
            if ($line->getNode1() === $node || $line->getNode2() === $node) {
                $line->SetAccLength($line->getLength() + $node->getDistance());
                array_splice($this->remained, $i, 1);
                $j = 0;
                while (
                    $j < count($this->taken)
                    && $this->taken[$j]->getAccLength() < $line->getAccLength()
                ) {
                    $j++;
                }
                array_splice($this->taken, $j, 0, [$line]);
            } else {
                $i++;
            }
        }
    }

    private function getNextNode(): ?Node
    {
    	$ret = null;
    	while ($this->taken && !$ret) {
    		$line = array_shift($this->taken);
    		if ($line->getNode1()->getRouter() === null) {
                $ret = $line->getNode1();
                $ret->setRouter($line->getNode2());
            } elseif ($line->getNode2()->getRouter() === null) {
                $ret = $line->getNode2();
                $ret->setRouter($line->getNode1());
            }
    	}
		if ($ret) {
            $ret->setDistance($line->getAccLength());
        }
    	return $ret;
    }


    public function solve(Node $start, Node $end)
    {
        $nodes = $this->net->getNodes();
        if (!in_array($start, $nodes) || !in_array($end, $nodes)) {
            throw new \Exception('Not in the net');
        }
        $this->remained = array_slice($this->net->getLines(), 0);
        $this->net->nullifyRouters();
        $start->setDistance(0);
        $start->setRouter($start);
        $node = $start;
        $this->taken = [];
        do {
            $this->takeMoreLines($node);
            $node = $this->getNextNode();
        } while ($node !== $end && $node);
        $this->remained = [];
        $this->taken = [];
    }
}
