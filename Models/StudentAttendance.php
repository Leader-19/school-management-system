<?php

class StudentAttendance extends Model {
    protected $table = 'student_attendance';

    protected $fillable = ['session_id', 'student_id', 'status', 'note'];

    /** Values accepted by the student_attendance.status ENUM. */
    public const STATUSES = ['present', 'absent', 'late', 'excused'];

    /**
     * Replace the attendance rows of one session inside a transaction.
     * UPDATE where the row exists, INSERT where it does not - the unique key
     * (session_id, student_id) is backed by upsert logic, not ON DUPLICATE
     * KEY, so marked_at refreshes only for rows actually touched.
     */
    public function syncForSession($sessionId, array $marks) {
        $db = $this->db;
        $db->beginTransaction();

        try {
            $existing = $this->query(
                "SELECT id, student_id FROM {$this->table} WHERE session_id = ?",
                [$sessionId]
            )->fetchAll();

            $byStudent = [];
            foreach ($existing as $row) {
                $byStudent[(int) $row['student_id']] = (int) $row['id'];
            }

            $insert = $db->prepare(
                "INSERT INTO {$this->table} (session_id, student_id, status, note)
                 VALUES (:session_id, :student_id, :status, :note)"
            );
            $update = $db->prepare(
                "UPDATE {$this->table} SET status = :status, note = :note WHERE id = :id"
            );

            foreach ($marks as $studentId => $status) {
                $status = in_array($status, self::STATUSES, true) ? $status : 'present';

                if (isset($byStudent[(int) $studentId])) {
                    $update->execute([
                        'status' => $status,
                        'note'   => null,
                        'id'     => $byStudent[(int) $studentId]
                    ]);
                } else {
                    $insert->execute([
                        'session_id' => (int) $sessionId,
                        'student_id' => (int) $studentId,
                        'status'     => $status,
                        'note'       => null
                    ]);
                }
            }

            $db->commit();
            return true;
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Attendance rows for one session joined with student info, so the form
     * can pre-fill previous marks when editing an existing session.
     */
    public function forSession($sessionId) {
        return $this->query(
            "SELECT sa.student_id, sa.status, sa.note,
                    st.student_code, st.first_name, st.last_name
             FROM {$this->table} sa
             JOIN students st ON sa.student_id = st.id
             WHERE sa.session_id = ?
             ORDER BY st.first_name ASC, st.last_name ASC",
            [$sessionId]
        )->fetchAll();
    }

    /**
     * Attendance history of one student across all their sessions.
     */
    public function forStudent($studentId, $limit = 30) {
        $limit = max(1, (int) $limit);
        return $this->query(
            "SELECT sa.status, sa.note, sa.marked_at,
                    s.session_date, s.session_type, c.name AS class_name
             FROM {$this->table} sa
             JOIN attendance_sessions s ON sa.session_id = s.id
             JOIN classes c ON s.class_id = c.id
             WHERE sa.student_id = ?
             ORDER BY s.session_date DESC, s.id DESC
             LIMIT {$limit}",
            [$studentId]
        )->fetchAll();
    }

    /**
     * Per-status totals for one student (used on the student profile).
     */
    public function summaryForStudent($studentId) {
        $rows = $this->query(
            "SELECT status, COUNT(*) AS total
             FROM {$this->table}
             WHERE student_id = ?
             GROUP BY status",
            [$studentId]
        )->fetchAll();

        $summary = array_fill_keys(self::STATUSES, 0);
        foreach ($rows as $row) {
            $summary[$row['status']] = (int) $row['total'];
        }

        return $summary;
    }
}
