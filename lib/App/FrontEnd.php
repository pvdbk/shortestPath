<?php

namespace App;

class FrontEnd
{
    use \Utils\Singleton;
    use \Dependencies\Injection;
    const VIEWS_DIR = __DIR__ . '/../../views/';
    const PUBLIC_DIR = __DIR__ . '/../../public/';
    private string $view;
    private object $headersHdl;

    protected function __construct()
    {
        $this->headersHdl = self::getDepInstance('headersHandler');
        $config = $this->getConfig();
        $routes = $config['routes'];
        $continue = true;
        for ($i = 0; $continue && $i < count($routes); $i++) {
            extract($routes[$i]);
            $continue = !preg_match('`^/' . $url . '$`', $_SERVER['REQUEST_URI'], $matches);
        }
        if ($continue) {
            $this->headersHdl->notFound();
            $this->view = $config['defaultView'];
        } else {
            $this->view = $view;
        }
    }

    public function includeCSS()
    {
        $partialPath = 'css/' . $this->view . '.css';
        if (file_exists(self::PUBLIC_DIR . $partialPath)) {
            echo '<link rel = "stylesheet" type="text/css" href="/' . $partialPath . '">';
        }
    }

    public function includeJS()
    {
        $partialPath = 'js/' . $this->view . '.js';
        if (file_exists(self::PUBLIC_DIR . $partialPath)) {
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
