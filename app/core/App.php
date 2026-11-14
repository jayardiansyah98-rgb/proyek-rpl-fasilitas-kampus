<?php
/**
 * Router Engine & URL Parser
 * Mengurai URL menjadi Controller, Method, dan Parameter
 */

class App {
    protected $controller = DEFAULT_CONTROLLER;
    protected $method = DEFAULT_METHOD;
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // 1. Cek Controller
        if (!empty($url[0])) {
            $formattedController = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $url[0]))) . 'Controller';
            $controllerFile = __DIR__ . '/../controllers/' . $formattedController . '.php';

            if (file_exists($controllerFile)) {
                $this->controller = $formattedController;
                unset($url[0]);
            } else {
                $this->controller = 'ErrorController';
            }
        }

        // Muat file controller
        $controllerPath = __DIR__ . '/../controllers/' . $this->controller . '.php';
        if (file_exists($controllerPath)) {
            require_once $controllerPath;
        } else {
            require_once __DIR__ . '/../controllers/ErrorController.php';
            $this->controller = 'ErrorController';
        }

        $this->controller = new $this->controller;

        // 2. Cek Method
        if (isset($url[1])) {
            $methodCandidate = lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $url[1]))));
            
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            } elseif (method_exists($this->controller, $methodCandidate)) {
                $this->method = $methodCandidate;
                unset($url[1]);
            } else {
                // Jika method spesifik tidak ditemukan di controller yang diminta
                if (!($this->controller instanceof ErrorController)) {
                    require_once __DIR__ . '/../controllers/ErrorController.php';
                    $this->controller = new ErrorController();
                    $this->method = 'index';
                }
            }
        }

        // 3. Ambil Parameter
        $this->params = $url ? array_values($url) : [];

        // 4. Jalankan Controller & Method
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    /**
     * Memfilter dan memecah URL menjadi array segmen
     */
    public function parseUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }
        return [];
    }
}
