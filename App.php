<?php

class App
{
    private array $config;
    private string $rootDir;
    private string $libDir;
    private string $viewsDir;
    private string $publicDir;
    private string $view;
    private array $args;
    private static ?self $instance = null;

    public static function get(): self
    {
        if (self::$instance === null) {
            self::$instance = new App();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->rootDir = __DIR__ . '/';
        $this->config = json_decode(file_get_contents($this->rootDir . 'config.json'), true);
        $this->libDir = $this->rootDir . 'lib/';
        $this->viewsDir = $this->rootDir . 'views/';
        $this->publicDir = $this->rootDir . 'public/';
        $this->args = [];
        $this->route();
        spl_autoload_register(array($this, 'loadClass'));
    }

    private function route()
    {
        try {
            $routes = $this->config['routes'];
            $notFound = true;
            for ($i = 0; $i < count($routes) && $notFound; $i++) {
                $regex = '`^' . $routes[$i]['url'] . '$`';
                $notFound = !preg_match($regex, $_SERVER['REQUEST_URI'], $matches);
            }
            if ($notFound) {
                $this->view = $this->config['defaultView'];
            } else {
                $this->view = $routes[--$i]['view'];
                $params = $routes[$i]['params'];
                for ($i = 0; $i < count($params); $i++) {
                    $this->args[$params[$i]] = $matches[$i+1];
                }
            }
        } catch (Exception) {
            throw new Exception ("Bad config.json");
        }
    }

    public function loadClass($className)
    {
        $partialPath = (preg_match('`^(.*)\\\\([^\\\\]+)$`', $className, $matches))
            ? str_replace('\\', '/', $matches[1]) . '/' . $matches[2]
            : $className;
        require $this->libDir . $partialPath . '.php';
    }

    public function includeCSS()
    {
        $partialPath = 'css/' . $this->view . '.css';
        if(file_exists($this->publicDir . $partialPath)) {
            echo '<link rel = "stylesheet" type="text/css" href="/' . $partialPath . '">';
        }
    }

    public function includeJS()
    {
        $partialPath = 'js/' . $this->view . '.js';
        if(file_exists($this->publicDir . $partialPath)) {
            echo '<script src="/' . $partialPath . '"></script>';
        }
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    public function getLibDir(): string
    {
        return $this->libDir;
    }

    public function getViewsDir(): string
    {
        return $this->viewsDir;
    }

    public function getView(): string
    {
        return $this->view;
    }

    public function getArgs(): array
    {
        return $this->args;
    }
}
