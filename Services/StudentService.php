<?php

class StudentService {
    private $studentRepo;
    private $userRepo;
    private $classRepo;
    private $studentModel;

    /** Values accepted by the students.gender ENUM column. */
    private const GENDERS = ['male', 'female', 'other'];

    /** roles.id of the student role, seeded by database/schema.sql. */
    private const ROLE_STUDENT = 3;

    /**
     * Column names accepted in an import spreadsheet. The first alias is the
     * one used in the downloadable template; the rest are tolerated spellings
     * so teachers do not have to match the template exactly.
     */
    private const IMPORT_ALIASES = [
        'student_code'  => ['student_code', 'code', 'studentcode', 'student_id', 'studentid', 'student_no', 'studentno'],
        'first_name'    => ['first_name', 'firstname', 'first', 'given_name', 'givenname'],
        'last_name'     => ['last_name', 'lastname', 'last', 'surname', 'family_name', 'familyname'],
        'username'      => ['username', 'user_name', 'login', 'user'],
        'email'         => ['email', 'e_mail', 'email_address', 'mail'],
        'password'      => ['password', 'pass', 'pwd'],
        'date_of_birth' => ['date_of_birth', 'dob', 'dateofbirth', 'birth_date', 'birthdate', 'birthday'],
        'gender'        => ['gender', 'sex'],
        'address'       => ['address'],
        'phone'         => ['phone', 'phone_number', 'mobile', 'tel', 'telephone'],
        'class'         => ['class', 'class_name', 'classname', 'class_id', 'classid', 'room'],
        'status'        => ['status'],
    ];

    /** Rows per import, so a single request cannot time out mid-import. */
    private const IMPORT_MAX_ROWS = 1000;

    /** Errors kept in the report before the view truncates the list. */
    private const IMPORT_MAX_REPORTED_ERRORS = 100;

    public function __construct() {
        $this->studentRepo = new StudentRepository();
        $this->userRepo = new UserRepository();
        $this->classRepo = new ClassRoomRepository();
        $this->studentModel = new Student();
    }

    public function getAllStudents() {
        return $this->studentRepo->findAllWithClass();
    }

    /**
     * One page of the student list, plus the total row count so the view
     * can render pagination links.
     */
    public function paginateStudents($page = 1, $perPage = 15, $keyword = null) {
        return $this->studentRepo->paginateWithClass($page, $perPage, $keyword);
    }

    public function getStudentById($id) {
        return $this->studentRepo->findWithDetails($id);
    }

    public function createStudent($data) {
        $studentData = $this->buildStudentData($data);

        // Students always log in with role_id = 3 (see schema.sql seed data).
        $userData = $this->buildUserData($data, self::ROLE_STUDENT);

        return $this->studentRepo->createWithUser($userData, $studentData);
    }

    public function updateStudent($id, $data) {
        $studentData = $this->buildStudentData($data, $id);
        $userData = $this->buildUserData($data, null, true);

        return $this->studentRepo->updateWithUser($id, $userData, $studentData);
    }

    public function deleteStudent($id) {
        return $this->studentRepo->deleteWithUser($id);
    }

    public function searchStudents($keyword) {
        return trim($keyword) === '' ? $this->getAllStudents() : $this->studentModel->search(trim($keyword));
    }

    public function getStudentByUserId($userId) {
        return $this->studentModel->findByUserId($userId);
    }

    public function getClasses() {
        return $this->classRepo->findAll();
    }

    /**
     * Bulk-create students from rows read out of a spreadsheet (see
     * SpreadsheetReader). Every row is validated and created independently,
     * so one bad row never blocks the rest - the summary reports exactly
     * which rows failed and why.
     *
     * @param array[] $rows Associative rows keyed by normalised headers.
     * @return array{total:int, created:int, failed:int, created_names:string[], errors:array[], errors_omitted:int}
     * @throws RuntimeException when the file has too many rows.
     */
    public function importStudents(array $rows) {
        if (count($rows) > self::IMPORT_MAX_ROWS) {
            throw new RuntimeException(
                'The file has ' . count($rows) . ' data rows. Please import at most '
                . self::IMPORT_MAX_ROWS . ' students at a time.'
            );
        }

        $classIndex = $this->buildClassIndex();

        $summary = [
            'total'           => count($rows),
            'created'         => 0,
            'failed'          => 0,
            'created_names'   => [],
            'errors'          => [],
            'errors_omitted'  => 0
        ];

        foreach (array_values($rows) as $index => $row) {
            $rowNumber = $index + 2; // +2: spreadsheet row 1 is the header.

            try {
                $data = $this->buildImportRowData($row);
                $name = trim($data['first_name'] . ' ' . $data['last_name']);

                $error = $this->validate($data);
                if ($error !== null) {
                    throw new RuntimeException($error);
                }

                $data['class_id'] = $this->resolveImportClass(
                    $this->importField($row, 'class'),
                    $classIndex
                );

                $studentId = $this->createStudent($data);

                if (!$studentId) {
                    throw new RuntimeException('The student could not be saved.');
                }

                $summary['created']++;
                if (count($summary['created_names']) < 50) {
                    $summary['created_names'][] = $name . ' (' . $data['student_code'] . ')';
                }
            } catch (RuntimeException $e) {
                $summary['failed']++;
                $this->recordImportError($summary, $rowNumber, $row, $e->getMessage());
            } catch (Throwable $e) {
                $summary['failed']++;
                $this->recordImportError($summary, $rowNumber, $row, 'Unexpected error: ' . $e->getMessage());
            }
        }

        return $summary;
    }

    /**
     * Map a spreadsheet row onto the same field names the create form posts.
     */
    private function buildImportRowData(array $row) {
        $dob = $this->importField($row, 'date_of_birth');

        // An Excel DATE cell arrives as a serial number (e.g. "45123") when
        // its formatting is not detected; convert it before strtotime sees it.
        if (ctype_digit($dob) && (int) $dob > 20000) {
            $dob = SpreadsheetReader::serialToDate((int) $dob) ?? $dob;
        }

        return [
            'username'      => $this->importField($row, 'username'),
            'email'         => $this->importField($row, 'email'),
            'password'      => $this->importField($row, 'password'),
            'student_code'  => $this->importField($row, 'student_code'),
            'first_name'    => $this->importField($row, 'first_name'),
            'last_name'     => $this->importField($row, 'last_name'),
            'date_of_birth' => $dob,
            'gender'        => $this->importField($row, 'gender'),
            'address'       => $this->importField($row, 'address'),
            'phone'         => $this->importField($row, 'phone'),
            'class_id'      => '',
            'status'        => $this->importField($row, 'status')
        ];
    }

    /**
     * First non-empty value among a column's accepted aliases.
     */
    private function importField(array $row, string $field) {
        foreach (self::IMPORT_ALIASES[$field] as $alias) {
            if (isset($row[$alias]) && trim((string) $row[$alias]) !== '') {
                return trim((string) $row[$alias]);
            }
        }
        return '';
    }

    /**
     * Look up classes by lower-cased name and by id, keeping the original
     * display names for error messages.
     *
     * @return array{name: array<string,int>, byId: array<int,int>, display: string[]}
     */
    private function buildClassIndex() {
        $index = ['name' => [], 'byId' => [], 'display' => []];
        foreach ($this->classRepo->findAll() as $class) {
            $id = (int) $class['id'];
            $name = trim((string) $class['name']);
            $index['byId'][$id] = $id;
            $index['name'][mb_strtolower($name)] = $id;
            $index['display'][] = $name;
        }
        return $index;
    }

    /**
     * Resolve the class column: empty = unassigned, a class name or a class
     * id. An unknown class is a row error, never a silent null.
     *
     * @throws RuntimeException when no class matches.
     */
    private function resolveImportClass($value, array $classIndex) {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $byName = $classIndex['name'][mb_strtolower($value)] ?? null;
        if ($byName !== null) {
            return $byName;
        }

        if (ctype_digit($value) && isset($classIndex['byId'][(int) $value])) {
            return (int) $value;
        }

        $available = $classIndex['display'];
        $examples  = implode(', ', array_slice($available, 0, 8));
        $hint      = $available === []
            ? 'No classes exist yet - create classes first or leave the class column empty.'
            : 'Available classes: ' . $examples . (count($available) > 8 ? ', ...' : '');

        throw new RuntimeException("Class '{$value}' was not found. {$hint}");
    }

    /**
     * Append one row error to the report, capping the stored list.
     */
    private function recordImportError(array &$summary, $rowNumber, array $row, $message) {
        if (count($summary['errors']) >= self::IMPORT_MAX_REPORTED_ERRORS) {
            $summary['errors_omitted']++;
            return;
        }

        $student = trim(
            $this->importField($row, 'first_name') . ' ' . $this->importField($row, 'last_name')
        );

        $summary['errors'][] = [
            'row'     => $rowNumber,
            'student' => $student !== '' ? $student : '(no name)',
            'message' => (string) $message
        ];
    }

    /**
     * Validate the student form and return the first problem found.
     */
    public function validate($data, $studentId = null) {
        if (trim((string) ($data['username'] ?? '')) === '') {
            return 'Username is required.';
        }
        if (!filter_var((string) ($data['email'] ?? ''), FILTER_VALIDATE_EMAIL)) {
            return 'A valid email address is required.';
        }
        if ($studentId === null) {
            if ((string) ($data['password'] ?? '') === '') {
                return 'Password is required.';
            } elseif (strlen((string) $data['password']) < 6) {
                return 'Password must be at least 6 characters long.';
            }
        } elseif ((string) ($data['password'] ?? '') !== '' && strlen((string) $data['password']) < 6) {
            return 'Password must be at least 6 characters long.';
        }
        if (trim((string) ($data['student_code'] ?? '')) === '') {
            return 'Student code is required.';
        }
        if (trim((string) ($data['first_name'] ?? '')) === '') {
            return 'First name is required.';
        }
        if (trim((string) ($data['last_name'] ?? '')) === '') {
            return 'Last name is required.';
        }

        return null;
    }

    /**
     * Map raw $_POST onto the students table columns.
     */
    private function buildStudentData($data, $studentId = null) {
        return [
            'student_code'  => trim((string) ($data['student_code'] ?? '')),
            'first_name'    => trim((string) ($data['first_name'] ?? '')),
            'last_name'     => trim((string) ($data['last_name'] ?? '')),
            'date_of_birth' => $this->normaliseDate($data['date_of_birth'] ?? null),
            'gender'        => $this->normaliseGender($data['gender'] ?? null),
            'address'       => trim((string) ($data['address'] ?? '')) ?: null,
            'phone'         => trim((string) ($data['phone'] ?? '')) ?: null,
            'class_id'      => !empty($data['class_id']) ? (int) $data['class_id'] : null
        ];
    }

    /**
     * Map raw $_POST onto the users table columns.
     *
     * The password is forwarded as PLAIN TEXT: UserRepository hashes it
     * exactly once.
     */
    private function buildUserData($data, $defaultRoleId = null, $forUpdate = false) {
        $userData = [
            'username' => trim((string) ($data['username'] ?? '')),
            'email'    => trim((string) ($data['email'] ?? '')),
            'status'   => in_array($data['status'] ?? '', ['active', 'inactive'], true)
                            ? $data['status']
                            : 'active'
        ];

        if ($defaultRoleId !== null) {
            $userData['role_id'] = (int) $defaultRoleId;
        }

        if (!$forUpdate || (string) ($data['password'] ?? '') !== '') {
            $userData['password'] = (string) ($data['password'] ?? '');
        }

        return $userData;
    }

    /**
     * The form posts a <input type="date">, but older versions of this form
     * used the name "dob". Accept both and normalise to Y-m-d.
     */
    private function normaliseDate($value) {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        return $timestamp === false ? null : date('Y-m-d', $timestamp);
    }

    /**
     * The column is ENUM('male','female','other'); the form used to post
     * "Male"/"Female"/"Other", which MySQL rejects in strict mode and that
     * aborted the whole insert.
     */
    private function normaliseGender($value) {
        $value = strtolower(trim((string) $value));
        return in_array($value, self::GENDERS, true) ? $value : null;
    }
}