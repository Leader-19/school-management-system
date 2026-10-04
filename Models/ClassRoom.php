<?php

class ClassRoom extends Model {
    protected $table = 'classes';

    protected $fillable = ['name', 'description', 'teacher_id'];

    public function findAllWithTeacher() {
        $sql = "SELECT c.*, u.username AS teacher_name,
                       (SELECT COUNT(*) FROM students s WHERE s.class_id = c.id) AS student_count
                FROM {$this->table} c
                LEFT JOIN users u ON c.teacher_id = u.id
                ORDER BY c.name ASC";
        return $this->query($sql)->fetchAll();
    }

    /**
     * Server-side pagination over classes, optionally filtered by name.
     *
     * @return array{rows: array, total: int, page: int, per_page: int}
     */
    public function paginateWithTeacher($page = 1, $perPage = 15, $keyword = null) {
        $perPage = max(1, (int) $perPage);
        $page    = max(1, (int) $page);
        $offset  = ($page - 1) * $perPage;

        $where  = '';
        $params = [];
        $needle = '%' . trim((string) $keyword) . '%';
        if (trim((string) $keyword) !== '') {
            $where  = 'WHERE c.name LIKE ?';
            $params = [$needle];
        }

        $total = (int) $this->query(
            "SELECT COUNT(*) AS total FROM {$this->table} c {$where}",
            $params
        )->fetch()['total'];

        $sql = "SELECT c.*, u.username AS teacher_name,
                       (SELECT COUNT(*) FROM students s WHERE s.class_id = c.id) AS student_count
                FROM {$this->table} c
                LEFT JOIN users u ON c.teacher_id = u.id
                {$where}
                ORDER BY c.name ASC
                LIMIT {$perPage} OFFSET {$offset}";
        $rows = $this->query($sql, $params)->fetchAll();

        return [
            'rows'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage
        ];
    }

    public function findWithDetails($id) {
        $sql = "SELECT c.*, u.username AS teacher_name,
                       (SELECT COUNT(*) FROM students s WHERE s.class_id = c.id) AS student_count
                FROM {$this->table} c
                LEFT JOIN users u ON c.teacher_id = u.id
                WHERE c.id = ?";
        return $this->query($sql, [$id])->fetch();
    }

    public function nameExists($name, $excludeId = null) {
        return $this->existsBy('name', $name, $excludeId);
    }

    public function countAll() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        return (int) $this->query($sql)->fetch()['total'];
    }
}