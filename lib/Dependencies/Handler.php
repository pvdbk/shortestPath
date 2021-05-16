<?php

namespace Dependencies;

class Handler
{
    use \Utils\Singleton;
    use \Utils\Arrays;
    private array $depTree;
    private array $map;
    private array $config;

    private function __construct()
    {
        $jsonsDir = __DIR__ . '/../../jsons/';
        $this->depTree = [];
        $this->config = json_decode(file_get_contents($jsonsDir . 'config.json'), true);
        $this->map = array_merge(
            json_decode(file_get_contents($jsonsDir . 'maps/main.json'), true),
            json_decode(file_get_contents($jsonsDir . 'maps/'. $this->config['env'] . 'Map.json'), true),
        );
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function get(string $client, string $depName): string
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

    private function addDependency(string $client, string $depClass)
    {
        $this->check($client);
        $this->check($depClass);
        if ($this->depends($depClass, $client)) {
            throw new \Exception();
        }
        $this->depTree[$client][] = $depClass;
    }

    private function check(string $client)
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

    public function dependsDirectly(string $client, string $depClass) : bool
    {
        return key_exists($client, $this->depTree) && in_array($depClass, $this->depTree[$client]);
    }

    public function depends(string $client, string $depClass) : bool
    {
        $this->check($client);
        $this->check($depClass);
        $isIndep = function (string $client) use(&$isIndep, $depClass): bool {
            return (
                $client !== $depClass
            ) && self::every(
                $this->depTree[$client],
                function(string $client) use(&$isIndep) {
                    return $isIndep($client);
                }
            );
        };
        return !$isIndep($client);
    }
}
