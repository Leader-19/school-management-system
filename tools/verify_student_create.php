<?php
/**
 * Standalone check for the student creation path and the mass-assignment
 * guard. Run:  php tools/verify_student_create.php
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
        $this->pdo->exec('CREATE TABLE roles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT UNIQUE NOT NULL
        )');
        $this->pdo->exec("INSERT INTO roles (name) VALUES ('admin'), ('student')");
        $this->pdo->exec('CREATE TABLE students (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            student_code TEXT UNIQUE NOT NULL,
            first_name TEXT NOT NULL,
            last_name TEXT NOT NULL,
            date_of_birth TEXT,
            gender TEXT CHECK (gender IN ("male","female","other") OR gender IS NULL),
            address TEXT,
            phone TEXT,
            class_id INTEGER NULL
        )');
        $this->pdo->exec('CREATE TABLE classes (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, teacher_id INTEGER NULL)');
    }

    public function getConnection() {
        return $this->pdo;
    }
}

$db = Database::getInstance()->getConnection();
$service = new StudentService();
$pass = 0;
$fail = 0;

function check($label, $actual, $expected = true) {
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

// Exactly what Views/students/create.php posts.
$form = [
    'username'      => 'john.doe',
    'email'         => 'john@school.local',
    'password'      => '12345678',
    'student_code'  => 'STU001',
    'first_name'    => 'John',
    'last_name'     => 'Doe',
    'date_of_birth' => '2005-05-15',
    'gender'        => 'Male',
    'address'       => '123 School Lane',
    'phone'         => '555-1234',
    'class_id'      => '',
];

echo "\n1. Create a student exactly as the form posts it\n";
$studentId = $service->createStudent($form);
check('student created', !empty($studentId));

$user = $db->prepare("SELECT * FROM users WHERE username = 'john.doe'");
$user->execute();
$record = $user->fetch();

check('login account created', !empty($record));
check('role is student (3)', (int) $record['role_id'], 3);
check('account is active', $record['status'] === 'active');

echo "\n2. The created student can actually sign in (the reported bug)\n";
check('password is a hash', password_get_info($record['password'])['algo'] !== null);
check('NOT double hashed', substr_count($record['password'], '$2y$') === 1);
check('password_verify matches', password_verify('12345678', $record['password']));
check('UserRepository authenticates', (new UserRepository())->authenticate('john.doe', '12345678') !== false);

echo "\n3. Form field mapping\n";
$student = $db->prepare("SELECT * FROM students WHERE id = ?");
$student->execute([$studentId]);
$row = $student->fetch();

check('date_of_birth saved (form used to post "dob")', $row['date_of_birth'] === '2005-05-15');
check('gender lower-cased for the ENUM', $row['gender'] === 'male');
check('class_id stored as NULL, not ""', $row['class_id'] === null);
check('user_id linked', (int) $row['user_id'] === (int) $record['id']);

echo "\n4. Duplicate protection\n";
try {
    $service->createStudent($form);
    check('duplicate student_code rejected', false);
} catch (RuntimeException $e) {
    check('duplicate rejected with a clear message', strpos($e->getMessage(), 'already') !== false);
}

$second = $form;
$second['student_code'] = 'STU002';
$second['email'] = $form['email'];
try {
    $service->createStudent($second);
    check('duplicate email rejected', false);
} catch (RuntimeException $e) {
    check('duplicate email rejected with a clear message', strpos($e->getMessage(), 'already') !== false);
}

echo "\n5. Validation\n";
check('missing username rejected', $service->validate(['email' => 'a@b.c', 'password' => '123456']) !== null);
check('bad email rejected', $service->validate(['username' => 'x', 'email' => 'nope']) !== null);
check('short password rejected', $service->validate(['username' => 'x', 'email' => 'a@b.c', 'password' => '123']) !== null);
check('valid input accepted', $service->validate($form) === null);

echo "\n6. Update keeps the username/status working\n";
$service->updateStudent($studentId, [
    'username' => 'john.doe2', 'email' => 'john2@school.local', 'password' => '',
    'student_code' => 'STU001', 'first_name' => 'Johnny', 'last_name' => 'Doe',
    'date_of_birth' => '2005-05-15', 'gender' => 'Female', 'status' => 'inactive'
]);

$db->prepare("SELECT * FROM users WHERE id = ?")->execute([$record['id']]);
$updated = $db->query("SELECT * FROM users WHERE id = " . (int) $record['id'])->fetch();

check('username was updated', $updated['username'] === 'john.doe2');
check('status was updated', $updated['status'] === 'inactive');
check('empty password left the hash intact', password_verify('12345678', $updated['password']));

echo "\n7. Mass-assignment guard (SQL injection via POST keys)\n";
$model = new Student();
$injected = $model->create([
    'first_name' => 'Test',
    'last_name' => 'User',
    'student_code' => 'STU999',
    'user_id' => 1,
    'id) VALUES (999, 1, "HACK", "a", "b"); DROP TABLE students; --' => 'x',
]);
check('unknown keys are dropped, insert still succeeds', !empty($injected));
$count = (int) $db->query('SELECT COUNT(*) FROM students')->fetchColumn();
check('no injected rows appeared (still only the 2 real students)', $count, 2);

echo "\n----------------------------------------\n";
echo "{$pass} passed, {$fail} failed\n\n";
exit($fail === 0 ? 0 : 1);