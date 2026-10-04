<?php

/**
 * AuthMiddleware
 * --------------
 * Requires a signed-in user before the route runs.
 *
 * Replaces the `if (!$this->isLoggedIn()) { redirect }` check that every
 * controller constructor used to repeat.
 */
class AuthMiddleware extends Middleware {
    public function handle(Request $request, callable $next) {
        if (Auth::check() && Auth::userId() !== null) {
            return $next($request);
        }

        if ($request->wantsJson()) {
            return Response::json(['error' => 'Authentication required.'], 401);
        }

        Session::setFlash('error', 'Please sign in to continue.');
        return Response::redirect('login');
    }
}
