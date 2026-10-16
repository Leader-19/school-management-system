<?php

class TeacherController extends Controller {
    private $teacherService;

    public function __construct() {
        $this->teacherService = new TeacherService();
    }

    /**
     * Paginated teacher list with search (?q, ?page, ?per_page).
     */
    public function index() {
        $keyword = trim((string) ($_GET['q'] ?? ''));
        $perPage = (int) ($_GET['per_page'] ?? 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100], true)) {
            $perPage = 15;
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $result = $this->teacherService->paginateTeachers(
            $page,
            $perPage,
            $keyword !== '' ? $keyword : null
        );

        // A bookmarked page beyond the last one falls back to the last page.
        $totalPages = max(1, (int) ceil($result['total'] / $result['per_page']));
        if ($result['page'] > $totalPages) {
            $result = $this->teacherService->paginateTeachers(
                $totalPages,
                $result['per_page'],
                $keyword !== '' ? $keyword : null
            );
        }

        $this->view('teachers/index', [
            'teachers'   => $result['rows'],
            'keyword'    => $keyword,
            'pagination' => [
                'page'        => $result['page'],
                'per_page'    => $result['per_page'],
                'total'       => $result['total'],
                'total_pages' => $totalPages,
                'offset'      => ($result['page'] - 1) * $result['per_page']
            ]
        ]);
    }

    public function import() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!isset($_FILES['import_file']) || !is_array($_FILES['import_file'])) {
                    throw new RuntimeException('Please choose an .xlsx or .csv file.');
                }
                $file = $_FILES['import_file'];
                if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    throw new RuntimeException('The file could not be uploaded. Please check the file and try again.');
                }
                if (!is_uploaded_file((string) ($file['tmp_name'] ?? ''))) {
                    throw new RuntimeException('Invalid upload source.');
                }
                $size = filesize((string) $file['tmp_name']);
                if (!$size || $size > Uploader::MAX_BYTES) {
                    throw new RuntimeException('The file is empty or exceeds the 10 MB upload limit.');
                }
                $rows = SpreadsheetReader::read((string) $file['tmp_name'], basename((string) $file['name']));
                $summary = $this->teacherService->importTeachers($rows);
                if ((int) ($summary['created'] ?? 0) > 0) {
                    Session::setFlash('success', (int) $summary['created'] . ' teacher account(s) created successfully.');
                }
                $this->view('teachers/import', ['summary' => $summary]);
            } catch (Throwable $e) {
                Session::setFlash('error', $e->getMessage());
                $this->view('teachers/import');
            }
            return;
        }
        $this->view('teachers/import');
    }

    public function template() {
        if (ob_get_length() !== false) { @ob_end_clean(); }
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="teachers_import_template.csv"');
        header('Cache-Control: no-store');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['username', 'email', 'password', 'status']);
        fputcsv($out, ['jane.smith', 'jane.smith@school.local', 'ChangeMe123!', 'active']);
        fclose($out);
        exit;
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->teacherService->createTeacher($_POST);
                Session::setFlash('success', 'Teacher account created. They can now sign in with the username and password you set.');
                $this->redirect('teacher');
            } catch (Throwable $e) {
                Session::setFlash('error', $e->getMessage());
                $this->view('teachers/create', ['data' => $_POST]);
            }
            return;
        }

        $this->view('teachers/create', [
            'data' => ['username' => '', 'email' => '', 'password' => '', 'status' => 'active']
        ]);
    }

    public function edit($id) {
        $teacher = $this->teacherService->getTeacherById($id);

        if (!$teacher || ($teacher['role_name'] ?? '') !== 'teacher') {
            Session::setFlash('error', 'Teacher not found.');
            $this->redirect('teacher');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->teacherService->updateTeacher($id, $_POST);
                Session::setFlash('success', 'Teacher updated successfully.');
                $this->redirect('teacher');
            } catch (Throwable $e) {
                Session::setFlash('error', $e->getMessage());
                $this->view('teachers/edit', ['teacher' => $teacher, 'data' => $_POST]);
            }
            return;
        }

        $this->view('teachers/edit', [
            'teacher' => $teacher,
            'data' => [
                'username' => $teacher['username'] ?? '',
                'email'    => $teacher['email'] ?? '',
                'password' => '',
                'status'   => $teacher['status'] ?? 'active'
            ]
        ]);
    }

    public function delete($id) {
        try {
            if ($this->teacherService->deleteTeacher($id)) {
                Session::setFlash('success', 'Teacher deleted successfully.');
            } else {
                Session::setFlash('error', 'Failed to delete teacher.');
            }
        } catch (Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect('teacher');
    }
}
