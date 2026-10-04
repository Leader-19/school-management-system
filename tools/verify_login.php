<?php
/**
 * Standalone check for the login fixes. Run:  php tools/verify_login.php
 * It exercises UserRepository::authenticate() against a temporary SQLite
 * database, so it needs no MySQL server.
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

/**
 * Minimal stand-in for config/Database.php that talks to SQLite in memory.
 */
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
    }

    public function getConnection() {
        return $this->pdo;
    }
}

$db = Database::getInstance()->getConnection();
$repo = new UserRepository();
$pass = 0;
$fail = 0;

function check($label, $actual, $expected = true) {
    global $pass, $fail;
    if ($actual === $expected) {
        $pass++;
        echo "  PASS  {$label}\n";
    } else {
        $fail++;
        echo "  FAIL  {$label} (got " . var_export($actual, true) . ")\n";
    }
}

echo "\n1. Plain-text password row (the manual INSERT case)\n";
$db->prepare("INSERT INTO users (username, email, password, role_id) VALUES ('leader', 'leader@school.local', '123456789', 1)")->execute();

$user = $repo->authenticate('leader', '123456789');
check('leader can sign in with the plain-text password', $user !== false);
check('...and the correct record was returned', ($user['username'] ?? null) === 'leader');

$stored = $db->query("SELECT password FROM users WHERE username = 'leader'")->fetchColumn();
check('password was transparently upgraded to a hash', password_get_info($stored)['algo'] !== null);
check('upgraded hash still verifies', password_verify('123456789', $stored));

echo "\n2. Wrong password is still rejected\n";
check('wrong password refused', $repo->authenticate('leader', 'wrongpass') === false);

echo "\n3. Login by email address\n";
check('email login works', $repo->authenticate('leader@school.local', '123456789') !== false);

echo "\n4. Hash written the normal way (no double hashing)\n";
$repo->create(['username' => 'alice', 'email' => 'alice@school.local', 'password' => 'Secret123', 'role_id' => 2]);
$hash = $db->query("SELECT password FROM users WHERE username = 'alice'")->fetchColumn();
check('exactly one bcrypt layer', password_verify('Secret123', $hash));
check('not a double hash', substr_count($hash, '$2y$') === 1);
check('alice can sign in', $repo->authenticate('alice', 'Secret123') !== false);

echo "\n5. Duplicate detection\n";
check('duplicate username detected', $repo->usernameExists('alice') === true);
check('duplicate email detected', $repo->emailExists('alice@school.local') === true);
check('existing id is excluded correctly', $repo->usernameExists('alice', 99) === true);

echo "\n6. Role resolution\n";
$withRole = $repo->findWithRole($user['id']);
check('role_name resolved', ($withRole['role_name'] ?? null) === 'admin');

echo "\n7. Unknown account\n";
check('unknown login refused', $repo->authenticate('ghost', 'whatever') === false);
check('empty login refused', $repo->authenticate('', '') === false);

echo "\n----------------------------------------\n";
echo "{$pass} passed, {$fail} failed\n\n";
exit($fail === 0 ? 0 : 1);