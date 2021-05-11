<?php

namespace Dependencies;

class Provider
{
    private string $client;
    private Handler $handler;

    public function __construct(string $client)
    {
        $this->client = $client;
        $this->handler = Handler::getInstance();
    }

    public function get($depName): string
    {
        return $this->handler->get($this->client, $depName);
    }
}
