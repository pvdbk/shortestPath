<?php

trait Singleton {
    private static $instance = null;

    final public static function getInstance(): static {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    final public function __clone() { }
}
