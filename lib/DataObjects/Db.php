<?php

namespace DataObjects;

class Db extends \PDO {
    use \Utils\Singleton;

    private function __construct() {
        parent::__construct('mysql:host=localhost; dbname=shortest_path; charset=utf8', 'root', '');
    }
}
