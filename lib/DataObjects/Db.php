<?php

namespace DataObjects;

class Db extends \PDO {
    use \Singleton;

    private function __construct() {
        parent::__construct('mysql:host=localhost; dbname=shortest_path; charset=utf8', 'root', '');
    }
}
