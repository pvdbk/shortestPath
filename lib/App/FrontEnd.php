<?php

namespace App;

class FrontEnd extends Abst
{
    use \Singleton;
    const VIEWS_DIR = self::ROOTDIR . 'views/';
    const PUBLIC_DIR = self::ROOTDIR . 'public/';
    private string $view;

    protected function __construct()
    {
        parent::__construct();
        $config = $this->getConfig();
        $routes = $config['routes'];
        $notFound = true;
        for ($i = 0; $i < count($routes) && $notFound; $i++) {
            extract($routes[$i]);
            $notFound = !preg_match('`^/' . $url . '$`', $_SERVER['REQUEST_URI'], $matches);
        }
        if ($notFound) {
            $this->notFound();
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
}
