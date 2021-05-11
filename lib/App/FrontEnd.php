<?php

namespace App;

class FrontEnd
{
    use \Singleton;
    use \Dependencies\Injection;
    const VIEWS_DIR = __DIR__ . '/../../views/';
    const PUBLIC_DIR = __DIR__ . '/../../public/';
    private string $view;
    private HeadersHandler $headersHdl;

    protected function __construct()
    {
        $this->headersHdl = $this->getDepInstance('headersHandler');
        $config = $this->getConfig();
        $routes = $config['routes'];
        $notFound = true;
        for ($i = 0; $i < count($routes) && $notFound; $i++) {
            extract($routes[$i]);
            $notFound = !preg_match('`^/' . $url . '$`', $_SERVER['REQUEST_URI'], $matches);
        }
        if ($notFound) {
            $this->headersHdl->notFound();
            $this->view = $config['defaultView'];
        } else {
            $this->view = $view;
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

    public function getView(): string
    {
        return $this->view;
    }

    public function sendHeaders() {
        $this->headersHdl->send();
    }
}
