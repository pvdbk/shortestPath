<?php

namespace App;

class Api
{
    use \Utils\Singleton;
    use \Utils\Arrays;
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
        $headersHdl = self::getDepInstance('headersHandler');
        $this->toCall = [$headersHdl, 'send'];
        if (!array_key_exists($domain, $conf)) {
            $headersHdl->notFound();
        } else {
            extract($conf[$domain]);
            $path = isset($matches[2]) ? $matches[2] : '';
            $continue = true;
            for ($i = 0; $continue && $i < count($routes); $i++) {
                extract($routes[$i]);
                $continue = (
                    $_SERVER['REQUEST_METHOD'] !== $method
                ) && (
                    preg_match('`^' . $url . '$`', $path, $matches) !== 1
                );
            }
            if ($continue) {
                $headersHdl->notFound();
            } else {
                $this->toCall = [self::getDepInstance($controller), $func];
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
