<?php

class Grade extends Model {
    protected $table = 'grades';

    public function findByStudentId($studentId) {
        $sql = "SELECT g.*, g.academic_year AS year, s.name as subject_name 
                FROM {$this->table} g 
                JOIN subjects s ON g.subject_id = s.id 
                WHERE g.student_id = ? 
                ORDER BY g.semester ASC, s.name ASC";
        return $this->query($sql, [$studentId])->fetchAll();
    }

    public function findByStudentAndSubject($studentId, $subjectId) {
        return $this->query("SELECT * FROM {$this->table} WHERE student_id = ? AND subject_id = ?", [$studentId, $subjectId])->fetchAll();
    }

    public function findAllWithDetails() {
        $sql = "SELECT g.*, g.academic_year AS year, st.student_code, CONCAT(st.first_name, ' ', st.last_name) AS student_name, s.name as subject_name 
                FROM {$this->table} g 
                JOIN students st ON g.student_id = st.id 
                JOIN subjects s ON g.subject_id = s.id 
                ORDER BY g.id DESC";
        return $this->query($sql)->fetchAll();
    }

    /**
     * Server-side pagination over all grades, optionally filtered by a search
     * keyword (student name, student code or subject name).
     *
     * @return array{rows: array, total: int, page: int, per_page: int}
     */
    public function paginateWithDetails($page = 1, $perPage = 15, $keyword = null) {
        $perPage = max(1, (int) $perPage);
        $page    = max(1, (int) $page);
        $offset  = ($page - 1) * $perPage;

        $where  = '';
        $params = [];
        $keyword = trim((string) $keyword);
        if ($keyword !== '') {
            $where  = 'WHERE st.first_name LIKE ? OR st.last_name LIKE ?'
                    . " OR CONCAT(st.first_name, ' ', st.last_name) LIKE ?"
                    . ' OR st.student_code LIKE ? OR s.name LIKE ?';
            $needle = '%' . $keyword . '%';
            $params = [$needle, $needle, $needle, $needle, $needle];
        }

        $total = (int) $this->query(
            "SELECT COUNT(*) AS total
             FROM {$this->table} g
             JOIN students st ON g.student_id = st.id
             JOIN subjects s ON g.subject_id = s.id
             {$where}",
            $params
        )->fetch()['total'];

        $sql = "SELECT g.*, g.academic_year AS year, st.student_code,
                       CONCAT(st.first_name, ' ', st.last_name) AS student_name,
                       s.name as subject_name
                FROM {$this->table} g
                JOIN students st ON g.student_id = st.id
                JOIN subjects s ON g.subject_id = s.id
                {$where}
                ORDER BY g.id DESC
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
        $sql = "SELECT g.*, g.academic_year AS year, st.student_code, CONCAT(st.first_name, ' ', st.last_name) AS student_name, s.name as subject_name 
                FROM {$this->table} g 
                JOIN students st ON g.student_id = st.id 
                JOIN subjects s ON g.subject_id = s.id 
                WHERE st.class_id = ? 
                ORDER BY st.last_name ASC, st.first_name ASC";
        return $this->query($sql, [$classId])->fetchAll();
    }

    public function getAverageByStudent($studentId) {
        $sql = "SELECT AVG(score) as average FROM {$this->table} WHERE student_id = ?";
        return $this->query($sql, [$studentId])->fetch()['average'];
    }
}
