<?php
class DashboardController extends Controller {
    public function index() {
        if (Auth::hasRole('admin')) {
            $studentModel = new Student();
            $userModel = new User();
            $classModel = new ClassRoom();
            $assignmentModel = new Assignment();

            $data = [
                'totalStudents' => $studentModel->countAll(),
                'totalTeachers' => $userModel->countByRole(2),
                'totalClasses' => $classModel->countAll(),
                'totalAssignments' => $assignmentModel->countAll()
            ];
            $this->view('dashboard/admin', $data);

        } elseif (Auth::hasRole('teacher')) {
            $assignmentService = new AssignmentService();
            $classModel = new ClassRoom();
            $teacherId = Auth::userId();
            $assignments = $assignmentService->getAssignmentsByTeacher($teacherId);

            $data = [
                'myClassesCount' => 0,
                'myAssignmentsCount' => count($assignments),
                'pendingSubmissions' => 0,
                'recentAssignments' => $assignments
            ];
            $this->view('dashboard/teacher', $data);

        } elseif (Auth::hasRole('student')) {
            $assignmentService = new AssignmentService();
            $gradeService = new GradeService();
            $studentService = new StudentService();

            $userId = Auth::userId();
            $student = $studentService->getStudentByUserId($userId);

            if ($student) {
                $assignments = $assignmentService->getAssignmentsForStudent($student['id']);
                $grades = $gradeService->getStudentGrades($student['id']);
                $data = [
                    'studentName' => $student['first_name'] . ' ' . $student['last_name'],
                    'className' => $student['class_name'] ?? 'Unassigned',
                    'pendingAssignments' => count($assignments),
                    'averageGrade' => $gradeService->getStudentAverage($student['id']),
                    'upcomingAssignments' => $assignments,
                    'recentGrades' => $grades
                ];
            } else {
                $data = [
                    'studentName' => Auth::user()['username'],
                    'className' => 'Unassigned',
                    'pendingAssignments' => 0,
                    'averageGrade' => 0,
                    'upcomingAssignments' => [],
                    'recentGrades' => []
                ];
            }
            $this->view('dashboard/student', $data);
        } else {
            // Logged in but the role is not recognized. Render a 403 page
            // instead of redirecting back to /auth - redirecting here would
            // create an infinite /auth <-> /dashboard redirect loop.
            http_response_code(403);
            $this->view('errors/403');
        }
    }
}
