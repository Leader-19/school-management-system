<?php

class AttendanceSession extends Model {
    protected $table = 'attendance_sessions';

    protected $fillable = ['class_id', 'subject_id', 'session_date', 'session_type', 'notes', 'created_by'];

    /**
     * Find an existing session for class+date+type, or create it. The unique
     * key (class_id, session_date, session_type) backs this up, so a double
     * submit can never produce two sessions for the same morning.
     */
    public function findOrCreate($classId, $sessionDate, $sessionType, $subjectId = null, $userId = null) {
        $existing = $this->query(
            "SELECT * FROM {$this->table}
             WHERE class_id = ? AND session_date = ? AND session_type = ?",
            [$classId, $sessionDate, $sessionType]
        )->fetch();

        if ($existing) {
            return $existing;
        }

        $id = $this->create([
            'class_id'      => (int) $classId,
            'subject_id'    => $subjectId !== null && $subjectId !== '' ? (int) $subjectId : null,
            'session_date'  => $sessionDate,
            'session_type'  => $sessionType,
            'created_by'    => $userId !== null ? (int) $userId : null
        ]);

        return $this->findById($id);
    }

    /**
     * Paginated session list for one class.
     *
     * @return array{rows: array, total: int, page: int, per_page: int}
     */
    public function paginateByClassId($classId, $page = 1, $perPage = 10) {
        $perPage = max(1, (int) $perPage);
        $page    = max(1, (int) $page);
        $offset  = ($page - 1) * $perPage;

        $total = (int) $this->query(
            "SELECT COUNT(*) AS total FROM {$this->table} WHERE class_id = ?",
            [$classId]
        )->fetch()['total'];

        $rows = $this->query(
            "SELECT s.*, u.username AS created_by_name,
                    (SELECT COUNT(*) FROM student_attendance sa WHERE sa.session_id = s.id) AS marked_count
             FROM {$this->table} s
             LEFT JOIN users u ON s.created_by = u.id
             WHERE s.class_id = ?
             ORDER BY s.session_date DESC, s.id DESC
             LIMIT {$perPage} OFFSET {$offset}",
            [$classId]
        )->fetchAll();

        return [
            'rows'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage
        ];
    }

    /**
     * One session with class/subject names for the heading.
     */
    public function findWithDetails($id) {
        return $this->query(
            "SELECT s.*, c.name AS class_name, sub.name AS subject_name
             FROM {$this->table} s
             JOIN classes c ON s.class_id = c.id
             LEFT JOIN subjects sub ON s.subject_id = sub.id
             WHERE s.id = ?",
            [$id]
        )->fetch();
    }
}
