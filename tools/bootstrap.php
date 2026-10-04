<?php
/**
 * Shared bootstrap for the CLI check scripts in tools/.
 *
 * Loads the framework classes without running the application, and starts a
 * session so Session-backed code (CSRF tokens) behaves as it does on a real
 * request.
 */

define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', '');

error_reporting(E_ALL);
ini_set('display_errors', '1');

spl_autoload_register(function ($className) {
    $paths = ['Core/', 'Middleware/', 'Controllers/', 'Models/', 'Repository/', 'Services/', 'config/'];

    foreach ($paths as $path) {
        $file = BASE_PATH . '/' . $path . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

if (PHP_SAPI === 'cli' && session_status() === PHP_SESSION_NONE) {
    @session_start();
}