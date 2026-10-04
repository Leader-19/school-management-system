<?php

/**
 * Request
 * -------
 * Read-only view over the current HTTP request.
 *
 * Controllers and middleware use this instead of touching $_GET/$_POST/
 * $_SERVER directly, which keeps input handling in one testable place.
 */
class Request {
    private $method;
    private $uri;
    private $query;
    private $body;
    private $files;
    private $server;

    public function __construct(array $query = null, array $body = null, array $files = null, array $server = null) {
        $this->query  = $query ?? $_GET;
        $this->body   = $body ?? $_POST;
        $this->files  = $files ?? $_FILES;
        $this->server = $server ?? $_SERVER;
        $this->method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
        $this->uri    = $this->resolveUri();
    }

    public function method() {
        return $this->method;
    }

    public function uri() {
        return $this->uri;
    }

    public function isPost() {
        return $this->method === 'POST';
    }

    /**
     * @return mixed
     */
    public function input($key, $default = null) {
        $value = $this->body[$key] ?? $this->query[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    public function post($key, $default = null) {
        $value = $this->body[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    public function queryParam($key, $default = null) {
        $value = $this->query[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    public function has($key) {
        return isset($this->body[$key]) || isset($this->query[$key]);
    }

    public function integer($key, $default = 0) {
        return (int) $this->input($key, $default);
    }

    public function all(): array {
        return array_merge($this->query, $this->body);
    }

    /**
     * One entry from $_FILES, or null when nothing was sent under that name.
     */
    public function file($key) {
        $file = $this->files[$key] ?? null;

        if (!is_array($file) || !isset($file['error'])) {
            return null;
        }

        // A multi-file input arrives with a nested array; unsupported here.
        if (is_array($file['error'])) {
            return null;
        }

        return $file;
    }

    public function hasFile($key) {
        $file = $this->file($key);

        return $file !== null
            && $file['error'] !== UPLOAD_ERR_NO_FILE
            && (!isset($file['name']) || $file['name'] !== '');
    }

    public function header($name, $default = null) {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        if (isset($this->server[$key])) {
            return $this->server[$key];
        }

        // Content-Type / Content-Length arrive without the HTTP_ prefix.
        $bare = strtoupper(str_replace('-', '_', $name));
        if (isset($this->server[$bare])) {
            return $this->server[$bare];
        }

        return $default;
    }

    public function ip() {
        return (string) $this->server['REMOTE_ADDR'];
    }

    public function userAgent() {
        return (string) $this->header('User-Agent', '');
    }

    /**
     * True when the client asked for JSON.
     */
    public function wantsJson() {
        $accept = $this->header('Accept', '');
        return stripos($accept, 'application/json') !== false
            || $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Full URL of the current request, used as the CSRF referer fallback.
     */
    public function fullUrl() {
        $scheme = (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $this->server['HTTP_HOST'] ?? 'localhost';

        return $scheme . '://' . $host . ($this->server['REQUEST_URI'] ?? '/');
    }

    /**
     * Strip the base path and query string, matching App::getRequestUrl().
     */
    private function resolveUri() {
        $uri = parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';

        if ($base !== '' && strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }

        return trim($uri, '/');
    }
}