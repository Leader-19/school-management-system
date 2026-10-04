<?php

class ClassRoomRepository {
    private $model;
    private $studentModel;
    private $subjectModel;

    public function __construct() {
        $this->model = new ClassRoom();
        $this->studentModel = new Student();
        $this->subjectModel = new Subject();
    }

    public function findAll() {
        return $this->model->findAll();
    }

    public function findAllWithTeacher() {
        return $this->model->findAllWithTeacher();
    }

    /**
     * One page of classes plus totals for the paginator.
     */
    public function paginateWithTeacher($page = 1, $perPage = 15, $keyword = null) {
        return $this->model->paginateWithTeacher($page, $perPage, $keyword);
    }

    public function findById($id) {
        return $this->model->findById($id);
    }

    public function findWithDetails($id) {
        return $this->model->findWithDetails($id);
    }

    public function nameExists($name, $excludeId = null) {
        return $this->model->nameExists($name, $excludeId);
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

    public function countAll() {
        return $this->model->countAll();
    }

    public function getClassStudents($classId) {
        return $this->studentModel->findByClassId($classId);
    }

    public function getClassSubjects($classId) {
        return $this->subjectModel->findByClassId($classId);
    }

    public function getClassWithFullDetails($classId) {
        $class = $this->model->findWithDetails($classId);
        if (!$class) {
            return false;
        }

        $class['students'] = $this->getClassStudents($classId);
        $class['subjects'] = $this->getClassSubjects($classId);

        return $class;
    }
}