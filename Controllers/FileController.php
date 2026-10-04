<?php

/**
 * FileController
 * --------------
 * Serves the documents stored by Uploader.
 *
 * Files live in /storage/uploads, outside the document root, so nothing is
 * reachable by guessing a URL - every request goes through the permission
 * checks below.
 */
class FileController extends Controller {
    private $uploader;
    private $assignmentModel;
    private $submissionModel;
    private $studentModel;

    public function __construct() {
        $this->uploader = new Uploader();
        $this->assignmentModel = new Assignment();
        $this->submissionModel = new AssignmentSubmission();
        $this->studentModel = new Student();
    }

    /**
     * Download the document attached to an assignment.
     */
    public function assignmentDocument($assignmentId) {
        $assignment = $this->assignmentModel->findById($assignmentId);

        if (!$assignment || empty($assignment['attachments'])) {
            $this->notFound('That document is no longer available.');
        }

        if (!Auth::hasPermission('view_assignments')) {
            $this->forbidden();
        }

        // Students may only download documents for assignments in their class.
        if (!Auth::hasPermission('create_assignments')) {
            $student = $this->studentModel->findByUserId(Auth::userId());

            if (!$student || (int) $student['class_id'] !== (int) $assignment['class_id']) {
                $this->forbidden();
            }
        }

        $this->sendFile($assignment['attachments']);
    }

    /**
     * Download the file a student attached to their submission.
     */
    public function submissionFile($submissionId) {
        $submission = $this->submissionModel->findById($submissionId);

        if (!$submission || empty($submission['file_path'])) {
            $this->notFound('That file is no longer available.');
        }

        $isOwner = false;
        $student = $this->studentModel->findByUserId(Auth::userId());
        if ($student && (int) $student['id'] === (int) $submission['student_id']) {
            $isOwner = true;
        }

        // The owner, or staff who can grade submissions.
        if (!$isOwner && !Auth::hasPermission('manage_grades')) {
            $this->forbidden();
        }

        $this->sendFile($submission['file_path'], $submission['file_name'] ?? null);
    }

    private function sendFile($relativePath, $fallbackName = null) {
        $absolute = $this->uploader->absolutePath($relativePath);

        if ($absolute === null) {
            $this->notFound('That file is no longer available.');
        }

        $downloadName = $fallbackName ?: $this->uploader->originalName($relativePath);
        $downloadName = preg_replace('/[\x00-\x1F\/\\\\]/', '_', (string) $downloadName);
        $downloadName = basename($downloadName !== '' ? $downloadName : 'download');

        $mime = function_exists('mime_content_type') ? mime_content_type($absolute) : false;
        if (!is_string($mime) || $mime === '') {
            $mime = 'application/octet-stream';
        }

        $size = filesize($absolute);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . $size);
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        header('Pragma: no-cache');

        readfile($absolute);
        exit;
    }

    private function notFound($message) {
        Session::setFlash('error', $message);
        $this->redirect('assignment');
    }

    private function forbidden() {
        http_response_code(403);
        $this->view('errors/403');
        exit;
    }
}