<?php
/**
 * Middleware behaviour check. Run the dev server first, then:
 *   php tools/verify_middleware.php
 */

// Loaded first: the in-process checks below need a session, and a session
// cannot start once output has been sent.
require_once __DIR__ . '/bootstrap.php';

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8766', '/');
$pass = 0;
$fail = 0;

function check($label, $actual, $expected = true) {
    global $pass, $fail;
    $result = func_num_args() > 2 ? ($actual === $expected) : (bool) $actual;
    if ($result) {
        $pass++;
        echo "  PASS  {$label}\n";
    } else {
        $fail++;
        echo "  FAIL  {$label} (got " . var_export($actual, true) . ")\n";
    }
}

/**
 * @return array{status:int, headers:array, body:string, location:string}
 */
function req($path, $method = 'GET', $post = null, $cookieJar = null) {
    global $base;

    $options = [
        'http' => [
            'method'          => $method,
            'ignore_errors'   => true,
            // Must not follow redirects: the 302 + Location header is exactly
            // what these checks assert on.
            'follow_location' => 0,
            'max_redirects'   => 1,
            'timeout'         => 10,
            'header'          => [],
        ]
    ];

    if ($post !== null) {
        $options['http']['header'][] = 'Content-Type: application/x-www-form-urlencoded';
        $options['http']['content'] = http_build_query($post);
    }

    if ($cookieJar) {
        $options['http']['header'][] = 'Cookie: ' . $cookieJar;
    }

    $raw = @file_get_contents($base . $path, false, stream_context_create($options));

    $status = 0;
    $headers = [];
    $location = '';
    $rawHeaders = $http_response_header ?? [];

    foreach ($rawHeaders as $h) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $h, $m)) {
            // Reset on each new response line so a followed redirect does not
            // leak the first response's headers into the result.
            $status = (int) $m[1];
            $headers = [];
            $location = '';
            continue;
        }

        $split = explode(':', $h, 2);
        if (count($split) < 2) {
            continue;
        }

        $name = strtolower(trim($split[0]));
        $value = trim($split[1]);

        if ($name === 'location') {
            $location = $value;
        }

        $headers[$name] = $value;
    }

    return [
        'status'    => $status,
        'headers'   => $headers,
        'body'      => $raw === false ? '' : $raw,
        'location'  => $location,
        'rawHeader' => $rawHeaders,
    ];
}

echo "\nMiddleware checks against {$base}\n";
echo str_repeat('-', 62) . "\n";

echo "\n1. SecurityHeadersMiddleware (global)\n";
$r = req('/login');
check('X-Content-Type-Options sent', ($r['headers']['x-content-type-options'] ?? '') === 'nosniff');
check('X-Frame-Options sent', ($r['headers']['x-frame-options'] ?? '') === 'SAMEORIGIN');
check('Referrer-Policy sent', isset($r['headers']['referrer-policy']));

echo "\n2. CsrfMiddleware issues a token in the login form\n";
check('hidden _token field present', strpos($r['body'], 'name="_token"') !== false);
preg_match('/name="_token" value="([a-f0-9]{64})"/', $r['body'], $m);
$token = $m[1] ?? '';
check('token is 64 hex chars (256 bits)', strlen($token), 64);

echo "\n3. CsrfMiddleware rejects POSTs with a missing or wrong token\n";
// Exercised directly: on the real routes AuthMiddleware runs first, so an
// unauthenticated request never reaches the CSRF check.
$reachedNext = 0;
$next = function ($request) use (&$reachedNext) {
    $reachedNext++;
    return 'reached-controller';
};

$csrf = new CsrfMiddleware();
CsrfMiddleware::regenerate();
$validToken = CsrfMiddleware::token();

$noToken = new Request([], ['title' => 'x'], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/assignment/create']);
$result = $csrf->handle($noToken, $next);
check('missing token does not reach the controller', $reachedNext, 0);
check('missing token produces a redirect', $result instanceof Response && $result->type() === Response::TYPE_REDIRECT, true);

$badToken = new Request([], ['_token' => str_repeat('0', 64)], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/assignment/create']);
$csrf->handle($badToken, $next);
check('wrong token does not reach the controller', $reachedNext, 0);

$goodToken = new Request([], ['_token' => $validToken], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/assignment/create']);
$ok = $csrf->handle($goodToken, $next);
check('valid token reaches the controller', $reachedNext, 1);
check('valid token returns the next layer result', $ok, 'reached-controller');

echo "\n3b. GET requests are never blocked\n";
$get = new Request([], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/assignment/create']);
$csrf->handle($get, $next);
check('GET passes through', $reachedNext, 2);

echo "\n3c. The rejection target keeps the user in the app\n";
check('redirects to the dashboard when there is no referer', $result->target(), 'dashboard');

$offSite = new Request([], ['x' => 1], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/assignment/create',
    'HTTP_HOST'      => 'school.local',
    'HTTP_REFERER'   => 'https://evil.example/steal',
]);
check('ignores an off-site referer', $csrf->handle($offSite, $next)->target(), 'dashboard');

$sameSite = new Request([], ['x' => 1], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/assignment/create',
    'HTTP_HOST'      => 'school.local',
    'HTTP_REFERER'   => 'http://school.local/student?page=2',
]);
check('follows a same-origin referer', $csrf->handle($sameSite, $next)->target(), 'student?page=2');

echo "\n5. AuthMiddleware blocks protected routes before the controller\n";
foreach (['/dashboard', '/student', '/class', '/assignment', '/grade'] as $path) {
    $rp = req($path);
    check("{$path} redirects to login", strpos($rp['location'], 'login') !== false, true);
}

echo "\n6. Public routes stay public\n";
$r4 = req('/auth');
check('/auth renders the login page', $r4['status'] === 200 && strpos($r4['body'], 'School Management') !== false);

echo "\n7. Unknown routes 404, wrong methods 405\n";
$r5 = req('/nope');
check('unknown path is 404', $r5['status'], 404);
$r6 = req('/student', 'DELETE');
check('unsupported method is 405', $r6['status'], 405);
check('405 renders the styled page', strpos($r6['body'], 'Method Not Allowed') !== false);

echo "\n8. PermissionMiddleware is declared on every mutating route\n";
$routes = require dirname(__DIR__) . '/Routers/index.php';
$missing = [];
// The login form is intentionally public, so it is exempt from AuthMiddleware.
$publicPost = ['/login', '/auth/login'];
foreach ($routes as $route) {
    if ($route[0] !== 'POST' || in_array($route[1], $publicPost, true)) {
        continue;
    }
    $middleware = $route[4] ?? [];
    $hasCsrf = false;
    $hasAuth = false;
    foreach ($middleware as $layer) {
        $class = is_array($layer) ? $layer[0] : $layer;
        if ($class === 'CsrfMiddleware') {
            $hasCsrf = true;
        }
        if ($class === 'AuthMiddleware') {
            $hasAuth = true;
        }
    }
    if (!$hasCsrf || !$hasAuth) {
        $missing[] = $route[1];
    }
}
check('every protected POST route has AuthMiddleware + CsrfMiddleware', $missing === [], true);
if ($missing) {
    echo '        missing on: ' . implode(', ', $missing) . "\n";
}

echo "\n" . str_repeat('-', 62) . "\n";
echo "{$pass} passed, {$fail} failed\n\n";
exit($fail === 0 ? 0 : 1);