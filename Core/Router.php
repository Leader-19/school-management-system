<?php
/**
 * Router
 * ------
 * Matches the incoming request against the route table defined
 * in /Routers/index.php and dispatches it to the right controller,
 * optionally wrapped in middleware.
 *
 * Route format:
 *     ['METHOD', '/path/{param}', 'ControllerName', 'actionMethod']
 *     ['METHOD', '/path/{param}', 'ControllerName', 'actionMethod', [middleware...]]
 *
 * A middleware entry is either a class name, a [class, [args]] pair, or an
 * already-built instance:
 *     AuthMiddleware::class
 *     [PermissionMiddleware::class, ['manage_students']]
 */
class Router {
    /** Middleware applied to every request, before the route's own. */
    private $globalMiddleware = [];

    private $routes = [];

    /**
     * Middleware that runs around every request.
     */
    public function setGlobalMiddleware(array $middleware) {
        $this->globalMiddleware = array_values($middleware);
    }

    /**
     * Load a route table array (as returned by Routers/index.php).
     */
    public function loadRoutes($routes) {
        foreach ($routes as $route) {
            // Route format is 4 elements with an optional 5th (middleware),
            // so pad to 5 before reading index 4.
            $route = array_pad(array_values($route), 5, null);
            $this->add($route[0], $route[1], $route[2], $route[3], $route[4] ?: []);
        }
    }

    /**
     * Register a single route.
     */
    public function add($method, $path, $controller, $action, array $middleware = []) {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'path'       => '/' . trim($path, '/'),
            'controller' => $controller,
            'action'     => $action,
            'middleware' => $middleware
        ];
    }

    /**
     * Match the request URL to a route and dispatch it.
     */
    public function dispatch($url) {
        $path   = '/' . trim($url, '/');
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        $methodMismatch = false;

        foreach ($this->routes as $route) {
            $params = $this->match($route['path'], $path);
            if ($params === false) {
                continue;
            }

            if ($route['method'] !== $method) {
                $methodMismatch = true;
                continue;
            }

            $this->runRoute($route, $params);
            return;
        }

        if ($methodMismatch) {
            $this->renderError(405, 'Method Not Allowed');
            return;
        }

        // Fallback: legacy convention /controller/action/param1/param2
        if ($this->dispatchFallback($path)) {
            return;
        }

        $this->renderError(404, 'Page Not Found');
    }

    /**
     * Run a matched route through the middleware pipeline.
     */
    private function runRoute(array $route, array $params) {
        $request = new Request();

        $destination = function (Request $request) use ($route, $params) {
            return $this->invoke($route['controller'], $route['action'], $params);
        };

        $middleware = array_merge($this->globalMiddleware, $this->instantiate($route['middleware']));
        $pipeline = new MiddlewarePipeline($middleware, $destination);

        $this->sendResponse($pipeline->handle($request));
    }

    /**
     * Turn route middleware entries into instances.
     */
    private function instantiate(array $middleware) {
        $instances = [];

        foreach ($middleware as $layer) {
            if (is_array($layer)) {
                $class = $layer[0];
                $args = array_slice($layer, 1);
                $instances[] = is_string($class) && class_exists($class) ? new $class(...$args) : null;
                continue;
            }

            $instances[] = is_string($layer) && class_exists($layer) ? new $layer() : $layer;
        }

        return array_values(array_filter($instances, function ($layer) {
            return $layer instanceof Middleware;
        }));
    }

    /**
     * Emit whatever the pipeline produced.
     */
    private function sendResponse($response) {
        if (!$response instanceof Response) {
            // The controller rendered a view directly; nothing to add.
            return;
        }

        switch ($response->type()) {
            case Response::TYPE_REDIRECT:
                $url = $response->target();
                if (!preg_match('#^https?://#i', (string) $url)) {
                    $url = BASE_URL . '/' . ltrim((string) $url, '/');
                }
                header('Location: ' . $url, true, $response->status());
                exit;

            case Response::TYPE_JSON:
                if (!headers_sent()) {
                    header('Content-Type: application/json');
                }
                http_response_code($response->status());
                echo json_encode($response->data());
                exit;

            case Response::TYPE_VIEW:
                http_response_code($response->status());
                $controller = new Controller();
                $controller->renderView($response->target(), (array) $response->data());
                exit;

            case Response::TYPE_RAW:
                foreach ($response->headers() as $name => $value) {
                    if (!headers_sent()) {
                        header($name . ': ' . $value);
                    }
                }
                http_response_code($response->status());
                echo $response->data();
                exit;

            case Response::TYPE_HANDLED:
            default:
                // The middleware already wrote everything (file download).
                exit;
        }
    }

    /**
     * Match a route path (supports {param} placeholders) against a request path.
     * Returns the captured params array, or false when there is no match.
     */
    private function match($routePath, $requestPath) {
        $pattern = preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', '([^/]+)', $routePath);
        if (preg_match('#^' . $pattern . '$#', $requestPath, $matches)) {
            array_shift($matches);
            return $matches;
        }
        return false;
    }

    /**
     * Instantiate the controller and call its action with the route params.
     */
    private function invoke($controllerName, $action, $params) {
        if (!class_exists($controllerName)) {
            $this->renderError(404, "Controller {$controllerName} not found.");
            return null;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $action)) {
            $this->renderError(404, "Action {$action} not found in controller {$controllerName}.");
            return null;
        }

        return call_user_func_array([$controller, $action], $params);
    }

    /**
     * Fallback to the old convention: /controller/action/param1/param2
     * Keeps older URLs working even when they are not in the route table.
     */
    private function dispatchFallback($path) {
        $segments = explode('/', trim($path, '/'));
        if (empty($segments[0])) {
            return false;
        }

        $controllerName = ucfirst($segments[0]) . 'Controller';
        if (!class_exists($controllerName)) {
            return false;
        }

        $action = isset($segments[1]) ? $segments[1] : 'index';
        $params = array_slice($segments, 2);

        $controller = new $controllerName();
        if (!method_exists($controller, $action)) {
            return false;
        }

        call_user_func_array([$controller, $action], $params);
        return true;
    }

    /**
     * Render an error view (e.g. 404) inside the main layout.
     */
    private function renderError($code, $message = '') {
        http_response_code($code);

        $viewFile = BASE_PATH . '/Views/errors/' . $code . '.php';
        $layoutFile = BASE_PATH . '/Views/layouts/main.php';

        if (file_exists($viewFile)) {
            ob_start();
            $httpCode = $code;
            require $viewFile;
            $viewContent = ob_get_clean();

            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                echo $viewContent;
            }
        } else {
            echo '<h1>Error ' . (int) $code . '</h1><p>' . htmlspecialchars($message) . '</p>';
        }
    }
}