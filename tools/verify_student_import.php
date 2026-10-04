<?php
/**
 * Checks for the Excel/CSV student import and student list pagination.
 * Run:  php tools/verify_student_import.php
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
$model = new Student();
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

$db->exec("INSERT INTO classes (name) VALUES ('Grade 10-A'), ('Grade 10-B')");

// ---------------------------------------------------------------------
echo "\n1. CSV parsing (headers, aliases, BOM)\n";
$csvPath = tempnam(sys_get_temp_dir(), 'sms_csv_');
file_put_contents($csvPath, "\xEF\xBB\xBF"
    . "Student Code,First Name,Last Name,Username,Email,Password,DOB,Gender,Class,Status\n"
    . "S001,Mai,Tran,mai.tran,mai@school.local,secret1,2011-03-05,Female,Grade 10-A,active\n"
    . "S002,Tom,Nguyen,tom.nguyen,tom@school.local,secret2,2011-07-21,male,Grade 10-B,\n");

$rows = SpreadsheetReader::read($csvPath, 'students.csv');
check('two data rows parsed', count($rows), 2);
check('headers normalised (spaces -> underscores, lower case)', array_keys($rows[0]) === [
    'student_code', 'first_name', 'last_name', 'username', 'email', 'password', 'dob', 'gender', 'class', 'status'
]);
check('BOM stripped from first header', isset($rows[0]['student_code']));
check('DOB alias mapped', $rows[0]['dob'], '2011-03-05');
check('values intact', $rows[1]['first_name'], 'Tom');
unlink($csvPath);

// ---------------------------------------------------------------------
echo "\n2. XLSX parsing (shared strings, date cells)\n";
$xlsxPath = tempnam(sys_get_temp_dir(), 'sms_xlsx_') . '.xlsx';
$dobSerial = 25569 + (int) floor(mktime(0, 0, 0, 3, 5, 2011) / 86400); // 2011-03-05

$zip = new ZipArchive();
$zip->open($xlsxPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
$zip->addFromString('xl/workbook.xml',
    '<?xml version="1.0" encoding="UTF-8"?>
    <workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
              xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
        <sheets><sheet name="Students" sheetId="1" r:id="rId1"/></sheets>
    </workbook>');
$zip->addFromString('xl/_rels/workbook.xml.rels',
    '<?xml version="1.0" encoding="UTF-8"?>
    <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
        <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    </Relationships>');
$zip->addFromString('xl/sharedStrings.xml',
    '<?xml version="1.0" encoding="UTF-8"?>
    <sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="8" uniqueCount="8">
        <si><t>student_code</t></si><si><t>first_name</t></si><si><t>last_name</t></si>
        <si><t>username</t></si><si><t>email</t></si><si><t>password</t></si>
        <si><t>date_of_birth</t></si><si><t>gender</t></si><si><t>class</t></si>
        <si><t>S101</t></si><si><t>Lan</t></si><si><t>Pham</t></si>
        <si><t>lan.pham</t></si><si><t>lan@school.local</t></si><si><t>secret3</t></si>
        <si><t>female</t></si><si><t>Grade 10-A</t></si>
    </sst>');
$zip->addFromString('xl/styles.xml',
    '<?xml version="1.0" encoding="UTF-8"?>
    <styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
        <cellXfs count="2">
            <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
            <xf numFmtId="14" fontId="0" fillId="0" borderId="0" applyNumberFormat="1"/>
        </cellXfs>
    </styleSheet>');
$zip->addFromString('xl/worksheets/sheet1.xml',
    '<?xml version="1.0" encoding="UTF-8"?>
    <worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
        <sheetData>
            <row r="1">
                <c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c><c r="C1" t="s"><v>2</v></c>
                <c r="D1" t="s"><v>3</v></c><c r="E1" t="s"><v>4</v></c><c r="F1" t="s"><v>5</v></c>
                <c r="G1" t="s"><v>6</v></c><c r="H1" t="s"><v>7</v></c><c r="I1" t="s"><v>8</v></c>
            </row>
            <row r="2">
                <c r="A2" t="s"><v>9</v></c><c r="B2" t="s"><v>10</v></c><c r="C2" t="s"><v>11</v></c>
                <c r="D2" t="s"><v>12</v></c><c r="E2" t="s"><v>13</v></c><c r="F2" t="s"><v>14</v></c>
                <c r="G2" s="1"><v>' . $dobSerial . '</v></c><c r="H2" t="s"><v>15</v></c>
                <c r="I2" t="s"><v>16</v></c>
            </row>
        </sheetData>
    </worksheet>');
$zip->close();

$xlsxRows = SpreadsheetReader::read($xlsxPath, 'students.xlsx');
check('xlsx data row parsed', count($xlsxRows), 1);
check('shared strings resolved', $xlsxRows[0]['first_name'], 'Lan');
check('date cell converted from serial to Y-m-d', $xlsxRows[0]['date_of_birth'], '2011-03-05');
check('class column read', $xlsxRows[0]['class'], 'Grade 10-A');
$rows[] = $xlsxRows[0];
unlink($xlsxPath);

// ---------------------------------------------------------------------
echo "\n3. Import creates students, assigns class by name, hashes passwords\n";
$rows[] = ['student_code' => 'S003', 'first_name' => 'Bad', 'last_name' => 'Row',
           'username' => 'bad.row', 'email' => '', 'password' => 'secret3'];
$summary = $service->importStudents($rows);

check('total rows', $summary['total'], 4);
check('created count', $summary['created'], 3);
check('failed count', $summary['failed'], 1);
check('error mentions the missing email', stripos($summary['errors'][0]['message'] ?? '', 'email') !== false);
check('error keeps the file row number', $summary['errors'][0]['row'] > 1);

$count = (int) $db->query('SELECT COUNT(*) FROM students')->fetchColumn();
check('students inserted', $count, 3);

$row = $db->query("SELECT s.student_code, s.class_id, u.username, u.password, u.status
                   FROM students s JOIN users u ON s.user_id = u.id
                   WHERE s.student_code = 'S001'")->fetch();
check('class resolved by name', (int) $row['class_id'], 1);
check('password hashed once', password_verify('secret1', $row['password']));
check('account active', $row['status'], 'active');

$row2 = $db->query("SELECT u.status FROM students s JOIN users u ON s.user_id = u.id
                    WHERE s.student_code = 'S002'")->fetch();
check('blank status defaults to active', $row2['status'], 'active');

// ---------------------------------------------------------------------
echo "\n4. Duplicate protection on import\n";
$dupes = [
    ['student_code' => 'S001', 'first_name' => 'Dup', 'last_name' => 'Code', 'username' => 'dup.1', 'email' => 'dup1@school.local', 'password' => 'secret4'],
    ['student_code' => 'S010', 'first_name' => 'Dup', 'last_name' => 'User', 'username' => 'mai.tran', 'email' => 'dup2@school.local', 'password' => 'secret5'],
    ['student_code' => 'S011', 'first_name' => 'Dup', 'last_name' => 'Mail', 'username' => 'dup.3', 'email' => 'mai@school.local', 'password' => 'secret6'],
    ['student_code' => 'S012', 'first_name' => 'Bad', 'last_name' => 'Class', 'username' => 'dup.4', 'email' => 'dup4@school.local', 'password' => 'secret7', 'class' => 'Nope 99'],
];
$summary = $service->importStudents($dupes);

check('all four rows rejected', $summary['failed'], 4);
check('created stays zero', $summary['created'], 0);
$messages = implode(' | ', array_column($summary['errors'], 'message'));
check('duplicate code reported', strpos($messages, 'S001') !== false && strpos($messages, 'already') !== false);
check('duplicate username reported', strpos($messages, 'mai.tran') !== false);
check('duplicate email reported', strpos($messages, 'mai@school.local') !== false);
check('unknown class reported with available classes', strpos($messages, 'Grade 10-A') !== false);
check('nothing partially inserted', (int) $db->query('SELECT COUNT(*) FROM students')->fetchColumn(), 3);

// ---------------------------------------------------------------------
echo "\n5. Pagination\n";
$paged = $model->paginateWithClass(1, 2);
check('page 1 respects page size', count($paged['rows']), 2);
check('total is the full row count', $paged['total'], 3);

$paged = $model->paginateWithClass(9, 2);
check('page beyond the end is empty, not an error', $paged['rows'], []);

$paged = $model->paginateWithClass(1, 2, 'Mai');
check('keyword filter narrows results', count($paged['rows']), 1);
check('keyword matches username column', $paged['rows'][0]['username'], 'mai.tran');

$paged = $model->paginateWithClass(1, 2, 'S00');
check('keyword matches student code', $paged['total'], 2);

// ---------------------------------------------------------------------
echo "\n----------------------------------------\n";
echo "{$pass} passed, {$fail} failed\n\n";
exit($fail === 0 ? 0 : 1);
