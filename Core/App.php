<?php
class App {
    public function run() {
        $router = new Router();

        // Applied to every request, before any route middleware.
        $router->setGlobalMiddleware([
            SecurityHeadersMiddleware::class
        ]);

        // Route definitions are kept separately in /Routers
        $routes = require BASE_PATH . '/Routers/index.php';
        $router->loadRoutes($routes);

        $url = $this->getRequestUrl();

        $router->dispatch($url);
    }

    /**
     * Resolve the route path for the current request.
     *
     * 1. Apache with .htaccess rewrites to index.php?url=...
     * 2. Servers without mod_rewrite (e.g. PHP built-in server used by
     *    "php -S localhost:8000"): derive the path from REQUEST_URI,
     *    stripping the BASE_URL prefix and the query string.
     */
    private function getRequestUrl() {
        if (isset($_GET['url'])) {
            $url = $_GET['url'];
        } else {
            $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
            $base = rtrim(BASE_URL, '/');

            if ($base !== '' && strpos($uri, $base) === 0) {
                $uri = substr($uri, strlen($base));
            }

            $url = trim($uri, '/');
        }

        $url = rtrim($url, '/');
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}