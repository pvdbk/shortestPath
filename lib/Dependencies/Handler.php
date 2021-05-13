<?php

namespace Dependencies;

class Handler
{
    use \Singleton;
    private array $depTree;
    private array $map;
    private array $config;

    private function __construct()
    {
        $this->depTree = [];
        $this->config = json_decode(file_get_contents(__DIR__ . '/../../config.json'), true);
        $this->map = array_merge(
            json_decode(file_get_contents(__DIR__ . '/maps/main.json'), true),
            json_decode(file_get_contents(__DIR__ . '/maps/'. $this->config['env'] . 'Map.json'), true),
        );
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function get($client, $depName): string
    {
        if (!key_exists($depName, $this->map)) {
            $this->map[$depName] = $depName;
        }
        $depClass = $this->map[$depName];
        if (!$this->dependsDirectly($client, $depClass)) {
            $this->addDependency($client, $depClass);
        }
        return $depClass;
    }

    private function addDependency($client, $depClass)
    {
        $this->check($client);
        $this->check($depClass);
        if ($this->depends($depClass, $client)) {
            throw new \Exception();
        }
        $this->depTree[$client][] = $depClass;
    }

    private function check($client)
    {
        if (!key_exists($client, $this->depTree)) {
            $parent = get_parent_class($client);
            if ($parent !== false) {
                $this->check($parent);
                $this->depTree[$client] = [$parent];
            } else {
                $this->depTree[$client] = [];
            }
            $keys = array_keys($this->depTree);
            foreach ($keys as $key) {
                if (get_parent_class($key) === $client) {
                    $this->depTree[$key][] = $client;
                }
            }
        }
    }

    public function dependsDirectly($client, $depClass) : bool
    {
        return key_exists($client, $this->depTree) && in_array($depClass, $this->depTree);
    }

    public function depends($client, $depClass) : bool
    {
        $this->check($client);
        $this->check($depClass);
        $ret = ($client === $depClass) || in_array($depClass, $this->depTree[$client]);
        if (!$ret) {
            $deps = $this->depTree[$client];
            for ($i = 0; !$ret && $i < count($deps); $i++) {
                $ret = $this->depends($deps[$i], $depClass);
            }
        }
        return $ret;
    }
}
