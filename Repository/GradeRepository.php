<?php

class GradeRepository {
    private $model;

    public function __construct() {
        $this->model = new Grade();
    }

    public function findAll() {
        return $this->model->findAll();
    }

    public function findById($id) {
        return $this->model->findById($id);
    }

    public function create($data) {
        return $this->model->create($data);
    }

    public function update($id, $data) {
        return $this->model->update($id, $data);
    }

    public function delete($id) {
        return $this->model->delete($id);
    }

    public function findByStudentId($studentId) {
        return $this->model->findByStudentId($studentId);
    }

    public function findByStudentAndSubject($studentId, $subjectId) {
        return $this->model->findByStudentAndSubject($studentId, $subjectId);
    }

    public function findAllWithDetails() {
        return $this->model->findAllWithDetails();
    }

    public function paginateWithDetails($page = 1, $perPage = 15, $keyword = null) {
        return $this->model->paginateWithDetails($page, $perPage, $keyword);
    }

    public function findByClassId($classId) {
        return $this->model->findByClassId($classId);
    }

    public function getAverageByStudent($studentId) {
        return $this->model->getAverageByStudent($studentId);
    }

    public function getStudentTranscript($studentId) {
        $grades = $this->findByStudentId($studentId);
        $transcript = [];
        
        foreach ($grades as $grade) {
            $semester = $grade['semester'];
            if (!isset($transcript[$semester])) {
                $transcript[$semester] = [];
            }
            $transcript[$semester][] = $grade;
        }
        
        return $transcript;
    }

    public function getClassGrades($classId, $subjectId) {
        $allClassGrades = $this->findByClassId($classId);
        $subjectGrades = [];
        
        foreach ($allClassGrades as $grade) {
            if ($grade['subject_id'] == $subjectId) {
                $subjectGrades[] = $grade;
            }
        }
        
        return $subjectGrades;
    }
}
