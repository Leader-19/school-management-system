<?php

class AttendanceController extends Controller {
    private $attendanceService;
    private $classRepo;

    /** Posted statuses accepted per student. */
    private const STATUSES = ['present', 'absent', 'late', 'excused'];

    public function __construct() {
        $this->attendanceService = new AttendanceService();
        $this->classRepo = new ClassRoomRepository();
    }

    /**
     * Class picker + session history (staff with manage_students).
     */
    public function index() {
        $classes = $this->classRepo->findAll();
        $classId = (int) ($_GET['class_id'] ?? 0);

        $sessions = [];
        $pagination = null;

        if ($classId > 0) {
            $perPage = (int) ($_GET['per_page'] ?? 10);
            if (!in_array($perPage, [5, 10, 20, 50], true)) {
                $perPage = 10;
            }
            $page = max(1, (int) ($_GET['page'] ?? 1));

            $result = $this->attendanceService->paginateSessions($classId, $page, $perPage);

            $totalPages = max(1, (int) ceil($result['total'] / $result['per_page']));
            if ($result['page'] > $totalPages) {
                $result = $this->attendanceService->paginateSessions($classId, $totalPages, $perPage);
            }

            $sessions = $result['rows'];
            $pagination = [
                'page'        => $result['page'],
                'per_page'    => $result['per_page'],
                'total'       => $result['total'],
                'total_pages' => $totalPages,
                'offset'      => ($result['page'] - 1) * $result['per_page']
            ];
        }

        $this->view('attendance/index', [
            'classes'    => $classes,
            'classId'    => $classId,
            'sessions'   => $sessions,
            'pagination' => $pagination
        ]);
    }

    /**
     * GET  - the marking form (pre-filled when the session already exists).
     * POST - save every student's status for that session.
     */
    public function take($classId) {
        $classId = (int) $classId;
        $class = $this->classRepo->findById($classId);

        if (!$class) {
            Session::setFlash('error', 'Class not found.');
            $this->redirect('attendance');
        }

        $students = $this->attendanceService->getClassStudents($classId);

        $sessionDate  = trim((string) ($_GET['date'] ?? ($_POST['session_date'] ?? date('Y-m-d'))));
        $sessionType  = trim((string) ($_GET['type'] ?? ($_POST['session_type'] ?? 'full_day')));
        $subjectId    = trim((string) ($_GET['subject_id'] ?? ($_POST['subject_id'] ?? '')));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $sessionId = $this->attendanceService->saveSession($classId, $_POST);
                Session::setFlash('success', "Attendance saved for {$sessionDate} ({$sessionType}).");
                $this->redirect('attendance/take/' . $classId . '?date=' . urlencode($sessionDate)
                    . '&type=' . urlencode($sessionType) . '&subject_id=' . urlencode($subjectId));
            } catch (Throwable $e) {
                Session::setFlash('error', $e->getMessage());
                $marks = [];
            }
        }

        // Reuse the existing session so the form pre-fills previous marks.
        $sessionModel = new AttendanceSession();
        $existing = $sessionModel->findOrCreate($classId, $sessionDate, $sessionType, $subjectId, null);
        $marks    = $this->attendanceService->getMarks((int) $existing['id']);
        $subjects = (new Subject())->findByClassId($classId);

        $this->view('attendance/take', [
            'class'       => $class,
            'students'    => $students,
            'sessionDate' => $sessionDate,
            'sessionType' => $sessionType,
            'subjectId'   => $subjectId,
            'subjects'    => $subjects,
            'existing'    => $existing,
            'marks'       => $marks,
            'statuses'    => self::STATUSES
        ]);
    }

    /**
     * Attendance history of one student (used from the student profile page).
     */
    public function student($studentId) {
        $studentId = (int) $studentId;

        $studentService = new StudentService();
        $student = $studentService->getStudentById($studentId);

        if (!$student) {
            Session::setFlash('error', 'Student not found.');
            $this->redirect('student');
        }

        $data = $this->attendanceService->getStudentAttendance($studentId);

        $this->view('attendance/student', [
            'student' => $student,
            'history' => $data['history'],
            'summary' => $data['summary'],
            'statuses' => self::STATUSES
        ]);
    }
}
