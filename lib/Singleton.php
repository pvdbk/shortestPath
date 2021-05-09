<?php

trait Singleton {
    private static ?self $instance = null;

    final public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    final public function __clone() { }
}
