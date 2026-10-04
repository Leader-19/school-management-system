<?php

class StudentRepository {
    private $model;
    private $userRepo;

    public function __construct() {
        $this->model = new Student();
        $this->userRepo = new UserRepository();
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

    public function findByUserId($userId) {
        return $this->model->findByUserId($userId);
    }

    public function findByStudentCode($code) {
        return $this->model->findByStudentCode($code);
    }

    public function findAllWithClass() {
        return $this->model->findAllWithClass();
    }

    public function paginateWithClass($page = 1, $perPage = 15, $keyword = null) {
        return $this->model->paginateWithClass($page, $perPage, $keyword);
    }

    public function findByClassId($classId) {
        return $this->model->findByClassId($classId);
    }

    public function findWithDetails($id) {
        return $this->model->findWithDetails($id);
    }

    public function countAll() {
        return $this->model->countAll();
    }

    public function search($keyword) {
        return $this->model->search($keyword);
    }

    public function studentCodeExists($code, $excludeId = null) {
        return $this->model->studentCodeExists($code, $excludeId);
    }

    /**
     * Create the login account and the student profile in one transaction.
     *
     * $userData['password'] must be PLAIN TEXT - UserRepository does the
     * hashing. Hashing here as well produced a double bcrypt hash, which is
     * why students created through the UI could never sign in.
     *
     * @throws RuntimeException on duplicate username/email/student code so the
     *         caller can show a useful message instead of a generic failure.
     */
    public function createWithUser($userData, $studentData) {
        $db = Database::getInstance()->getConnection();

        if ($this->userRepo->usernameExists($userData['username'])) {
            throw new RuntimeException("Username '{$userData['username']}' is already taken.");
        }
        if (!empty($userData['email']) && $this->userRepo->emailExists($userData['email'])) {
            throw new RuntimeException("Email '{$userData['email']}' is already registered.");
        }
        if (!empty($studentData['student_code']) && $this->studentCodeExists($studentData['student_code'])) {
            throw new RuntimeException("Student code '{$studentData['student_code']}' already exists.");
        }

        $db->beginTransaction();
        try {
            $userId = $this->userRepo->create($userData);

            if (!$userId) {
                throw new RuntimeException('Failed to create the login account.');
            }

            $studentData['user_id'] = $userId;
            $studentId = $this->model->create($studentData);

            if (!$studentId) {
                throw new RuntimeException('Failed to create the student profile.');
            }

            $db->commit();
            return $studentId;
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Update the linked account and the student profile in one transaction.
     *
     * @throws RuntimeException on duplicate username/email/student code.
     */
    public function updateWithUser($studentId, $userData, $studentData) {
        $db = Database::getInstance()->getConnection();

        $student = $this->model->findById($studentId);
        if (!$student) {
            throw new RuntimeException('Student not found.');
        }

        if (!empty($userData['username']) && $this->userRepo->usernameExists($userData['username'], $student['user_id'])) {
            throw new RuntimeException("Username '{$userData['username']}' is already taken.");
        }
        if (!empty($userData['email']) && $this->userRepo->emailExists($userData['email'], $student['user_id'])) {
            throw new RuntimeException("Email '{$userData['email']}' is already registered.");
        }
        if (!empty($studentData['student_code']) && $this->studentCodeExists($studentData['student_code'], $studentId)) {
            throw new RuntimeException("Student code '{$studentData['student_code']}' already exists.");
        }

        $db->beginTransaction();
        try {
            // An empty password means "leave the current one alone".
            if (array_key_exists('password', $userData) && ($userData['password'] === '' || $userData['password'] === null)) {
                unset($userData['password']);
            }

            if (!empty($userData)) {
                $this->userRepo->update($student['user_id'], $userData);
            }

            if (!empty($studentData)) {
                $this->model->update($studentId, $studentData);
            }

            $db->commit();
            return true;
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function deleteWithUser($studentId) {
        $db = Database::getInstance()->getConnection();

        $student = $this->model->findById($studentId);
        if (!$student) {
            return false;
        }

        $db->beginTransaction();
        try {
            $this->model->delete($studentId);

            if (!empty($student['user_id'])) {
                $this->userRepo->delete($student['user_id']);
            }

            $db->commit();
            return true;
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function getStudentDashboardData($userId) {
        $student = $this->model->findByUserId($userId);
        if (!$student) {
            return false;
        }

        $data = [
            'student' => $this->model->findWithDetails($student['id'])
        ];

        $assignmentModel = new Assignment();
        $data['recent_assignments'] = array_slice($assignmentModel->findForStudent($student['id']), 0, 5);

        $gradeModel = new Grade();
        $data['grades'] = $gradeModel->findByStudentId($student['id']);
        $data['average'] = $gradeModel->getAverageByStudent($student['id']);

        return $data;
    }
}