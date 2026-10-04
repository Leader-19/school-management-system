<?php
/**
 * Standalone check for the document upload handling. Run:
 *   php tools/verify_upload.php
 *
 * Exercises Uploader's validation, naming and path-traversal defences. Files
 * are moved with rename() instead of move_uploaded_file() because this runs
 * on the CLI (no real HTTP upload).
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
 * Test double: on the CLI is_uploaded_file() is always false and
 * move_uploaded_file() refuses to run, so both are overridden here to
 * exercise the validation rules themselves.
 */
class TestUploader extends Uploader {
    protected function isRealUpload($path) {
        return is_string($path) && is_file($path);
    }

    protected function persist($from, $to) {
        return rename($from, $to);
    }
}

$tmp = sys_get_temp_dir() . '/upload_test_' . bin2hex(random_bytes(4));
mkdir($tmp, 0755, true);

$uploader = new TestUploader($tmp);
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

echo "\n1. Accepting a valid PDF\n";
$pdf = '%PDF-1.4' . str_repeat("\n1 0 obj", 40) . '%%EOF';
$src = $tmp . '/source.pdf';
file_put_contents($src, $pdf);

$file = [
    'name' => 'Lesson Plan.pdf',
    'type' => 'application/pdf',
    'tmp_name' => $src,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($src)
];

try {
    $stored = $uploader->store($file, 'assignments');
    check('stored successfully', is_string($stored));
    check('kept the sub folder', strpos($stored, 'assignments/') === 0);
    check('file exists on disk', $uploader->exists($stored));
    check('original name preserved', $uploader->originalName($stored) === 'Lesson Plan.pdf');
    check('stored under a random name, not the original', basename($stored) !== 'Lesson Plan.pdf');
} catch (RuntimeException $e) {
    check('stored successfully: ' . $e->getMessage(), false);
}

echo "\n2. Rejected file types\n";
foreach ([
    ['evil.php', '<?php system($_GET["c"]); ?>'],
    ['shell.phtml', '<?php system($_GET["c"]); ?>'],
    ['page.html', '<script>alert(1)</script>'],
    ['noext', 'random data'],
] as [$name, $contents]) {
    $path = $tmp . '/probe_' . $name;
    file_put_contents($path, $contents);
    try {
        $uploader->store(['name' => $name, 'tmp_name' => $path, 'error' => UPLOAD_ERR_OK, 'size' => filesize($path)], 'assignments');
        check("rejected '{$name}'", false);
    } catch (RuntimeException $e) {
        check("rejected '{$name}'", true);
    }
}

echo "\n3. Extension/MIME mismatch (renamed executable)\n";
$fake = $tmp . '/notes.pdf';
file_put_contents($fake, '<?php system($_GET["c"]); ?>');
try {
    $uploader->store(['name' => 'notes.pdf', 'tmp_name' => $fake, 'error' => UPLOAD_ERR_OK, 'size' => filesize($fake)], 'assignments');
    check('rejected a PHP file disguised as .pdf', false);
} catch (RuntimeException $e) {
    check('rejected a PHP file disguised as .pdf', true);
}

echo "\n4. Path traversal on read/delete\n";
check('absolute path refused', $uploader->absolutePath('/etc/passwd') === null);
check('parent traversal refused', $uploader->absolutePath('../../../etc/passwd') === null);
check('windows drive refused', $uploader->absolutePath('C:/Windows/win.ini') === null);
check('embedded traversal refused', $uploader->absolutePath('assignments/../../secret.txt') === null);
check('empty path refused', $uploader->absolutePath('') === null);
check('subdir escape via ../.. refused', $uploader->exists('../uploads/assignments') === false);

echo "\n5. Missing file\n";
$noFile = $uploader->store(['name' => '', 'tmp_name' => '', 'error' => UPLOAD_ERR_NO_FILE, 'size' => 0], 'assignments');
check('UPLOAD_ERR_NO_FILE returns false, not an exception', $noFile, false);
check('unknown path reports as missing', $uploader->exists('assignments/does_not_exist.pdf') === false);

$emptySrc = $tmp . '/empty.txt';
file_put_contents($emptySrc, '');
try {
    $uploader->store(['name' => 'empty.txt', 'tmp_name' => $emptySrc, 'error' => UPLOAD_ERR_OK, 'size' => 0], 'assignments');
    check('empty file rejected', false);
} catch (RuntimeException $e) {
    check('empty file rejected', true);
}

try {
    $uploader->store(['name' => 'x.txt', 'tmp_name' => null, 'error' => UPLOAD_ERR_OK, 'size' => 1], 'assignments');
    check('missing tmp_name rejected', false);
} catch (RuntimeException $e) {
    check('missing tmp_name rejected', true);
}

echo "\n6. Replacing an attachment removes the old file\n";
$old = $uploader->store(['name' => 'old.txt', 'tmp_name' => (function () use ($tmp) {
    $p = $tmp . '/old_src.txt';
    file_put_contents($p, 'old');
    return $p;
})(), 'error' => UPLOAD_ERR_OK, 'size' => 3], 'assignments');
check('old file stored', $uploader->exists($old));

$new = $uploader->store(['name' => 'new.txt', 'tmp_name' => (function () use ($tmp) {
    $p = $tmp . '/new_src.txt';
    file_put_contents($p, 'new');
    return $p;
})(), 'error' => UPLOAD_ERR_OK, 'size' => 3], 'assignments', $old);
check('new file stored', $uploader->exists($new));
check('old file cleaned up', !$uploader->exists($old));

echo "\n7. delete() removes the file\n";
$uploader->delete($new);
check('file gone', !$uploader->exists($new));

echo "\n----------------------------------------\n";
echo "{$pass} passed, {$fail} failed\n";

// Cleanup
foreach (glob($tmp . '/*') as $f) { @unlink($f); }
foreach (glob($tmp . '/assignments/*') as $f) { @unlink($f); }
@rmdir($tmp . '/assignments');
@rmdir($tmp);

echo "\n";
exit($fail === 0 ? 0 : 1);