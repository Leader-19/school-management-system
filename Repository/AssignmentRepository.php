<?php

class AssignmentRepository {
    private $model;

    public function __construct() {
        $this->model = new Assignment();
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

    public function findAllWithDetails() {
        return $this->model->findAllWithDetails();
    }

    public function paginateWithDetails($page = 1, $perPage = 15, $keyword = null, $teacherId = null) {
        return $this->model->paginateWithDetails($page, $perPage, $keyword, $teacherId);
    }

    public function findByClassId($classId) {
        return $this->model->findByClassId($classId);
    }

    public function findByTeacherId($teacherId) {
        return $this->model->findByTeacherId($teacherId);
    }

    public function findForStudent($studentId) {
        return $this->model->findForStudent($studentId);
    }

    public function findWithDetails($id) {
        return $this->model->findWithDetails($id);
    }

    public function countAll() {
        return $this->model->countAll();
    }
}