<?php

/**
 * MiddlewarePipeline
 * ------------------
 * Runs a list of middleware around a final handler.
 *
 * Each layer receives the request and a $next closure. Returning a Response
 * from any layer short-circuits the chain, so authentication or permission
 * checks can stop a request before the controller ever runs.
 */
class MiddlewarePipeline {
    private $middleware = [];
    private $destination;

    /**
     * @param array    $middleware  Instances or class names.
     * @param callable $destination The controller action to run last.
     */
    public function __construct(array $middleware, callable $destination) {
        $this->middleware = array_values($middleware);
        $this->destination = $destination;
    }

    public function handle(Request $request) {
        $chain = $this->buildChain($this->middleware, $this->destination, $request);

        return $chain($request);
    }

    /**
     * Fold the middleware list into nested closures, innermost first.
     */
    private function buildChain(array $middleware, callable $destination, Request $request) {
        $next = function (Request $request) use ($destination) {
            return $destination($request);
        };

        foreach (array_reverse($middleware) as $layer) {
            $current = $next;
            $next = function (Request $request) use ($layer, $current) {
                $instance = $this->resolve($layer);

                if ($instance === null) {
                    // Unknown middleware must not silently unlock a route.
                    return Response::error(500, 'Unknown middleware.');
                }

                return $instance->handle($request, $current);
            };
        }

        return $next;
    }

    /**
     * Accept an instance or a class name.
     */
    private function resolve($layer) {
        if ($layer instanceof Middleware) {
            return $layer;
        }

        if (is_string($layer) && class_exists($layer)) {
            return new $layer();
        }

        return null;
    }
}