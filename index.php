<?php
/**
 * Application bootstrap.
 *
 * Keeps error display off in the browser: a raw warning printed mid-page
 * breaks the layout and can leak file paths. Errors go to the PHP error log
 * instead, and APP_DEBUG=true re-enables them while developing.
 */
define('APP_DEBUG', getenv('APP_DEBUG') === '1');

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

session_start();
define('BASE_PATH', __DIR__);

// Derive BASE_URL from the script location so the app works both at
// the domain root (e.g. "php -S localhost:8000" from the project
// folder -> BASE_URL = '') and inside a subfolder (e.g. XAMPP
// htdocs/student-management-system -> BASE_URL = '/student-management-system').
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$scriptDir = rtrim($scriptDir, '/');
define('BASE_URL', ($scriptDir === '/' || $scriptDir === '.') ? '' : $scriptDir);

spl_autoload_register(function ($className) {
    // Middleware/ holds concrete request filters (AuthMiddleware, ...);
    // Core/ holds the framework base classes they extend.
    $paths = [
        'Core/',
        'Middleware/',
        'Controllers/',
        'Models/',
        'Repository/',
        'Services/',
        'config/'
    ];

    foreach ($paths as $path) {
        $file = BASE_PATH . '/' . $path . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

$app = new App();
$app->run();
