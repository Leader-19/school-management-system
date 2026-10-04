<?php

class AttendanceService {
    private $sessionModel;
    private $attendanceModel;
    private $classRepo;

    public function __construct() {
        $this->sessionModel    = new AttendanceSession();
        $this->attendanceModel = new StudentAttendance();
        $this->classRepo       = new ClassRoomRepository();
    }

    /** Session types offered on the take-attendance form. */
    public const SESSION_TYPES = ['full_day', 'morning', 'afternoon', 'custom'];

    /**
     * Students of a class for the marking form.
     */
    public function getClassStudents($classId) {
        return $this->classRepo->getClassStudents($classId);
    }

    /**
     * Paginated session history of one class.
     */
    public function paginateSessions($classId, $page = 1, $perPage = 10) {
        return $this->sessionModel->paginateByClassId($classId, $page, $perPage);
    }

    public function getSession($id) {
        return $this->sessionModel->findWithDetails($id);
    }

    /**
     * Existing marks for a session keyed by student_id, for pre-filling the form.
     */
    public function getMarks($sessionId) {
        $marks = [];
        foreach ($this->attendanceModel->forSession($sessionId) as $row) {
            $marks[(int) $row['student_id']] = $row['status'];
        }
        return $marks;
    }

    /**
     * Create (or reuse) the session for class+date+type and save every
     * student's status in one transaction.
     *
     * @throws RuntimeException when there is nothing to save or inputs are invalid.
     */
    public function saveSession($classId, $data) {
        $classId = (int) $classId;
        if ($classId <= 0) {
            throw new RuntimeException('Please select a class.');
        }

        $sessionDate = trim((string) ($data['session_date'] ?? ''));
        $timestamp   = strtotime($sessionDate);
        if ($timestamp === false) {
            throw new RuntimeException('Please provide a valid session date.');
        }
        $sessionDate = date('Y-m-d', $timestamp);

        // Optional custom label, e.g. "Week 3 extra lab".
        $sessionType = trim((string) ($data['session_type'] ?? 'full_day'));
        if ($sessionType === 'custom') {
            $custom = trim((string) ($data['session_type_custom'] ?? ''));
            if ($custom === '') {
                throw new RuntimeException('Please name the custom session or pick a standard type.');
            }
            if (mb_strlen($custom) > 50) {
                throw new RuntimeException('The session name must be 50 characters or fewer.');
            }
            $sessionType = $custom;
        }

        $subjectId = !empty($data['subject_id']) ? (int) $data['subject_id'] : null;

        $marks = [];
        if (!empty($data['attendance']) && is_array($data['attendance'])) {
            foreach ($data['attendance'] as $studentId => $status) {
                if (in_array($status, StudentAttendance::STATUSES, true)) {
                    $marks[(int) $studentId] = $status;
                }
            }
        }

        if ($marks === []) {
            throw new RuntimeException('Mark at least one student before saving.');
        }

        $session = $this->sessionModel->findOrCreate(
            $classId,
            $sessionDate,
            $sessionType,
            $subjectId,
            Auth::userId()
        );

        if (!$session) {
            throw new RuntimeException('The session could not be created.');
        }

        $this->attendanceModel->syncForSession((int) $session['id'], $marks);

        return (int) $session['id'];
    }

    /**
     * Attendance history + summary of one student.
     */
    public function getStudentAttendance($studentId) {
        return [
            'history' => $this->attendanceModel->forStudent($studentId),
            'summary' => $this->attendanceModel->summaryForStudent($studentId)
        ];
    }
}
