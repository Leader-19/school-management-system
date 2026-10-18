<?php

class DashboardController extends Controller {
    public function index() {
        if (Auth::hasRole('admin')) {
            $studentModel = new Student();
            $userModel = new User();
            $classModel = new ClassRoom();
            $assignmentModel = new Assignment();

            $db = Database::getInstance()->getConnection();

            // ទាញចំនួនសិស្សប្រុស និងស្រីពី Database
            $totalMaleStudents = (int) $db->query(
                "SELECT COUNT(*) FROM students WHERE LOWER(gender) IN ('male', 'm', 'ប្រុស')"
            )->fetchColumn();

            $totalFemaleStudents = (int) $db->query(
                "SELECT COUNT(*) FROM students WHERE LOWER(gender) IN ('female', 'f', 'ស្រី')"
            )->fetchColumn();

            // ទាញទិន្នន័យចំនួនសិស្សចុះឈ្មោះតាមខែ (Jan - Dec)
            $currentYear = date('Y');
            $monthlyEnrollments = [];
            $monthlyAttendance = []; // Array សម្រាប់ភាគរយវត្តមានសិស្ស

            for ($month = 1; $month <= 12; $month++) {
                // ១. ចំនួនសិស្សសរុបតាមខែ
                $stmt = $db->prepare("
                    SELECT COUNT(*) FROM students
                    WHERE YEAR(created_at) <= :year AND MONTH(created_at) <= :month
                ");
                $stmt->execute(['year' => $currentYear, 'month' => $month]);
                $monthlyEnrollments[] = (int) $stmt->fetchColumn();

                // ២. ភាគរយវត្តមានប្រចាំខែ (អ្នកអាចកែសម្រួល Query នេះតាមតារាង attendance របស់អ្នក)
                // ទីនេះយើងដាក់ទិន្នន័យគំរូ ឬទាញពី Database បើមានតារាង attendance
                $monthlyAttendance[] = rand(88, 98); // ភាគរយវត្តមានចន្លោះ 88% ដល់ 98%
            }

            $data = [
                'totalStudents'       => $studentModel->countAll(),
                'totalTeachers'       => $userModel->countByRole(2),
                'totalClasses'        => $classModel->countAll(),
                'totalAssignments'    => $assignmentModel->countAll(),
                'totalMaleStudents'   => $totalMaleStudents,
                'totalFemaleStudents' => $totalFemaleStudents,
                'monthlyEnrollments'  => $monthlyEnrollments,
                'monthlyAttendance'   => $monthlyAttendance // បញ្ជូនទិន្នន័យវត្តមានទៅ View
            ];
            $this->view('dashboard/admin', $data);

        } elseif (Auth::hasRole('teacher')) {
            // ... (រក្សាទុកដដែល)
        } elseif (Auth::hasRole('student')) {
            // ... (រក្សាទុកដដែល)
        } else {
            http_response_code(403);
            $this->view('errors/403');
        }
    }
}