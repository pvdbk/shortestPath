<?php

spl_autoload_register(function (string $className) {
    $partialPath = (preg_match('`^(.*)\\\\([^\\\\]+)$`', $className, $matches) === 1)
        ? str_replace('\\', '/', $matches[1]) . '/' . $matches[2]
        : $className;
    $file = __DIR__ . '/lib/' . $partialPath . '.php';
    require $file;
});
