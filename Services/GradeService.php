<?php

class GradeService {
    private $gradeRepo;

    public function __construct() {
        $this->gradeRepo = new GradeRepository();
    }

    public function getStudentGrades($studentId) {
        return $this->gradeRepo->findByStudentId($studentId);
    }

    public function getAllGrades() {
        return $this->gradeRepo->findAllWithDetails();
    }

    /**
     * One page of the grades list plus totals for the paginator.
     */
    public function paginateGrades($page = 1, $perPage = 15, $keyword = null) {
        return $this->gradeRepo->paginateWithDetails($page, $perPage, $keyword);
    }

    public function addGrade($data) {
        return $this->gradeRepo->create($data);
    }

    public function updateGrade($id, $data) {
        return $this->gradeRepo->update($id, $data);
    }

    public function deleteGrade($id) {
        return $this->gradeRepo->delete($id);
    }

    public function getStudentAverage($studentId) {
        return $this->gradeRepo->getAverageByStudent($studentId);
    }
}
