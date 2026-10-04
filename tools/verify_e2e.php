<?php
/**
 * End-to-end check against the real MySQL database.
 * Run:  php tools/verify_e2e.php [base-url]
 *
 * Exercises the actual login flow, permission middleware and the classes
 * CRUD through HTTP with a real session cookie.
 */

require_once __DIR__ . '/bootstrap.php';

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8766', '/');
$adminUser = 'admin';
$adminPass = '12345678';
$jar = tempnam(sys_get_temp_dir(), 'smsjar');

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

function http($path, $method = 'GET', $post = null, $jar = null) {
    global $base;

    $headers = [];
    if ($post !== null) {
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    }
    if ($jar && file_exists($jar) && filesize($jar) > 0) {
        $cookie = trim(file_get_contents($jar));
        if ($cookie !== '') {
            $headers[] = 'Cookie: ' . $cookie;
        }
    }

    $options = ['http' => [
        'method'          => $method,
        'ignore_errors'   => true,
        'follow_location' => 0,
        'timeout'         => 20,
        'header'          => $headers,
    ]];

    if ($post !== null) {
        $options['http']['content'] = http_build_query($post);
    }

    $body = @file_get_contents($base . $path, false, stream_context_create($options));

    $status = 0;
    $location = '';
    foreach ($http_response_header ?? [] as $h) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $h, $m)) {
            $status = (int) $m[1];
            $location = '';
            continue;
        }
        if (stripos($h, 'Location:') === 0) {
            $location = trim(substr($h, 9));
        }
    }

    return ['status' => $status, 'body' => $body === false ? '' : $body, 'location' => $location];
}

function csrfFrom($html) {
    return preg_match('/name="_token" value="([a-f0-9]{64})"/', $html, $m) ? $m[1] : '';
}

echo "\nEnd-to-end against {$base}\n";
echo str_repeat('-', 62) . "\n";

echo "\n1. Sign in with the seeded admin account\n";
$loginPage = http('/login');
check('login page renders', $loginPage['status'], 200);

$token = csrfFrom($loginPage['body']);
check('CSRF token issued', strlen($token), 64);

$bad = http('/login', 'POST', ['login' => $adminUser, 'password' => 'wrong-password', '_token' => $token], $jar);
check('wrong password is refused', $bad['status'], 302);
check('...and stays on the login page', strpos((string) $bad['location'], 'login') !== false, true);

$login = http('/login', 'POST', ['login' => $adminUser, 'password' => $adminPass, '_token' => $token], $jar);
check('correct password redirects', $login['status'], 302);
check('...to the dashboard', strpos((string) $login['location'], 'dashboard') !== false, true);

echo "\n2. Authenticated pages render\n";
$dash = http('/dashboard', 'GET', null, $jar);
check('dashboard renders', $dash['status'], 200);
check('sidebar shows the admin role', strpos($dash['body'], 'Admin') !== false, true);
check('sidebar links to Classes', strpos($dash['body'], '/class') !== false, true);

$students = http('/student', 'GET', null, $jar);
check('student list renders', $students['status'], 200);
check('email column header present', strpos($students['body'], 'Email') !== false, true);

echo "\n3. Create a class through the real form\n";
$classPage = http('/class/create', 'GET', null, $jar);
check('new class page renders', $classPage['status'], 200);
$token = csrfFrom($classPage['body']);
check('CSRF token present on the class form', strlen($token), 64);

$suffix = strtolower(bin2hex(random_bytes(3)));
$className = 'E2E Class ' . $suffix;

$created = http('/class/create', 'POST', [
    'name'        => $className,
    'description' => 'Created by verify_e2e.php',
    'teacher_id'  => '',
    '_token'      => $token,
], $jar);
check('class created (redirected)', $created['status'], 302);

$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->prepare('SELECT id, name, description FROM classes WHERE name = ?');
$stmt->execute([$className]);
$class = $stmt->fetch();
check('class row exists in the database', (bool) $class, true);
check('name stored correctly', ($class['name'] ?? '') === $className, true);

$classId = (int) ($class['id'] ?? 0);

echo "\n4. Duplicate class names are refused\n";
$token = csrfFrom(http('/class/create', 'GET', null, $jar)['body']);
$dupe = http('/class/create', 'POST', [
    'name' => $className, 'description' => 'dupe', '_token' => $token,
], $jar);
check('duplicate rejected with an error flash', strpos($dupe['body'] . http('/class', 'GET', null, $jar)['body'], 'already exists') !== false, true);

echo "\n5. CSRF is enforced on the real form\n";
$noToken = http('/class/create', 'POST', [
    'name' => 'No Token ' . $suffix, 'description' => 'x',
], $jar);
check('POST without a token does not create', $noToken['status'], 302);
$stmt = $pdo->prepare('SELECT COUNT(*) FROM classes WHERE name = ?');
$stmt->execute(['No Token ' . $suffix]);
check('...confirmed: no row was inserted', (int) $stmt->fetchColumn(), 0);

echo "\n6. The class page shows the new record\n";
$list = http('/class', 'GET', null, $jar);
check('new class appears in the list', strpos($list['body'], $className) !== false, true);

$show = http('/class/show/' . $classId, 'GET', null, $jar);
check('class detail page renders', $show['status'], 200);
check('detail page shows the name', strpos($show['body'], $className) !== false, true);

echo "\n7. Clean up\n";
$token = csrfFrom($list['body']);
$deleted = http('/class/delete/' . $classId, 'POST', ['_token' => $token], $jar);
check('class deleted (redirected)', $deleted['status'], 302);
$stmt = $pdo->prepare('SELECT COUNT(*) FROM classes WHERE id = ?');
$stmt->execute([$classId]);
check('row is gone from the database', (int) $stmt->fetchColumn(), 0);

@unlink($jar);

echo "\n" . str_repeat('-', 62) . "\n";
echo "{$pass} passed, {$fail} failed\n\n";
exit($fail === 0 ? 0 : 1);