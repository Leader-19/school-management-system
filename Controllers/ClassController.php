<?php

class ClassController extends Controller {
    private $classService;

    public function __construct() {
        $this->classService = new ClassRoomService();
    }

    public function index() {
        $keyword = trim((string) ($_GET['q'] ?? ''));
        $perPage = (int) ($_GET['per_page'] ?? 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100], true)) {
            $perPage = 15;
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $result = $this->classService->paginate($page, $perPage, $keyword !== '' ? $keyword : null);

        $totalPages = max(1, (int) ceil($result['total'] / $result['per_page']));
        if ($result['page'] > $totalPages) {
            $result = $this->classService->paginate(
                $totalPages,
                $result['per_page'],
                $keyword !== '' ? $keyword : null
            );
        }

        $this->view('classes/index', [
            'classes'  => $result['rows'],
            'keyword'  => $keyword,
            'canWrite' => Auth::hasPermission('manage_classes'),
            'pagination' => [
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
                $this->classService->create($_POST);
                Session::setFlash('success', 'Class created successfully.');
                $this->redirect('class');
            } catch (Throwable $e) {
                Session::setFlash('error', $e->getMessage());
                $this->view('classes/create', [
                    'teachers' => $this->classService->getTeachers(),
                    'data'     => $_POST
                ]);
            }
            return;
        }

        $this->view('classes/create', [
            'teachers' => $this->classService->getTeachers(),
            'data'     => ['name' => '', 'description' => '', 'teacher_id' => '']
        ]);
    }

    public function edit($id) {
        $class = $this->classService->getById($id);

        if (!$class) {
            Session::setFlash('error', 'Class not found.');
            $this->redirect('class');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->classService->update($id, $_POST);
                Session::setFlash('success', 'Class updated successfully.');
                $this->redirect('class');
            } catch (Throwable $e) {
                Session::setFlash('error', $e->getMessage());
                $this->view('classes/edit', [
                    'class'    => $class,
                    'teachers' => $this->classService->getTeachers(),
                    'data'     => $_POST
                ]);
            }
            return;
        }

        $this->view('classes/edit', [
            'class'    => $class,
            'teachers' => $this->classService->getTeachers(),
            'data'     => [
                'name'        => $class['name'] ?? '',
                'description' => $class['description'] ?? '',
                'teacher_id'  => $class['teacher_id'] ?? ''
            ]
        ]);
    }

    public function delete($id) {
        try {
            if ($this->classService->delete($id)) {
                Session::setFlash('success', 'Class deleted successfully.');
            } else {
                Session::setFlash('error', 'Failed to delete class.');
            }
        } catch (Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect('class');
    }

    public function show($id) {
        $class = $this->classService->getById($id);

        if (!$class) {
            Session::setFlash('error', 'Class not found.');
            $this->redirect('class');
        }

        // Viewing a class is allowed for staff; students may only open the
        // class they belong to.
        if (!Auth::hasPermission('manage_classes')) {
            $studentService = new StudentService();
            $student = $studentService->getStudentByUserId(Auth::userId());

            if (!$student || (int) $student['class_id'] !== (int) $id) {
                $this->checkPermission('manage_students');
            }
        }

        $this->view('classes/show', [
            'class'        => $class,
            'students'     => $this->classService->getStudents($id),
            'subjects'     => $this->classService->getSubjects($id),
            'canWrite'     => Auth::hasPermission('manage_classes')
        ]);
    }
}