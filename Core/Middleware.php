<?php

/**
 * Middleware
 * ----------
 * Base class for request filters.
 *
 * A middleware runs before the controller action. It either returns a
 * response to short-circuit the request (redirect, 403, ...) or calls
 * $next($request) to continue the chain.
 *
 * Example:
 *     class MaintenanceMiddleware extends Middleware {
 *         public function handle(Request $request, Closure $next) {
 *             if (now() < self::until()) {
 *                 return Response::redirect('/');
 *             }
 *             return $next($request);
 *         }
 *     }
 */
abstract class Middleware {
    /**
     * @param Request   $request
     * @param callable  $next    Passes the request to the next layer.
     * @return mixed    A Response short-circuits; anything else is ignored
     *                  unless it is what $next() returned.
     */
    abstract public function handle(Request $request, callable $next);

    /**
     * Convenience guard: true once the middleware has already produced a
     * response and the chain must stop.
     */
    protected function isResponse($value) {
        return $value instanceof Response;
    }
}