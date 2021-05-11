<?php

namespace App;

class HeadersHandler
{
    use \Singleton;
    private array $headers;

    protected function __construct()
    {
        $this->headers = [];
    }

    private function add(string $header) {
        $this->headers[] = $header;
    }

    public function send() {
        foreach($this->headers as $header) {
            header($header);
        }
    }

    public function ok(): self
    {
        $this->add('HTTP/1.1 200 OK');
        return $this;
    }

    public function created(): self
    {
        $this->add('HTTP/1.1 201 Created');
        return $this;
    }

    public function noContent(): self
    {
        $this->add('HTTP/1.1 204 No Content');
        return $this;
    }

    public function badRequest(): self
    {
        $this->add('HTTP/1.1 400 Bad Request');
        return $this;
    }

    public function notFound(): self
    {
        $this->add('HTTP/1.1 404 Not Found');
        return $this;
    }

    public function internalError(): self
    {
        $this->add('HTTP/1.1 500 Internal Server Error');
        return $this;
    }

    public function jsonContent(): self
    {
        $this->add('Content-Type: application/json; charset=utf-8');
        return $this;
    }

    public function textContent(): self
    {
        $this->add('Content-Type: text/plain; charset=utf-8');
        return $this;
    }
}
