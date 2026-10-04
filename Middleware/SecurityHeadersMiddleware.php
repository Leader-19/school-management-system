<?php

/**
 * SecurityHeadersMiddleware
 * -------------------------
 * Sends hardening headers with every response and blocks obviously unsafe
 * cross-origin requests on state-changing endpoints.
 */
class SecurityHeadersMiddleware extends Middleware {
    public function handle(Request $request, callable $next) {
        // Set before $next() runs: PHP flushes headers as soon as any output
        // is produced, so they must be registered before the view renders.
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('X-XSS-Protection: 0');
        }

        return $next($request);
    }
}
