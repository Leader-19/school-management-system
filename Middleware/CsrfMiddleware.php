<?php

/**
 * CsrfMiddleware
 * --------------
 * Verifies the CSRF token on every state-changing request.
 *
 * The project has POST-only actions (delete, create, grade), so this closes
 * cross-site request forgery on all of them with one check. GET requests
 * pass straight through.
 */
class CsrfMiddleware extends Middleware {
    const FIELD = '_token';

    public function handle(Request $request, callable $next) {
        if (!$request->isPost()) {
            return $next($request);
        }

        $token = (string) ($request->post(self::FIELD) ?? $request->header('X-CSRF-Token', ''));

        if ($token !== '' && hash_equals(self::token(), $token)) {
            return $next($request);
        }

        if ($request->wantsJson()) {
            return Response::json(['error' => 'Your session expired. Please reload the page and try again.'], 419);
        }

        Session::setFlash('error', 'Your session expired. Please reload the page and try again.');

        // Send the user back where they came from rather than to the login
        // page: a CSRF failure is a stale session, not a missing one, and
        // bouncing to /login would throw away their place in the app.
        return Response::redirect($this->safeReturnUrl($request));
    }

    /**
     * Same-origin path to return to after a rejected request.
     */
    private function safeReturnUrl(Request $request) {
        $referer = $request->header('Referer');

        if (is_string($referer) && $referer !== '') {
            $host = $request->header('Host');
            $path = parse_url($referer, PHP_URL_PATH);
            $query = parse_url($referer, PHP_URL_QUERY);

            // Only follow a referer on this host, and never an absolute URL.
            if ($path && parse_url($referer, PHP_URL_HOST) === $host) {
                $base = rtrim(defined('BASE_URL') ? BASE_URL : '', '/');
                $path = ($base !== '' && strpos($path, $base) === 0) ? substr($path, strlen($base)) : $path;
                $path = trim((string) $path, '/');

                // Keep the query string so filters and paging survive the
                // round trip.
                if ($query) {
                    $path .= '?' . $query;
                }

                if ($path !== '' && stripos($path, 'login') === false) {
                    return $path;
                }
            }
        }

        return 'dashboard';
    }

    /**
     * Per-session token, generated once and reused.
     */
    public static function token() {
        if (!Session::has('_csrf_token')) {
            Session::set('_csrf_token', bin2hex(random_bytes(32)));
        }

        return (string) Session::get('_csrf_token');
    }

    /**
     * Regenerate the token, e.g. after a successful sign-in.
     */
    public static function regenerate() {
        Session::set('_csrf_token', bin2hex(random_bytes(32)));
        return Session::get('_csrf_token');
    }
}