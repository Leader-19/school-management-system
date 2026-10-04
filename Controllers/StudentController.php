<?php

class StudentController extends Controller {
    private $studentService;

    /**
     * Access rules (signed in + manage_students) are declared on the routes
     * in Routers/index.php via AuthMiddleware and PermissionMiddleware, so
     * they are not repeated here.
     */
    public function __construct() {
        $this->studentService = new StudentService();
    }

    /** Page sizes offered for the student list. */
    private const PER_PAGE_OPTIONS = [10, 15, 25, 50, 100];

    public function index() {
        $keyword  = trim((string) ($_GET['q'] ?? ''));
        $perPage  = (int) ($_GET['per_page'] ?? 15);
        if (!in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 15;
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $result = $this->studentService->paginateStudents(
            $page,
            $perPage,
            $keyword !== '' ? $keyword : null
        );

        // A bookmarked page beyond the last one falls back to the last page.
        $totalPages = max(1, (int) ceil($result['total'] / $result['per_page']));
        if ($result['page'] > $totalPages) {
            $result = $this->studentService->paginateStudents(
                $totalPages,
                $result['per_page'],
                $keyword !== '' ? $keyword : null
            );
        }

        $pagination = [
            'page'        => $result['page'],
            'per_page'    => $result['per_page'],
            'total'       => $result['total'],
            'total_pages' => $totalPages,
            'offset'      => ($result['page'] - 1) * $result['per_page']
        ];

        $this->view('students/index', [
            'students'   => $result['rows'],
            'keyword'    => $keyword,
            'pagination' => $pagination
        ]);
    }

    public function create() {
        $formData = [
            'username' => '', 'email' => '', 'password' => '',
            'student_code' => '', 'first_name' => '', 'last_name' => '',
            'date_of_birth' => '', 'gender' => '', 'address' => '',
            'phone' => '', 'class_id' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = array_merge($formData, $this->collectForm());

            $error = $this->studentService->validate($_POST);

            if ($error !== null) {
                Session::setFlash('error', $error);
                $this->view('students/create', ['classes' => $this->loadClasses(), 'data' => $formData]);
                return;
            }

            try {
                $this->studentService->createStudent($_POST);
                Session::setFlash('success', 'Student created successfully. They can now sign in with the username and password you set.');
                $this->redirect('student');
            } catch (Throwable $e) {
                Session::setFlash('error', $e->getMessage());
                $this->view('students/create', ['classes' => $this->loadClasses(), 'data' => $formData]);
            }
            return;
        }

        $this->view('students/create', ['classes' => $this->loadClasses(), 'data' => $formData]);
    }

    public function edit($id) {
        $student = $this->studentService->getStudentById($id);

        if (!$student) {
            Session::setFlash('error', 'Student not found.');
            $this->redirect('student');
        }

        $formData = $this->toFormData($student);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = array_merge($formData, $this->collectForm());

            $error = $this->studentService->validate($_POST, $id);

            if ($error !== null) {
                Session::setFlash('error', $error);
                $this->view('students/edit', ['student' => $student, 'classes' => $this->loadClasses(), 'data' => $formData]);
                return;
            }

            try {
                $this->studentService->updateStudent($id, $_POST);
                Session::setFlash('success', 'Student updated successfully.');
                $this->redirect('student');
            } catch (Throwable $e) {
                Session::setFlash('error', $e->getMessage());
                $this->view('students/edit', ['student' => $student, 'classes' => $this->loadClasses(), 'data' => $formData]);
            }
            return;
        }

        $this->view('students/edit', ['student' => $student, 'classes' => $this->loadClasses(), 'data' => $formData]);
    }

    public function delete($id) {
        try {
            if ($this->studentService->deleteStudent($id)) {
                Session::setFlash('success', 'Student deleted successfully.');
            } else {
                Session::setFlash('error', 'Failed to delete student.');
            }
        } catch (Throwable $e) {
            Session::setFlash('error', 'Failed to delete student: ' . $e->getMessage());
        }

        $this->redirect('student');
    }

    public function show($id) {
        $student = $this->studentService->getStudentById($id);

        if (!$student) {
            Session::setFlash('error', 'Student not found.');
            $this->redirect('student');
        }

        $this->view('students/show', ['student' => $student]);
    }

    /**
     * GET  - show the Excel import form (with a column guide and template).
     * POST - read the uploaded spreadsheet, create the students and show a
     *        per-row result report. Rendered directly (no redirect) so the
     *        detailed report survives a page refresh is not needed - the user
     *        intentionally sees it once.
     */
    public function import() {
        $classes = $this->loadClasses();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $rows    = $this->readImportFile();
                $summary = $this->studentService->importStudents($rows);

                $this->view('students/import', [
                    'classes' => $classes,
                    'summary' => $summary
                ]);
            } catch (Throwable $e) {
                Session::setFlash('error', $e->getMessage());
                $this->view('students/import', ['classes' => $classes]);
            }
            return;
        }

        $this->view('students/import', ['classes' => $classes]);
    }

    /**
     * Download an empty import template with example rows.
     */
    public function template() {
        if (ob_get_length() !== false) {
            @ob_end_clean();
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="students_import_template.csv"');
        header('Cache-Control: no-store');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // BOM so Excel detects UTF-8.

        fputcsv($out, [
            'student_code', 'first_name', 'last_name', 'username', 'email',
            'password', 'date_of_birth', 'gender', 'class', 'phone', 'address', 'status'
        ]);
        fputcsv($out, [
            'STU001', 'Ana', 'Lee', 'ana.lee', 'ana.lee@school.local', 'Passw0rd!',
            '2010-05-14', 'female', 'Grade 10-A', '555-0101', '12 Oak Street', 'active'
        ]);
        fputcsv($out, [
            'STU002', 'Ben', 'Kim', 'ben.kim', 'ben.kim@school.local', 'Passw0rd!',
            '2010-11-02', 'male', '', '555-0102', '', 'active'
        ]);

        fclose($out);
        exit;
    }

    /**
     * Validate the uploaded spreadsheet and return its data rows.
     * The file is processed straight from PHP's upload tmp dir - imports are
     * never persisted to storage/uploads.
     */
    private function readImportFile() {
        if (!isset($_FILES['import_file']) || !is_array($_FILES['import_file'])) {
            throw new RuntimeException('Please choose an .xlsx or .csv file to upload.');
        }

        $file = $_FILES['import_file'];

        switch ($file['error'] ?? UPLOAD_ERR_NO_FILE) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('Please choose an .xlsx or .csv file to upload.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('The file is larger than the server allows.');
            case UPLOAD_ERR_PARTIAL:
                throw new RuntimeException('The file was only partially uploaded. Please try again.');
            default:
                throw new RuntimeException('The file could not be uploaded.');
        }

        if (!is_uploaded_file((string) ($file['tmp_name'] ?? ''))) {
            throw new RuntimeException('Invalid upload source.');
        }

        $size = filesize((string) $file['tmp_name']);
        if ($size === false || $size === 0) {
            throw new RuntimeException('The uploaded file is empty.');
        }
        if ($size > Uploader::MAX_BYTES) {
            throw new RuntimeException('The file is too large. Maximum size is ' . (new Uploader())->formatSize(Uploader::MAX_BYTES) . '.');
        }

        return SpreadsheetReader::read((string) $file['tmp_name'], basename((string) $file['name']));
    }

    private function loadClasses() {
        return $this->studentService->getClasses();
    }

    /**
     * Whitelist the form fields so nothing else from $_POST is forwarded.
     */
    private function collectForm() {
        $fields = [
            'username', 'email', 'password', 'student_code', 'first_name',
            'last_name', 'date_of_birth', 'gender', 'address', 'phone',
            'class_id', 'status'
        ];

        $data = [];
        foreach ($fields as $field) {
            $data[$field] = $_POST[$field] ?? '';
        }

        return $data;
    }

    /**
     * Project a database row back onto the form field names.
     */
    private function toFormData($student) {
        return [
            'username'      => $student['username'] ?? '',
            'email'         => $student['email'] ?? '',
            'password'      => '',
            'student_code'  => $student['student_code'] ?? '',
            'first_name'    => $student['first_name'] ?? '',
            'last_name'     => $student['last_name'] ?? '',
            'date_of_birth' => $student['date_of_birth'] ?? '',
            'gender'        => $student['gender'] ?? '',
            'address'       => $student['address'] ?? '',
            'phone'         => $student['phone'] ?? '',
            'class_id'      => $student['class_id'] ?? '',
            'status'        => $student['user_status'] ?? 'active'
        ];
    }
}