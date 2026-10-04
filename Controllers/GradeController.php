<?php

class GradeController extends Controller {
    private $gradeService;

    public function __construct() {
$this->gradeService = new GradeService();
    }

    public function index() {
        $keyword = trim((string) ($_GET['q'] ?? ''));

        if (Auth::hasPermission('manage_grades')) {
            $perPage = (int) ($_GET['per_page'] ?? 15);
            if (!in_array($perPage, [10, 15, 25, 50, 100], true)) {
                $perPage = 15;
            }
            $page = max(1, (int) ($_GET['page'] ?? 1));

            $result = $this->gradeService->paginateGrades(
                $page,
                $perPage,
                $keyword !== '' ? $keyword : null
            );

            // A bookmarked page beyond the last one falls back to the last page.
            $totalPages = max(1, (int) ceil($result['total'] / $result['per_page']));
            if ($result['page'] > $totalPages) {
                $result = $this->gradeService->paginateGrades(
                    $totalPages,
                    $result['per_page'],
                    $keyword !== '' ? $keyword : null
                );
            }

            $this->view('grades/index', [
                'grades'     => $result['rows'],
                'keyword'    => $keyword,
                'pagination' => [
                    'page'        => $result['page'],
                    'per_page'    => $result['per_page'],
                    'total'       => $result['total'],
                    'total_pages' => $totalPages,
                    'offset'      => ($result['page'] - 1) * $result['per_page']
                ]
            ]);
            return;
        }

        if (Auth::hasPermission('view_own_grades')) {
            $studentService = new StudentService();
            $student = $studentService->getStudentByUserId(Auth::userId());
            $grades = $student ? $this->gradeService->getStudentGrades($student['id']) : [];

            $this->view('grades/index', [
                'grades'  => $grades,
                'keyword' => $keyword
            ]);
            return;
        }

        $this->redirect('dashboard');
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Map form fields to the grades table columns
            $data = [
                'student_id'    => $_POST['student_id'] ?? null,
                'subject_id'    => $_POST['subject_id'] ?? null,
                'score'         => $_POST['score'] ?? null,
                'semester'      => $_POST['semester'] ?? '1',
                'academic_year' => $_POST['academic_year'] ?? ($_POST['year'] ?? date('Y'))
            ];
            
            if ($this->gradeService->addGrade($data)) {
                Session::setFlash('success', 'Grade added successfully.');
                $this->redirect('grade');
            } else {
                Session::setFlash('error', 'Failed to add grade.');
                $viewData = array_merge(['data' => $data], $this->loadCreateFormData());
                $this->view('grades/create', $viewData);
            }
        } else {
            $this->view('grades/create', $this->loadCreateFormData());
        }
    }

    public function delete($id) {
        if ($this->gradeService->deleteGrade($id)) {
            Session::setFlash('success', 'Grade deleted successfully.');
        } else {
            Session::setFlash('error', 'Failed to delete grade.');
        }
        
        $this->redirect('grade');
    }

    /**
     * Load students and subjects for the grade form selects.
     */
    private function loadCreateFormData() {
        $studentModel = new Student();
        $subjectModel = new Subject();
        return [
            'students' => $studentModel->findAll(),
            'subjects' => $subjectModel->findAll()
        ];
    }
}
