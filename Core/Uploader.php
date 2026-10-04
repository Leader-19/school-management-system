<?php

/**
 * Uploader
 * --------
 * Small wrapper around PHP's file upload handling that keeps user supplied
 * files outside the document root.
 *
 * Every upload is validated (extension AND real MIME type AND size), stored
 * under a generated random name, and recorded with its original name so it
 * can be served back with a meaningful Content-Disposition later.
 */
class Uploader {
    /** Extension => [mime types accepted, friendly label]. */
    const ALLOWED_TYPES = [
        'pdf'  => [['application/pdf'], 'PDF'],
        'doc'  => [['application/msword'], 'DOC'],
        'docx' => [['application/vnd.openxmlformats-officedocument.wordprocessingml.document'], 'DOCX'],
        'xls'  => [['application/vnd.ms-excel'], 'XLS'],
        'xlsx' => [['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'], 'XLSX'],
        'ppt'  => [['application/vnd.ms-powerpoint'], 'PPT'],
        'pptx' => [['application/vnd.openxmlformats-officedocument.presentationml.presentation'], 'PPTX'],
        'txt'  => [['text/plain'], 'TXT'],
        'csv'  => [['text/plain', 'text/csv', 'application/csv'], 'CSV'],
        'jpg'  => [['image/jpeg'], 'JPG'],
        'jpeg' => [['image/jpeg'], 'JPEG'],
        'png'  => [['image/png'], 'PNG'],
        'gif'  => [['image/gif'], 'GIF'],
        'webp' => [['image/webp'], 'WEBP'],
        'zip'  => [['application/zip', 'application/x-zip-compressed'], 'ZIP'],
    ];

    const MAX_BYTES = 10485760; // 10 MB

    /** Absolute directory holding every stored upload. */
    private $baseDir;

    /**
     * @param string $baseDir Absolute directory that will hold the uploads.
     */
    public function __construct($baseDir = null) {
        $this->baseDir = $baseDir ?? (defined('BASE_PATH') ? BASE_PATH . '/storage/uploads' : __DIR__ . '/../storage/uploads');
    }

    /**
     * Store one uploaded file.
     *
     * @param array  $file     One entry from $_FILES.
     * @param string $subdir   Optional sub folder, e.g. 'assignments'.
     * @param string $oldPath  Previously stored path, deleted when a new
     *                         file replaces it.
     * @return string|false    Relative path to store in the database.
     * @throws RuntimeException with a user-facing message on any problem.
     */
    public function store(array $file, $subdir = '', $oldPath = null) {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new RuntimeException('Invalid upload request.');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return false; // Nothing chosen - not an error.
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('The file is larger than the server allows.');
            case UPLOAD_ERR_PARTIAL:
                throw new RuntimeException('The file was only partially uploaded. Please try again.');
            default:
                throw new RuntimeException('The file could not be uploaded.');
        }

        if (!is_string($file['tmp_name'] ?? null) || $file['tmp_name'] === '' || !$this->isRealUpload($file['tmp_name'])) {
            throw new RuntimeException('Invalid upload source.');
        }

        $size = filesize($file['tmp_name']);
        if ($size === false || $size === 0) {
            throw new RuntimeException('The uploaded file is empty.');
        }
        if ($size > self::MAX_BYTES) {
            throw new RuntimeException('The file is too large. Maximum size is ' . self::formatSize(self::MAX_BYTES) . '.');
        }

        $originalName = basename((string) $file['name']);
        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));

        if (!isset(self::ALLOWED_TYPES[$extension])) {
            throw new RuntimeException('Files of type "' . ($extension !== '' ? $extension : 'unknown') . '" are not allowed. Allowed types: ' . $this->allowedList() . '.');
        }

        $mime = $this->detectMime($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED_TYPES[$extension][0], true)) {
            throw new RuntimeException('The file content does not match its extension. Allowed types: ' . $this->allowedList() . '.');
        }

        $relativeDir = trim(str_replace('..', '', (string) $subdir), '/\\');
        $targetDir = $this->baseDir . ($relativeDir !== '' ? '/' . $relativeDir : '');

        if (!$this->ensureDirectory($targetDir)) {
            throw new RuntimeException('The upload folder is not writable. Check the permissions on storage/uploads.');
        }

        $storedName = $this->uniqueName($extension);
        $targetPath = $targetDir . '/' . $storedName;

        if (!$this->persist($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('The file could not be saved to disk.');
        }

        @chmod($targetPath, 0644);

        $relativePath = ($relativeDir !== '' ? $relativeDir . '/' : '') . $storedName;

        // Keep the display name next to the file so downloads can use it.
        @file_put_contents($targetPath . '.original', $originalName);

        if (!empty($oldPath)) {
            $this->delete($oldPath);
        }

        return $relativePath;
    }

    public function rememberOriginalName($relativePath, $originalName) {
        $absolute = $this->absolutePath($relativePath);
        if ($absolute !== null) {
            @file_put_contents($absolute . '.original', $originalName);
        }
    }

    /**
     * Resolve a stored relative path to an absolute one inside the upload
     * directory, or null when the path escapes that directory.
     */
    public function absolutePath($relativePath) {
        $relativePath = (string) $relativePath;
        if ($relativePath === '') {
            return null;
        }

        $relativePath = str_replace('\\', '/', $relativePath);
        if (strpos($relativePath, '..') !== false || strpos($relativePath, ':') !== false || $relativePath[0] === '/') {
            return null;
        }

        $base = realpath($this->baseDir);
        if ($base === false) {
            return null;
        }

        $candidate = realpath($this->baseDir . '/' . $relativePath);
        if ($candidate === false || !is_file($candidate)) {
            return null;
        }

        // Belt and braces: the resolved file must stay inside the base dir.
        if (strpos(str_replace('\\', '/', $candidate), str_replace('\\', '/', $base) . '/') !== 0) {
            return null;
        }

        return $candidate;
    }

    public function exists($relativePath) {
        return $this->absolutePath($relativePath) !== null;
    }

    /**
     * Original file name, preserved in the "___original" sidecar file, so a
     * download keeps the name the user recognises.
     */
    public function originalName($relativePath) {
        $absolute = $this->absolutePath($relativePath);
        if ($absolute === null) {
            return basename((string) $relativePath);
        }

        $sidecar = $absolute . '.original';
        if (is_file($sidecar)) {
            return (string) file_get_contents($sidecar);
        }

        return basename((string) $relativePath);
    }

    public function formatSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max((float) $bytes, 0);
        $power = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);

        return round($bytes / (1024 ** $power), $power === 0 ? 0 : 1) . ' ' . $units[$power];
    }

    /**
     * Delete a stored upload together with its original-name sidecar.
     */
    public function delete($relativePath) {
        $absolute = $this->absolutePath($relativePath);
        if ($absolute !== null) {
            @unlink($absolute . '.original');
            @unlink($absolute);
        }
    }

    public function allowedList() {
        $labels = array_unique(array_column(self::ALLOWED_TYPES, 1));
        return implode(', ', $labels);
    }

    /**
     * Confirm the file really arrived through an HTTP upload.
     *
     * Isolated in its own method so tests can exercise the validation rules
     * on the CLI, where is_uploaded_file() is always false.
     */
    protected function isRealUpload($path) {
        return is_uploaded_file($path);
    }

    /**
     * Move the validated upload to its final location.
     */
    protected function persist($from, $to) {
        return move_uploaded_file($from, $to);
    }

    /**
     * Create the target directory when it is missing and confirm it is
     * writable, so uploads never fail midway through a request.
     */
    private function ensureDirectory($dir) {
        if (is_dir($dir)) {
            return is_writable($dir);
        }

        if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
            return false;
        }

        return is_writable($dir);
    }

    private function uniqueName($extension) {
        try {
            $random = bin2hex(random_bytes(16));
        } catch (Exception $e) {
            $random = bin2hex(openssl_random_pseudo_bytes(16));
        }

        return date('Ymd_His') . '_' . $random . '.' . $extension;
    }

    /**
     * Prefer the fileinfo extension; fall back to a content sniff when the
     * extension is unavailable so the check still works on stripped builds.
     */
    private function detectMime($path) {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mime = finfo_file($finfo, $path);
                finfo_close($finfo);
                if (is_string($mime) && $mime !== '') {
                    return $mime;
                }
            }
        }

        $info = @getimagesize($path);
        if (is_array($info) && isset($info['mime'])) {
            return $info['mime'];
        }

        return 'application/octet-stream';
    }
}