<?php
require_once __DIR__ . '/bootstrap.php';

echo "bootstrap loaded\n";
$ctx = stream_context_create(['http' => ['method' => 'GET', 'ignore_errors' => true, 'follow_location' => 0, 'timeout' => 10]]);
$body = @file_get_contents('http://127.0.0.1:8766/login', false, $ctx);
echo 'body false? '; var_dump($body === false);
if ($body === false) {
    $e = error_get_last();
    echo 'last error: ' . ($e['message'] ?? 'none') . "\n";
}
echo 'headers: ' . count($http_response_header ?? []) . "\n";