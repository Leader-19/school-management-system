<?php

class ClassRoomService {
    private $repo;
    private $userRepo;
    private $assignmentModel;

    public function __construct() {
        $this->repo = new ClassRoomRepository();
        $this->userRepo = new UserRepository();
        $this->assignmentModel = new Assignment();
    }

    public function getAll() {
        return $this->repo->findAllWithTeacher();
    }

    /**
     * One page of the class list plus totals for the paginator.
     */
    public function paginate($page = 1, $perPage = 15, $keyword = null) {
        return $this->repo->paginateWithTeacher($page, $perPage, $keyword);
    }

    public function getById($id) {
        return $this->repo->findWithDetails($id);
    }

    public function getTeachers() {
        return $this->userRepo->findAllTeachers();
    }

    /**
     * @throws RuntimeException when the class name is missing or taken.
     */
    public function create($data) {
        $payload = $this->buildData($data);

        if ($payload === null) {
            throw new RuntimeException('Class name is required.');
        }

        if ($this->repo->nameExists($payload['name'])) {
            throw new RuntimeException("A class named '{$payload['name']}' already exists.");
        }

        return $this->repo->create($payload);
    }

    /**
     * @throws RuntimeException on validation failure.
     */
    public function update($id, $data) {
        $payload = $this->buildData($data);

        if ($payload === null) {
            throw new RuntimeException('Class name is required.');
        }

        if (!$this->repo->findById($id)) {
            throw new RuntimeException('Class not found.');
        }

        if ($this->repo->nameExists($payload['name'], $id)) {
            throw new RuntimeException("A class named '{$payload['name']}' already exists.");
        }

        return $this->repo->update($id, $payload);
    }

    /**
     * Deleting a class is safe for students (ON DELETE SET NULL) but would
     * take the class's assignments and submissions with it, so refuse when
     * there is still work attached.
     */
    public function delete($id) {
        $class = $this->repo->findById($id);

        if (!$class) {
            return false;
        }

        $assignments = $this->assignmentModel->findByClassId($id);
        if (!empty($assignments)) {
            throw new RuntimeException(
                'This class still has ' . count($assignments) . ' assignment(s). Delete or move them first.'
            );
        }

        return $this->repo->delete($id);
    }

    public function getStudents($id) {
        return $this->repo->getClassStudents($id);
    }

    public function getSubjects($id) {
        return $this->repo->getClassSubjects($id);
    }

    /**
     * Map the form onto the classes table columns.
     */
    private function buildData($data) {
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            return null;
        }

        return [
            'name'        => $name,
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'teacher_id'  => !empty($data['teacher_id']) ? (int) $data['teacher_id'] : null
        ];
    }
}