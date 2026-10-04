<?php

class AssignmentSubmission extends Model {
    protected $table = 'assignment_submissions';

    protected $fillable = [
        'assignment_id', 'student_id', 'content', 'file_path',
        'file_name', 'grade', 'feedback', 'submitted_at', 'graded_at'
    ];

    public function findByAssignmentId($assignmentId) {
        $sql = "SELECT asb.*, st.student_code, CONCAT(st.first_name, ' ', st.last_name) AS student_name
                FROM {$this->table} asb
                JOIN students st ON asb.student_id = st.id
                WHERE asb.assignment_id = ?
                ORDER BY asb.submitted_at DESC";
        return $this->query($sql, [$assignmentId])->fetchAll();
    }

    public function findByStudentId($studentId) {
        $sql = "SELECT asb.*, a.title, a.due_date
                FROM {$this->table} asb
                JOIN assignments a ON asb.assignment_id = a.id
                WHERE asb.student_id = ?
                ORDER BY asb.submitted_at DESC";
        return $this->query($sql, [$studentId])->fetchAll();
    }

    public function findByAssignmentAndStudent($assignmentId, $studentId) {
        return $this->query("SELECT * FROM {$this->table} WHERE assignment_id = ? AND student_id = ?", [$assignmentId, $studentId])->fetch();
    }

    public function countByAssignment($assignmentId) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE assignment_id = ?";
        return (int) $this->query($sql, [$assignmentId])->fetch()['total'];
    }

    public function countPendingGrading($teacherId) {
        $sql = "SELECT COUNT(asb.id) as total
                FROM {$this->table} asb
                JOIN assignments a ON asb.assignment_id = a.id
                WHERE a.teacher_id = ? AND asb.grade IS NULL";
        return (int) $this->query($sql, [$teacherId])->fetch()['total'];
    }
}