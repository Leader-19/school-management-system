<?php
/**
 * Checks for teacher management, attendance, and the new paginated lists.
 * Run:  php tools/verify_teacher_attendance.php
 *
 * Uses a temporary SQLite database, so no MySQL server is needed.
 */

define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', '');

spl_autoload_register(function ($className) {
    foreach (['Core/', 'Controllers/', 'Models/', 'Repository/', 'Services/', 'config/'] as $path) {
        $file = BASE_PATH . '/' . $path . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

class Database {
    private static $instance;
    public $pdo;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $this->pdo->exec('CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role_id INTEGER NOT NULL,
            status TEXT DEFAULT "active"
        )');
        $this->pdo->exec('CREATE TABLE roles (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT UNIQUE NOT NULL)');
        $this->pdo->exec("INSERT INTO roles (name) VALUES ('admin'), ('teacher'), ('student')");
        $this->pdo->exec('CREATE TABLE students (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            student_code TEXT UNIQUE NOT NULL,
            first_name TEXT NOT NULL,
            last_name TEXT NOT NULL,
            date_of_birth TEXT,
            gender TEXT,
            address TEXT,
            phone TEXT,
            class_id INTEGER NULL
        )');
        $this->pdo->exec('CREATE TABLE classes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            description TEXT,
            teacher_id INTEGER NULL
        )');
        $this->pdo->exec('CREATE TABLE subjects (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, code TEXT)');
        $this->pdo->exec('CREATE TABLE grades (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            student_id INTEGER NOT NULL,
            subject_id INTEGER NOT NULL,
            score REAL NOT NULL,
            semester TEXT NOT NULL,
            academic_year TEXT NOT NULL
        )');
        $this->pdo->exec('CREATE TABLE attendance_sessions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            class_id INTEGER NOT NULL,
            subject_id INTEGER NULL,
            session_date TEXT NOT NULL,
            session_type TEXT NOT NULL,
            notes TEXT,
            created_by INTEGER NULL,
            UNIQUE (class_id, session_date, session_type)
        )');
        $this->pdo->exec('CREATE TABLE student_attendance (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            session_id INTEGER NOT NULL,
            student_id INTEGER NOT NULL,
            status TEXT NOT NULL,
            note TEXT,
            marked_at TEXT DEFAULT CURRENT_TIMESTAMP,
            UNIQUE (session_id, student_id)
        )');
    }

    public function getConnection() {
        return $this->pdo;
    }
}

$db = Database::getInstance()->getConnection();
$pass = 0;
$fail = 0;

function check($label, $actual, $expected = null) {
    global $pass, $fail;
    $result = func_num_args() > 2 ? ($actual === $expected) : (bool) $actual;
    if ($result) {
        $pass++;
        echo "  PASS  {$label}\n";
    } else {
        $fail++;
        echo "  FAIL  {$label} (got " . var_export($actual, true) . ")\n";
    }
}

// Seed: admin + 2 students per class x3 classes, subjects, grades
$db->exec("INSERT INTO users (username, email, password, role_id) VALUES
    ('admin', 'admin@school.local', 'hash', 1),
    ('teacher.one', 'teacher.one@school.local', 'hash', 2)");
$teacherId = (int) $db->lastInsertId();
$db->exec("INSERT INTO classes (name, teacher_id) VALUES ('Grade 7-A', {$teacherId}), ('Grade 7-B', NULL), ('Grade 8-A', NULL)");

$subjectIds = [];
foreach ([['Mathematics', 'MATH'], ['Physics', 'PHY']] as $i => $s) {
    $db->exec("INSERT INTO subjects (name, code) VALUES ('{$s[0]}', '{$s[1]}')");
    $subjectIds[] = (int) $db->lastInsertId();
}

$classStudents = [1 => [], 2 => [], 3 => []];
for ($i = 1; $i <= 6; $i++) {
    $classId = ($i <= 3) ? 1 : 2;
    $db->exec("INSERT INTO users (username, email, password, role_id)
               VALUES ('st{$i}', 'st{$i}@school.local', 'hash', 3)");
    $uid = (int) $db->lastInsertId();
    $db->exec("INSERT INTO students (user_id, student_code, first_name, last_name, class_id)
               VALUES ({$uid}, 'S{$i}', 'First{$i}', 'Last{$i}', {$classId})");
    $classStudents[$classId][] = (int) $db->lastInsertId();
}

// ---------------------------------------------------------------------
echo "\n1. Teacher management\n";
$teacherService = new TeacherService();

$newId = $teacherService->createTeacher([
    'username' => 'jane.doe', 'email' => 'jane@school.local',
    'password' => 'secret1', 'status' => 'active'
]);
check('teacher created', !empty($newId));

$row = $db->query("SELECT u.username, r.name AS role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = {$newId}")->fetch();
check('created with teacher role', $row['role'], 'teacher');
check('password hashed', password_verify('secret1', $db->query("SELECT password FROM users WHERE id = {$newId}")->fetch()['password']));

try {
    $teacherService->createTeacher(['username' => 'jane.doe', 'email' => 'other@school.local', 'password' => 'secret1']);
    check('duplicate username rejected', false);
} catch (RuntimeException $e) {
    check('duplicate username rejected', strpos($e->getMessage(), 'already') !== false);
}

try {
    $teacherService->createTeacher(['username' => 'x', 'email' => 'bad', 'password' => '123']);
    check('validation rejects bad input', false);
} catch (RuntimeException $e) {
    check('validation rejects bad input', strpos($e->getMessage(), 'Username') !== false);
}

$teacherService->updateTeacher($newId, ['username' => 'jane.d', 'email' => 'jane2@school.local', 'password' => '', 'status' => 'inactive']);
$updated = $db->query("SELECT username, email, status, password FROM users WHERE id = {$newId}")->fetch();
check('update keeps password when empty', password_verify('secret1', $updated['password']));
check('update applies status', $updated['status'], 'inactive');
check('update applies username', $updated['username'], 'jane.d');

check('teacher deleted', $teacherService->deleteTeacher($newId));
check('row gone', (int) $db->query("SELECT COUNT(*) FROM users WHERE id = {$newId}")->fetchColumn(), 0);

try {
    $teacherService->deleteTeacher(999);
    check('unknown teacher refused', false);
} catch (RuntimeException $e) {
    check('unknown teacher refused', strpos($e->getMessage(), 'not found') !== false);
}

try {
    $teacherService->deleteTeacher(1); // admin
    check('non-teacher account refused', false);
} catch (RuntimeException $e) {
    check('non-teacher account refused', strpos($e->getMessage(), 'not found') !== false);
}

// ---------------------------------------------------------------------
echo "\n2. Teacher pagination + search\n";
$teacherService->createTeacher(['username' => 'adam.smith', 'email' => 'adam@school.local', 'password' => 'secret1']);
$teacherService->createTeacher(['username' => 'bella.jones', 'email' => 'bella@school.local', 'password' => 'secret1']);

$paged = $teacherService->paginateTeachers(1, 2);
check('page size respected', count($paged['rows']), 2);
check('total counts all teachers', $paged['total'], 3);
check('class_count present', isset($paged['rows'][0]['class_count']));

$paged = $teacherService->paginateTeachers(1, 10, 'bella');
check('keyword search finds bella', count($paged['rows']), 1);
check('search result is bella', $paged['rows'][0]['username'], 'bella.jones');

// ---------------------------------------------------------------------
echo "\n3. Attendance: create session + save marks\n";
$attendanceService = new AttendanceService();

$sessionId = $attendanceService->saveSession(1, [
    'session_date' => '2026-10-01',
    'session_type' => 'full_day',
    'attendance'   => [
        $classStudents[1][0] => 'present',
        $classStudents[1][1] => 'absent',
        $classStudents[1][2] => 'late',
    ]
]);
check('session saved', !empty($sessionId));

$count = (int) $db->query('SELECT COUNT(*) FROM student_attendance WHERE session_id = ' . $sessionId)->fetchColumn();
check('three marks stored', $count, 3);

// Same session again -> updates, never duplicates
$attendanceService->saveSession(1, [
    'session_date' => '2026-10-01',
    'session_type' => 'full_day',
    'attendance'   => [
        $classStudents[1][0] => 'late',
        $classStudents[1][1] => 'present',
    ]
]);
$sessions = (int) $db->query("SELECT COUNT(*) FROM attendance_sessions WHERE class_id = 1 AND session_date = '2026-10-01' AND session_type = 'full_day'")->fetchColumn();
check('re-saving does not duplicate the session', $sessions, 1);
$count = (int) $db->query('SELECT COUNT(*) FROM student_attendance WHERE session_id = ' . $sessionId)->fetchColumn();
check('marks updated in place (no dup rows)', $count, 3);
$statuses = $db->query("SELECT student_id, status FROM student_attendance WHERE session_id = {$sessionId}")->fetchAll();
$byStudent = [];
foreach ($statuses as $s) { $byStudent[(int) $s['student_id']] = $s['status']; }
check('updated mark 1 = late', $byStudent[$classStudents[1][0]], 'late');
check('updated mark 2 = present', $byStudent[$classStudents[1][1]], 'present');
check('untouched mark 3 kept = late', $byStudent[$classStudents[1][2]], 'late');

try {
    $attendanceService->saveSession(1, ['session_date' => '2026-10-02', 'attendance' => []]);
    check('empty marks refused', false);
} catch (RuntimeException $e) {
    check('empty marks refused', strpos($e->getMessage(), 'Mark at least one') !== false);
}

try {
    $attendanceService->saveSession(1, ['session_date' => 'not-a-date', 'attendance' => [$classStudents[1][0] => 'present']]);
    check('bad date refused', false);
} catch (RuntimeException $e) {
    check('bad date refused', strpos($e->getMessage(), 'valid session date') !== false);
}

$customId = $attendanceService->saveSession(1, [
    'session_date' => '2026-10-03', 'session_type' => 'custom',
    'session_type_custom' => 'Week 3 Lab', 'attendance' => [$classStudents[1][0] => 'excused']
]);
$type = $db->query("SELECT session_type FROM attendance_sessions WHERE id = {$customId}")->fetch()['session_type'];
check('custom session type saved', $type, 'Week 3 Lab');

try {
    $attendanceService->saveSession(1, ['session_date' => '2026-10-04', 'session_type' => 'custom', 'session_type_custom' => '', 'attendance' => [$classStudents[1][0] => 'present']]);
    check('blank custom name refused', false);
} catch (RuntimeException $e) {
    check('blank custom name refused', strpos($e->getMessage(), 'custom session') !== false);
}

// ---------------------------------------------------------------------
echo "\n4. Attendance: marks pre-fill + student history\n";
$marks = $attendanceService->getMarks($sessionId);
check('getMarks returns keyed statuses', $marks[$classStudents[1][0]], 'late');

$history = $attendanceService->getStudentAttendance($classStudents[1][0]);
check('history includes sessions', count($history['history']) >= 2);
check('summary counts late', $history['summary']['late'] >= 1);
check('summary counts excused', $history['summary']['excused'] >= 1);

$sessionPage = $attendanceService->paginateSessions(1, 1, 10);
check('session list for class', $sessionPage['total'], 3);
check('session rows include marked_count', isset($sessionPage['rows'][0]['marked_count']));

// ---------------------------------------------------------------------
echo "\n5. Pagination: grades, classes, assignments queries\n";
$gradeModel = new Grade();
for ($i = 0; $i < 5; $i++) {
    $db->exec("INSERT INTO grades (student_id, subject_id, score, semester, academic_year)
               VALUES ({$classStudents[1][0]}, {$subjectIds[0]}, " . (60 + $i * 5) . ", '1', '2026')");
}
$paged = $gradeModel->paginateWithDetails(1, 3);
check('grades page size', count($paged['rows']), 3);
check('grades total', $paged['total'], 5);
$paged = $gradeModel->paginateWithDetails(1, 10, 'First1');
check('grades search by student name', $paged['total'], 5);
$paged = $gradeModel->paginateWithDetails(1, 10, 'Mathematics');
check('grades search by subject', $paged['total'], 5);

$classModel = new ClassRoom();
$paged = $classModel->paginateWithTeacher(1, 2);
check('classes page size', count($paged['rows']), 2);
check('classes total', $paged['total'], 3);
check('classes include student_count', isset($paged['rows'][0]['student_count']));
$paged = $classModel->paginateWithTeacher(1, 10, 'Grade 7');
check('class search by name', $paged['total'], 2);

// ---------------------------------------------------------------------
echo "\n6. Attendance and grades models work through services\n";
$gs = new GradeService();
$avg = $gs->paginateGrades(2, 3);
check('grade service page 2 returns remaining rows', count($avg['rows']), 2);

// ---------------------------------------------------------------------
echo "\n----------------------------------------\n";
echo "{$pass} passed, {$fail} failed\n\n";
exit($fail === 0 ? 0 : 1);
