<?php

use Dijkstra\{Net, Node, Line, Solver};

(function () {
    $net = new Net();
    $a = new Node(1, 1);
    $b = new Node(2, 2);
    $c = new Node(3, 3);
    $d = new Node(4, 4);
    $e = new Node(5, 5);
    $f = new Node(6, 6);
    $g = new Node(7, 7);
    $net->addNodes($a, $b, $c, $d, $e, $f, $g);
    $net->addLines(
        new Line($a, $b, 7),
        new Line($a, $c, 9),
        new Line($a, $f, 14),
        new Line($b, $c, 10),
        new Line($b, $d, 15),
        new Line($c, $d, 11),
        new Line($c, $f, 2),
        new Line($d, $e, 6),
        new Line($e, $f, 9)
    );
    $solver = new Solver($net);
    $solver->solve($a, $e);
    $res = '';
    for ($h = $e; $h !== $a; $h = $h->getRouter()) $res .= $h->getX() . ' ';
    $solver->solve($a, $g);
    $res .= $g->getRouter() == null ? 'OK' : 'Aïe';
    echo '<p>Result: ' . $res . '<br />Expected: 5 6 3 OK</p>';
})();
