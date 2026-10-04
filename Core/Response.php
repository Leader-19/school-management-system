<?php

/**
 * Response
 * --------
 * Small value object describing what to send back. The Router turns it into
 * real output, so middleware can return a redirect or an error page without
 * touching headers directly.
 */
class Response {
    const TYPE_REDIRECT = 'redirect';
    const TYPE_VIEW     = 'view';
    const TYPE_JSON     = 'json';
    const TYPE_RAW      = 'raw';
    const TYPE_HANDLED  = 'handled';

    private $type;
    private $target;
    private $data;
    private $status;
    private $headers;

    public function __construct($type, $target = null, $data = null, $status = 200, array $headers = []) {
        $this->type    = $type;
        $this->target  = $target;
        $this->data    = $data;
        $this->status  = $status;
        $this->headers = $headers;
    }

    public static function redirect($url, $status = 302) {
        return new self(self::TYPE_REDIRECT, $url, null, $status);
    }

    /**
     * Render an error view inside the main layout.
     */
    public static function error($code, $message = '') {
        return new self(self::TYPE_VIEW, 'errors/' . (int) $code, ['httpCode' => $code, 'message' => $message], $code);
    }

    public static function json($data, $status = 200) {
        return new self(self::TYPE_JSON, null, $data, $status);
    }

    public static function raw($body, $status = 200, array $headers = []) {
        return new self(self::TYPE_RAW, null, $body, $status, $headers);
    }

    /**
     * The middleware already wrote the output itself (a file download, for
     * instance). Tell the Router to stop without emitting anything more.
     */
    public static function handled($status = 200) {
        return new self(self::TYPE_HANDLED, null, null, $status);
    }

    public function type() {
        return $this->type;
    }

    public function target() {
        return $this->target;
    }

    public function data() {
        return $this->data;
    }

    public function status() {
        return $this->status;
    }

    public function headers() {
        return $this->headers;
    }
}