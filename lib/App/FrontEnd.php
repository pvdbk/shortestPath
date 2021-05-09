<?php

namespace App;

class FrontEnd
{
    use \Singleton;
    const VIEWS_DIR = self::ROOTDIR . 'views/';
    const PUBLIC_DIR = self::ROOTDIR . 'public/';
    const ROOTDIR = __DIR__ . '/../../';
    private array $headers;
    private array $config;
    private string $view;

    private function __construct()
    {
        $this->headers = [];
        $this->config = json_decode(file_get_contents(self::ROOTDIR . 'config.json'), true);
        $routes = $this->config['routes'];
        $notFound = true;
        for ($i = 0; $i < count($routes) && $notFound; $i++) {
            extract($routes[$i]);
            $notFound = !preg_match('`^/' . $url . '$`', $_SERVER['REQUEST_URI'], $matches);
        }
        if ($notFound) {
            $this->addHeader('HTTP/1.1 404 Not Found');
            $this->view = $this->config['defaultView'];
        } else {
            $this->view = $view;
        }
    }

    public function addHeader(string $header) {
        $this->headers[] = $header;
    }

    public function sendHeaders() {
        foreach($this->headers as $header) {
            header($header);
        }
    }

    public function includeCSS()
    {
        $partialPath = 'css/' . $this->view . '.css';
        if(file_exists(self::PUBLIC_DIR . $partialPath)) {
            echo '<link rel = "stylesheet" type="text/css" href="/' . $partialPath . '">';
        }
    }

    public function includeJS()
    {
        $partialPath = 'js/' . $this->view . '.js';
        if(file_exists(self::PUBLIC_DIR . $partialPath)) {
            echo '<script src="/' . $partialPath . '"></script>';
        }
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getView(): string
    {
        return $this->view;
    }
}
