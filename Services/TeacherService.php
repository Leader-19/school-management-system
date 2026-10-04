<?php

class TeacherService {
    private $userRepo;
    private $userModel;

    /** roles.name of the teacher role (seeded by database/schema.sql). */
    private const ROLE_TEACHER = 'teacher';

    public function __construct() {
        $this->userRepo  = new UserRepository();
        $this->userModel = new User();
    }

    /**
     * One page of the teacher list plus totals for the paginator.
     */
    public function paginateTeachers($page = 1, $perPage = 15, $keyword = null) {
        return $this->userModel->paginateTeachers($page, $perPage, $keyword);
    }

    public function getTeacherById($id) {
        return $this->userModel->findWithRole($id);
    }

    /**
     * Create a teacher login account.
     *
     * @throws RuntimeException on validation failure or duplicate username/email.
     */
    public function createTeacher($data) {
        $payload = $this->buildData($data);

        $error = $this->validate($payload, null);
        if ($error !== null) {
            throw new RuntimeException($error);
        }

        if ($this->userRepo->usernameExists($payload['username'])) {
            throw new RuntimeException("Username '{$payload['username']}' is already taken.");
        }
        if ($this->userRepo->emailExists($payload['email'])) {
            throw new RuntimeException("Email '{$payload['email']}' is already registered.");
        }

        $roleId = $this->userModel->findRoleIdByName(self::ROLE_TEACHER);
        if ($roleId === null) {
            throw new RuntimeException("The 'teacher' role is missing. Run the database seed script first.");
        }

        $payload['role_id'] = $roleId;
        $userId = $this->userRepo->create($payload);

        if (!$userId) {
            throw new RuntimeException('Failed to create the teacher account.');
        }

        return $userId;
    }

    /**
     * Update a teacher's profile; empty password keeps the current one.
     *
     * @throws RuntimeException on validation failure or duplicate username/email.
     */
    public function updateTeacher($id, $data) {
        $teacher = $this->getTeacherById($id);

        if (!$teacher || ($teacher['role_name'] ?? '') !== self::ROLE_TEACHER) {
            throw new RuntimeException('Teacher not found.');
        }

        $payload = $this->buildData($data);

        $error = $this->validate($payload, $id);
        if ($error !== null) {
            throw new RuntimeException($error);
        }

        if ($this->userRepo->usernameExists($payload['username'], $id)) {
            throw new RuntimeException("Username '{$payload['username']}' is already taken.");
        }
        if ($this->userRepo->emailExists($payload['email'], $id)) {
            throw new RuntimeException("Email '{$payload['email']}' is already registered.");
        }

        if ((string) ($payload['password'] ?? '') === '') {
            unset($payload['password']); // Keep the existing password.
        }

        if (!$this->userRepo->update($id, $payload)) {
            throw new RuntimeException('Failed to update the teacher account.');
        }

        return true;
    }

    /**
     * Remove the login account. Homeroom assignments (classes.teacher_id) are
     * cleared automatically by the FK (ON DELETE SET NULL).
     *
     * @throws RuntimeException when the id is not a teacher account.
     */
    public function deleteTeacher($id) {
        $teacher = $this->getTeacherById($id);

        if (!$teacher || ($teacher['role_name'] ?? '') !== self::ROLE_TEACHER) {
            throw new RuntimeException('Teacher not found.');
        }

        return $this->userRepo->delete($id);
    }

    /**
     * Map the form onto the users table columns (minus role_id, added on create).
     */
    private function buildData($data) {
        return [
            'username' => trim((string) ($data['username'] ?? '')),
            'email'    => trim((string) ($data['email'] ?? '')),
            'password' => (string) ($data['password'] ?? ''),
            'status'   => in_array($data['status'] ?? '', ['active', 'inactive'], true)
                            ? $data['status']
                            : 'active'
        ];
    }

    /**
     * Field-level validation shared by create and update. On update an empty
     * password is allowed (means "keep the current one").
     */
    private function validate($data, $teacherId = null) {
        if ($data['username'] === '') {
            return 'Username is required.';
        }
        if (strlen($data['username']) < 3) {
            return 'Username must be at least 3 characters long.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return 'A valid email address is required.';
        }
        if ($teacherId === null || $data['password'] !== '') {
            if ($data['password'] === '') {
                return 'Password is required.';
            }
            if (strlen($data['password']) < 6) {
                return 'Password must be at least 6 characters long.';
            }
        }

        return null;
    }
}
