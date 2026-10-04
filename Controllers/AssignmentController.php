<?php

class AssignmentController extends Controller {
    private $assignmentService;
    private $uploader;

    public function __construct() {
$this->assignmentService = new AssignmentService();
        $this->uploader = new Uploader();
    }

    public function index() {
        $keyword = trim((string) ($_GET['q'] ?? ''));
        $perPage = (int) ($_GET['per_page'] ?? 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100], true)) {
            $perPage = 15;
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $teacherId = null;
        if (Auth::hasRole('teacher')) {
            $teacherId = Auth::userId();
        } elseif (!Auth::hasRole('admin') && !Auth::hasRole('student')) {
            $this->redirect('dashboard');
        }

        $result = $this->assignmentService->paginateAssignments(
            $page,
            $perPage,
            $keyword !== '' ? $keyword : null,
            $teacherId
        );

        $totalPages = max(1, (int) ceil($result['total'] / $result['per_page']));
        if ($result['page'] > $totalPages) {
            $result = $this->assignmentService->paginateAssignments(
                $totalPages,
                $result['per_page'],
                $keyword !== '' ? $keyword : null,
                $teacherId
            );
        }

        $this->view('assignments/index', [
            'assignments' => $result['rows'],
            'keyword'     => $keyword,
            'pagination'  => [
                'page'        => $result['page'],
                'per_page'    => $result['per_page'],
                'total'       => $result['total'],
                'total_pages' => $totalPages,
                'offset'      => ($result['page'] - 1) * $result['per_page']
            ]
        ]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->assignmentService->createAssignment($_POST, $_FILES['document'] ?? null);
                Session::setFlash('success', 'Assignment created successfully.');
                $this->redirect('assignment');
            } catch (Throwable $e) {
                Session::setFlash('error', $e->getMessage());
                $this->view('assignments/create', array_merge(
                    $this->loadFormData(),
                    ['data' => $_POST]
                ));
            }
            return;
        }

        $this->view('assignments/create', array_merge($this->loadFormData(), ['data' => []]));
    }

    public function edit($id) {
        $assignment = $this->assignmentService->getAssignmentById($id);

        if (!$assignment) {
            Session::setFlash('error', 'Assignment not found.');
            $this->redirect('assignment');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->assignmentService->updateAssignment($id, $_POST, $_FILES['document'] ?? null);
                Session::setFlash('success', 'Assignment updated successfully.');
                $this->redirect('assignment');
            } catch (Throwable $e) {
                Session::setFlash('error', $e->getMessage());
                $this->view('assignments/edit', array_merge(
                    $this->loadFormData(),
                    ['assignment' => $assignment, 'data' => $_POST]
                ));
            }
            return;
        }

        $this->view('assignments/edit', array_merge(
            $this->loadFormData(),
            ['assignment' => $assignment, 'data' => []]
        ));
    }

    public function show($id) {
        $assignment = $this->assignmentService->getAssignmentById($id);

        if (!$assignment) {
            Session::setFlash('error', 'Assignment not found.');
            $this->redirect('assignment');
        }

        $data = ['assignment' => $assignment, 'submission' => null];

        if (Auth::hasRole('student')) {
            $studentService = new StudentService();
            $student = $studentService->getStudentByUserId(Auth::userId());

            if (!$student || (int) $student['class_id'] !== (int) $assignment['class_id']) {
                $this->forbiddenView();
            }

            $data['submission'] = $this->assignmentService->getSubmissionByStudent($id, $student['id']);
        } elseif (Auth::hasPermission('manage_grades')) {
            $data['submissions'] = $this->assignmentService->getSubmissions($id);
        }

        $this->view('assignments/view', $data);
    }

    public function submit($id) {
        $assignment = $this->assignmentService->getAssignmentById($id);

        if (!$assignment) {
            Session::setFlash('error', 'Assignment not found.');
            $this->redirect('assignment');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentService = new StudentService();
            $student = $studentService->getStudentByUserId(Auth::userId());

            if (!$student) {
                Session::setFlash('error', 'No student profile is linked to your account.');
                $this->redirect('assignment/show/' . $id);
            }

            try {
                $this->assignmentService->submitAssignment(
                    $id,
                    $student['id'],
                    $_POST['content'] ?? '',
                    $_FILES['file'] ?? null
                );
                Session::setFlash('success', 'Assignment submitted successfully.');
            } catch (Throwable $e) {
                Session::setFlash('error', $e->getMessage());
            }

            $this->redirect('assignment/show/' . $id);
            return;
        }

        $this->view('assignments/submit', ['assignment' => $assignment]);
    }

    public function grade($submissionId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('assignment');
        }

        $assignmentId = $_POST['assignment_id'] ?? null;

        try {
            $this->assignmentService->gradeSubmission(
                $submissionId,
                $_POST['score'] ?? ($_POST['grade'] ?? null),
                $_POST['feedback'] ?? ''
            );
            Session::setFlash('success', 'Submission graded successfully.');
        } catch (Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect($assignmentId ? 'assignment/show/' . (int) $assignmentId : 'assignment');
    }

    public function delete($id) {
        try {
            if ($this->assignmentService->deleteAssignment($id)) {
                Session::setFlash('success', 'Assignment deleted successfully.');
            } else {
                Session::setFlash('error', 'Failed to delete assignment.');
            }
        } catch (Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect('assignment');
    }

    /**
     * Subjects and classes for the form selects.
     */
    private function loadFormData() {
        $subjectModel = new Subject();
        $classModel = new ClassRoom();

        return [
            'subjects' => $subjectModel->findAll(),
            'classes'  => $classModel->findAll()
        ];
    }

    private function forbiddenView() {
        http_response_code(403);
        $this->view('errors/403');
        exit;
    }
}