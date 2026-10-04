<?php

class Assignment extends Model {
    protected $table = 'assignments';

    protected $fillable = [
        'title', 'description', 'subject_id', 'class_id',
        'teacher_id', 'due_date', 'attachments'
    ];

    public function findAllWithDetails() {
        $sql = "SELECT a.*, s.name as subject_name, c.name as class_name, u.username AS teacher_name
                FROM {$this->table} a
                JOIN subjects s ON a.subject_id = s.id
                JOIN classes c ON a.class_id = c.id
                JOIN users u ON a.teacher_id = u.id
                ORDER BY a.due_date ASC";
        return $this->query($sql)->fetchAll();
    }

    /**
     * Server-side pagination over all assignments with joined details,
     * optionally filtered by title/subject/class name.
     *
     * @return array{rows: array, total: int, page: int, per_page: int}
     */
    public function paginateWithDetails($page = 1, $perPage = 15, $keyword = null, $teacherId = null) {
        $perPage = max(1, (int) $perPage);
        $page    = max(1, (int) $page);
        $offset  = ($page - 1) * $perPage;

        $conditions = [];
        $params = [];

        if (trim((string) $keyword) !== '') {
            $needle = '%' . trim((string) $keyword) . '%';
            $conditions[] = '(a.title LIKE ? OR s.name LIKE ? OR c.name LIKE ?)';
            $params = [$needle, $needle, $needle];
        }
        if ($teacherId !== null) {
            $conditions[] = 'a.teacher_id = ?';
            $params[] = (int) $teacherId;
        }
        $where = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $total = (int) $this->query(
            "SELECT COUNT(*) AS total
             FROM {$this->table} a
             JOIN subjects s ON a.subject_id = s.id
             JOIN classes c ON a.class_id = c.id
             {$where}",
            $params
        )->fetch()['total'];

        $sql = "SELECT a.*, s.name as subject_name, c.name as class_name, u.username AS teacher_name
                FROM {$this->table} a
                JOIN subjects s ON a.subject_id = s.id
                JOIN classes c ON a.class_id = c.id
                JOIN users u ON a.teacher_id = u.id
                {$where}
                ORDER BY a.due_date ASC
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
        $sql = "SELECT a.*, s.name as subject_name, c.name as class_name, u.username AS teacher_name
                FROM {$this->table} a
                JOIN subjects s ON a.subject_id = s.id
                JOIN classes c ON a.class_id = c.id
                JOIN users u ON a.teacher_id = u.id
                WHERE a.class_id = ?
                ORDER BY a.due_date ASC";
        return $this->query($sql, [$classId])->fetchAll();
    }

    public function findByTeacherId($teacherId) {
        $sql = "SELECT a.*, s.name as subject_name, c.name as class_name
                FROM {$this->table} a
                JOIN subjects s ON a.subject_id = s.id
                JOIN classes c ON a.class_id = c.id
                WHERE a.teacher_id = ?
                ORDER BY a.due_date ASC";
        return $this->query($sql, [$teacherId])->fetchAll();
    }

    public function findForStudent($studentId) {
        $sql = "SELECT DISTINCT a.*, s.name as subject_name, c.name as class_name, u.username AS teacher_name
                FROM {$this->table} a
                JOIN students st ON a.class_id = st.class_id
                JOIN subjects s ON a.subject_id = s.id
                JOIN classes c ON a.class_id = c.id
                JOIN users u ON a.teacher_id = u.id
                WHERE st.id = ?
                ORDER BY a.due_date ASC";
        return $this->query($sql, [$studentId])->fetchAll();
    }

    public function findWithDetails($id) {
        $sql = "SELECT a.*, s.name as subject_name, c.name as class_name, u.username AS teacher_name
                FROM {$this->table} a
                JOIN subjects s ON a.subject_id = s.id
                JOIN classes c ON a.class_id = c.id
                JOIN users u ON a.teacher_id = u.id
                WHERE a.id = ?";
        return $this->query($sql, [$id])->fetch();
    }

    public function countAll() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        return (int) $this->query($sql)->fetch()['total'];
    }

    public function countByTeacher($teacherId) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE teacher_id = ?";
        return (int) $this->query($sql, [$teacherId])->fetch()['total'];
    }
}