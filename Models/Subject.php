<?php

class Subject extends Model {
    protected $table = 'subjects';

    protected $fillable = ['name', 'code', 'description', 'credit_hours'];

    // The column is "code"; the old query looked for a non-existent
    // "subject_code" column and always failed.
    public function findByCode($code) {
        return $this->query("SELECT * FROM {$this->table} WHERE code = ?", [$code])->fetch();
    }

    public function findAll() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByClassId($classId) {
        $sql = "SELECT s.*
                FROM {$this->table} s
                JOIN class_subjects cs ON s.id = cs.subject_id
                WHERE cs.class_id = ?
                ORDER BY s.name ASC";
        return $this->query($sql, [$classId])->fetchAll();
    }

    public function countAll() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        return (int) $this->query($sql)->fetch()['total'];
    }
}