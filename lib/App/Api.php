<?php

namespace App;

class Api
{
    use \Singleton;
    use \Dependencies\Injection;
    private array $toCall;
    private array $args;

    protected function __construct()
    {
        $this->args = [];
        preg_match(
            '`^/api/([^?/]*)(/[^?]*)?(\\?(.*))?$`',
            $_SERVER['REQUEST_URI'],
            $matches
        );
        $domain = $matches[1];
        $conf = $this->getConfig()['api'];
        $headersHdl = $this->getDepInstance('headersHandler');
        $this->toCall = array($headersHdl, 'send');
        if(!array_key_exists($domain, $conf)) {
            $headersHdl->notFound();
        } else {
            extract($conf[$domain]);
            $path = isset($matches[2]) ? $matches[2] : '';
            $notFound = true;
            for ($i = 0; $i < count($routes) && $notFound; $i++) {
                extract($routes[$i]);
                $notFound = $_SERVER['REQUEST_METHOD'] !== $method || !preg_match('`^' . $url . '$`', $path, $matches);
            }
            if ($notFound) {
                $headersHdl->notFound();
            } else {
                $this->toCall = array($this->getDepInstance($controller), $func);
                for ($i = 0; $i < count($args); $i++) {
                    $this->args[$args[$i]] = $matches[$i+1];
                }
            }
        }
    }

    public function run()
    {
        ($this->toCall)(...$this->args);
    }
}
