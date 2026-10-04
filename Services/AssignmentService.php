<?php

class AssignmentService {
    private $assignmentRepo;
    private $submissionModel;
    private $uploader;

    public function __construct() {
        $this->assignmentRepo = new AssignmentRepository();
        $this->submissionModel = new AssignmentSubmission();
        $this->uploader = new Uploader();
    }

    public function getAllAssignments() {
        return $this->assignmentRepo->findAllWithDetails();
    }

    /**
     * One page of the assignment list plus totals for the paginator.
     * $teacherId narrows the list to one teacher's assignments.
     */
    public function paginateAssignments($page = 1, $perPage = 15, $keyword = null, $teacherId = null) {
        return $this->assignmentRepo->paginateWithDetails($page, $perPage, $keyword, $teacherId);
    }

    public function getAssignmentById($id) {
        return $this->assignmentRepo->findWithDetails($id);
    }

    public function getAssignmentsForStudent($studentId) {
        return $this->assignmentRepo->findForStudent($studentId);
    }

    public function getAssignmentsByTeacher($teacherId) {
        return $this->assignmentRepo->findByTeacherId($teacherId);
    }

    public function countAll() {
        return $this->assignmentRepo->countAll();
    }

    /**
     * Create an assignment, optionally attaching a supporting document.
     *
     * @param array      $data Raw form fields.
     * @param array|null $file One entry from $_FILES, if any.
     * @throws RuntimeException on validation or upload failure.
     */
    public function createAssignment($data, $file = null) {
        $payload = $this->buildAssignmentData($data);
        $payload['teacher_id'] = Auth::userId();

        $attachment = $this->storeAttachment($file);
        if ($attachment !== false) {
            $payload['attachments'] = $attachment;
        }

        $id = $this->assignmentRepo->create($payload);

        if (!$id && !empty($payload['attachments'])) {
            // Do not leave an orphaned upload behind if the insert failed.
            $this->uploader->delete($payload['attachments']);
        }

        return $id;
    }

    /**
     * @throws RuntimeException on validation or upload failure.
     */
    public function updateAssignment($id, $data, $file = null) {
        $payload = $this->buildAssignmentData($data);

        $existing = $this->assignmentRepo->findById($id);
        if (!$existing) {
            throw new RuntimeException('Assignment not found.');
        }

        $oldAttachment = $existing['attachments'] ?? null;

        if ($this->hasUpload($file)) {
            $payload['attachments'] = $this->uploader->store($file, 'assignments', $oldAttachment);
        } elseif (($data['remove_attachment'] ?? '') === '1') {
            $payload['attachments'] = null;
        } else {
            $payload['attachments'] = $oldAttachment;
        }

        $result = $this->assignmentRepo->update($id, $payload);

        if ($result && empty($payload['attachments']) && !empty($oldAttachment)) {
            $this->uploader->delete($oldAttachment);
        }

        return $result;
    }

    /**
     * Deleting an assignment also removes its uploaded document and any
     * files attached by students.
     */
    public function deleteAssignment($id) {
        $assignment = $this->assignmentRepo->findById($id);

        if (!$assignment) {
            return false;
        }

        $submissions = $this->submissionModel->findByAssignmentId($id);
        $result = $this->assignmentRepo->delete($id);

        if ($result) {
            if (!empty($assignment['attachments'])) {
                $this->uploader->delete($assignment['attachments']);
            }
            foreach ($submissions as $submission) {
                if (!empty($submission['file_path'])) {
                    $this->uploader->delete($submission['file_path']);
                }
            }
        }

        return $result;
    }

    /**
     * Record a student's work. Either typed text, an uploaded file, or both.
     *
     * @throws RuntimeException on validation or upload failure.
     */
    public function submitAssignment($assignmentId, $studentId, $content, $file = null) {
        if ($this->submissionModel->findByAssignmentAndStudent($assignmentId, $studentId)) {
            throw new RuntimeException('You have already submitted this assignment.');
        }

        $content = trim((string) $content);
        $filePath = false;

        if ($this->hasUpload($file)) {
            $filePath = $this->uploader->store($file, 'submissions');
        }

        if ($content === '' && !$filePath) {
            throw new RuntimeException('Type your answer or attach a file before submitting.');
        }

        $data = [
            'assignment_id' => $assignmentId,
            'student_id'    => $studentId,
            'content'       => $content !== '' ? $content : null,
            'submitted_at'  => date('Y-m-d H:i:s')
        ];

        if ($filePath) {
            $data['file_path'] = $filePath;
            $data['file_name'] = basename((string) $file['name']);
        }

        $id = $this->submissionModel->create($data);

        if (!$id && $filePath) {
            $this->uploader->delete($filePath);
            throw new RuntimeException('The submission could not be saved.');
        }

        return $id;
    }

    public function getSubmissions($assignmentId) {
        return $this->submissionModel->findByAssignmentId($assignmentId);
    }

    public function getSubmissionByStudent($assignmentId, $studentId) {
        return $this->submissionModel->findByAssignmentAndStudent($assignmentId, $studentId);
    }

    public function gradeSubmission($submissionId, $grade, $feedback) {
        $grade = ($grade === '' || $grade === null) ? null : max(0, min(100, (float) $grade));

        return $this->submissionModel->update($submissionId, [
            'grade'     => $grade,
            'feedback'  => trim((string) $feedback) ?: null,
            'graded_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * True when the request actually carried a file.
     */
    private function hasUpload($file) {
        if (!is_array($file) || !isset($file['error'])) {
            return false;
        }

        return $file['error'] !== UPLOAD_ERR_NO_FILE && (!isset($file['name']) || $file['name'] !== '');
    }

    /**
     * @return string|false Relative path, or false when no file was sent.
     */
    private function storeAttachment($file) {
        if (!$this->hasUpload($file)) {
            return false;
        }

        return $this->uploader->store($file, 'assignments');
    }

    /**
     * Whitelist + validate the assignment form fields.
     *
     * @throws RuntimeException
     */
    private function buildAssignmentData($data) {
        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            throw new RuntimeException('The title is required.');
        }

        $subjectId = (int) ($data['subject_id'] ?? 0);
        if ($subjectId <= 0) {
            throw new RuntimeException('Please select a subject.');
        }

        $classId = (int) ($data['class_id'] ?? 0);
        if ($classId <= 0) {
            throw new RuntimeException('Please select a class.');
        }

        $dueDate = $this->normaliseDateTime($data['due_date'] ?? '');
        if ($dueDate === null) {
            throw new RuntimeException('Please provide a valid due date and time.');
        }

        return [
            'title'       => $title,
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'subject_id'  => $subjectId,
            'class_id'    => $classId,
            'due_date'    => $dueDate
        ];
    }

    /**
     * <input type="datetime-local"> posts "2026-10-05T14:30"; MySQL wants a
     * space separator.
     */
    private function normaliseDateTime($value) {
        $value = trim(str_replace('T', ' ', (string) $value));
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        return $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp);
    }
}