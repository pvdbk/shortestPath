<?php

namespace App;

abstract class Abst
{
    const ROOTDIR = __DIR__ . '/../../';
    private array $headers;
    private array $config;

    protected function __construct()
    {
        $this->headers = [];
        $this->config = json_decode(file_get_contents(__DIR__ . '/../../config.json'), true);
    }

    public function addHeader(string $header) {
        $this->headers[] = $header;
    }

    public function sendHeaders() {
        foreach($this->headers as $header) {
            header($header);
        }
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function notFound() {
        $this->addHeader('HTTP/1.1 404 Not Found');
    }
}
