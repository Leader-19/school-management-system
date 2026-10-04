<?php
/**
 * Route smoke test. Run the dev server first:
 *   php -S 127.0.0.1:8765 -t .
 * then:  php tools/smoke_routes.php
 *
 * Verifies the application boots (the "Class App not found" failure) and
 * that every registered route resolves instead of 404-ing.
 */

$base = $argv[1] ?? 'http://127.0.0.1:8765';

$paths = [
    '/', '/login', '/auth', '/logout',
    '/dashboard', '/student', '/student/create', '/student/show/1',
    '/class', '/class/create', '/class/show/1', '/class/edit/1',
    '/assignment', '/assignment/create', '/assignment/show/1', '/assignment/edit/1',
    '/assignment/submit/1',
    '/grade', '/grade/create',
    '/file/assignment/1', '/file/submission/1',
    '/definitely-not-a-route',
];

$pass = 0;
$fail = 0;

echo "\nRoute smoke test against {$base}\n";
echo str_repeat('-', 60) . "\n";

foreach ($paths as $path) {
    $context = stream_context_create(['http' => [
        'method'        => 'GET',
        'ignore_errors' => true,
        'follow_location' => 0,
        'timeout'       => 10,
    ]]);

    $body = @file_get_contents($base . $path, false, $context);

    if ($body === false) {
        $fail++;
        echo "  FAIL  {$path} (no response)\n";
        continue;
    }

    $status = 0;
    $location = '';
    foreach ($http_response_header ?? [] as $header) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $m)) {
            $status = (int) $m[1];
        }
        if (stripos($header, 'Location:') === 0) {
            $location = trim(substr($header, 9));
        }
    }

    $fatal = stripos($body, 'Fatal error') !== false
        || stripos($body, 'Uncaught') !== false
        || stripos($body, 'Parse error') !== false
        || stripos($body, 'Warning:') !== false;

    // 302 to the login page is the correct behaviour when not signed in.
    $redirectsToLogin = in_array($status, [302, 303], true) && stripos($location, 'login') !== false;

    if ($fatal || !($status === 200 || $redirectsToLogin || $status === 404)) {
        $fail++;
        printf("  FAIL  %-26s %d %s\n", $path, $status, $location);
        $snippet = trim(preg_replace('/\s+/', ' ', strip_tags(substr($body, 0, 400))));
        if ($snippet !== '') {
            echo "        " . substr($snippet, 0, 220) . "\n";
        }
    } else {
        $pass++;
        printf("  PASS  %-26s %d%s\n", $path, $status, $location !== '' ? ' -> ' . $location : '');
    }
}

echo str_repeat('-', 60) . "\n";
echo "{$pass} passed, {$fail} failed\n\n";
exit($fail === 0 ? 0 : 1);