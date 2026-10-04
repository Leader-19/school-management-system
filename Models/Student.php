<?php

class Student extends Model {
    protected $table = 'students';

    protected $fillable = [
        'user_id', 'student_code', 'first_name', 'last_name',
        'date_of_birth', 'gender', 'address', 'phone', 'class_id'
    ];

    /**
     * Columns from the users table exposed on student rows.
     */
    private const USER_COLUMNS = 'u.username, u.email, u.status AS user_status, u.role_id';

    public function findByUserId($userId) {
        return $this->query("SELECT * FROM {$this->table} WHERE user_id = ?", [$userId])->fetch();
    }

    public function findByStudentCode($code) {
        return $this->query("SELECT * FROM {$this->table} WHERE student_code = ?", [$code])->fetch();
    }

    public function findAllWithClass() {
        $sql = "SELECT s.*, c.name as class_name, " . self::USER_COLUMNS . "
                FROM {$this->table} s
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN users u ON s.user_id = u.id
                ORDER BY s.id DESC";
        return $this->query($sql)->fetchAll();
    }

    /**
     * Server-side pagination for the student list, optionally filtered by a
     * search keyword (code, name, email or username).
     *
     * LIMIT/OFFSET are cast to int before interpolation, so they are safe.
     *
     * @return array{rows: array, total: int, page: int, per_page: int}
     */
    public function paginateWithClass($page = 1, $perPage = 15, $keyword = null) {
        $perPage = max(1, (int) $perPage);
        $page    = max(1, (int) $page);
        $offset  = ($page - 1) * $perPage;

        $where  = '';
        $params = [];
        $keyword = trim((string) $keyword);
        if ($keyword !== '') {
            $where  = 'WHERE s.student_code LIKE ? OR s.first_name LIKE ?'
                    . ' OR s.last_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?';
            $needle = '%' . $keyword . '%';
            $params = [$needle, $needle, $needle, $needle, $needle];
        }

        $total = (int) $this->query(
            "SELECT COUNT(*) AS total
             FROM {$this->table} s
             LEFT JOIN users u ON s.user_id = u.id
             {$where}",
            $params
        )->fetch()['total'];

        $sql = "SELECT s.*, c.name as class_name, " . self::USER_COLUMNS . "
                FROM {$this->table} s
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN users u ON s.user_id = u.id
                {$where}
                ORDER BY s.id DESC
                LIMIT {$perPage} OFFSET {$offset}";
        $rows = $this->query($sql, $params)->fetchAll();

        return [
            'rows'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage
        ];
    }

    public function findByClassId($classId) {
        $sql = "SELECT s.*, c.name as class_name, " . self::USER_COLUMNS . "
                FROM {$this->table} s
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN users u ON s.user_id = u.id
                WHERE s.class_id = ?
                ORDER BY s.first_name ASC, s.last_name ASC";
        return $this->query($sql, [$classId])->fetchAll();
    }

    public function findWithDetails($id) {
        $sql = "SELECT s.*, c.name as class_name, " . self::USER_COLUMNS . ",
                       COALESCE(u.status, 'inactive') AS user_status
                FROM {$this->table} s
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN users u ON s.user_id = u.id
                WHERE s.id = ?";
        return $this->query($sql, [$id])->fetch();
    }

    public function countAll() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        return (int) $this->query($sql)->fetch()['total'];
    }

    public function countByClassId($classId) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE class_id = ?";
        return (int) $this->query($sql, [$classId])->fetch()['total'];
    }

    public function search($keyword) {
        $keyword = "%{$keyword}%";
        $sql = "SELECT s.*, c.name as class_name, " . self::USER_COLUMNS . "
                FROM {$this->table} s
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN users u ON s.user_id = u.id
                WHERE s.student_code LIKE ?
                   OR s.first_name LIKE ?
                   OR s.last_name LIKE ?
                   OR u.email LIKE ?
                ORDER BY s.id DESC";
        return $this->query($sql, [$keyword, $keyword, $keyword, $keyword])->fetchAll();
    }

    public function studentCodeExists($code, $excludeId = null) {
        return $this->existsBy('student_code', $code, $excludeId);
    }
}