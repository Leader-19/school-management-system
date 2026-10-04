<?php

class UserRepository {
    private $model;

    public function __construct() {
        $this->model = new User();
    }

    public function findAll() {
        return $this->model->findAll();
    }

    public function findAllWithRoles() {
        return $this->model->findAllWithRoles();
    }

    public function findAllTeachers() {
        return $this->model->findAllTeachers();
    }

    public function findById($id) {
        return $this->model->findById($id);
    }

    public function findByUsername($username) {
        return $this->model->findByUsername($username);
    }

    public function findByEmail($email) {
        return $this->model->findByEmail($email);
    }

    public function findByLogin($login) {
        return $this->model->findByLogin($login);
    }

    public function usernameExists($username, $excludeId = null) {
        return $this->model->existsBy('username', $username, $excludeId);
    }

    public function emailExists($email, $excludeId = null) {
        return $this->model->existsBy('email', $email, $excludeId);
    }

    public function findWithRole($id) {
        return $this->model->findWithRole($id);
    }

    /**
     * Hash the password (when given) and insert the account.
     *
     * Callers must pass the PLAIN TEXT password. Hashing lives here so that
     * there is exactly one hashing site per write path - hashing in both the
     * service and the repository is what silently broke student logins.
     */
    public function create($data) {
        return $this->model->create($this->withPlainPassword($data));
    }

    public function update($id, $data) {
        if (isset($data['password'])) {
            if ($data['password'] === '' || $data['password'] === null) {
                unset($data['password']);
            } else {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
        }
        return $this->model->update($id, $data);
    }

    public function setPassword($userId, $plainPassword) {
        return $this->model->update($userId, [
            'password' => password_hash($plainPassword, PASSWORD_DEFAULT)
        ]);
    }

    public function delete($id) {
        return $this->model->delete($id);
    }

    /**
     * Verify a login attempt.
     *
     * password_verify() only works against a bcrypt/argon hash, so any row
     * whose password was written as plain text (a manual SQL INSERT, an old
     * export, ...) could never sign in. Those rows are accepted here once and
     * then transparently re-hashed so they behave like every other account.
     */
    public function authenticate($login, $password) {
        if ($login === '' || $password === '') {
            return false;
        }

        $user = $this->model->findByLogin($login);
        if (!$user) {
            return false;
        }

        $stored = (string) $user['password'];

        if ($stored !== '' && password_get_info($stored)['algo'] !== null) {
            if (!password_verify($password, $stored)) {
                return false;
            }

            if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                $this->setPassword($user['id'], $password);
            }

            return $user;
        }

        if ($stored !== '' && hash_equals($stored, $password)) {
            $this->setPassword($user['id'], $password);
            return $this->model->findById($user['id']);
        }

        return false;
    }

    /**
     * Keep only the fields that belong on the users table.
     */
    private function withPlainPassword($data) {
        $userData = [
            'username' => $data['username'] ?? null,
            'email'    => $data['email'] ?? null,
            'role_id'  => $data['role_id'] ?? null,
            'status'   => $data['status'] ?? 'active',
        ];

        if (isset($data['password'])) {
            $userData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        return $userData;
    }
}