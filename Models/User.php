<?php

class User extends Model {
    protected $table = 'users';

    protected $fillable = ['username', 'email', 'password', 'role_id', 'status'];

    public function findByUsername($username) {
        return $this->query("SELECT * FROM {$this->table} WHERE username = ?", [$username])->fetch();
    }

    public function findByEmail($email) {
        return $this->query("SELECT * FROM {$this->table} WHERE email = ?", [$email])->fetch();
    }

    /**
     * Allow signing in with either the username or the email address.
     */
    public function findByLogin($login) {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE username = ? OR email = ? LIMIT 1",
            [$login, $login]
        )->fetch();
    }

    public function findWithRole($id) {
        $sql = "SELECT u.*, r.name as role_name
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.id = ?";
        return $this->query($sql, [$id])->fetch();
    }

    public function findAllWithRoles() {
        $sql = "SELECT u.*, r.name as role_name
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                ORDER BY u.id DESC";
        return $this->query($sql)->fetchAll();
    }

    /**
     * Accounts eligible to be assigned as a homeroom teacher.
     */
    public function findAllTeachers() {
        $sql = "SELECT u.id, u.username, u.email
                FROM {$this->table} u
                JOIN roles r ON u.role_id = r.id
                WHERE r.name = 'teacher' AND u.status = 'active'
                ORDER BY u.username ASC";
        return $this->query($sql)->fetchAll();
    }

    public function countByRole($roleId) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE role_id = ?";
        return (int) $this->query($sql, [$roleId])->fetch()['total'];
    }

    /**
     * Server-side pagination over teacher accounts, optionally filtered by a
     * keyword (username or email).
     *
     * @return array{rows: array, total: int, page: int, per_page: int}
     */
    public function paginateTeachers($page = 1, $perPage = 15, $keyword = null) {
        $perPage = max(1, (int) $perPage);
        $page    = max(1, (int) $page);
        $offset  = ($page - 1) * $perPage;

        $where  = "WHERE r.name = 'teacher'";
        $params = [];
        $needle = '%' . trim((string) $keyword) . '%';
        if (trim((string) $keyword) !== '') {
            $where  .= ' AND (u.username LIKE ? OR u.email LIKE ?)';
            $params  = [$needle, $needle];
        }

        $total = (int) $this->query(
            "SELECT COUNT(*) AS total
             FROM {$this->table} u
             JOIN roles r ON u.role_id = r.id
             {$where}",
            $params
        )->fetch()['total'];

        $sql = "SELECT u.id, u.username, u.email, u.status, u.role_id, u.created_at,
                       r.name AS role_name,
                       (SELECT COUNT(*) FROM classes c WHERE c.teacher_id = u.id) AS class_count
                FROM {$this->table} u
                JOIN roles r ON u.role_id = r.id
                {$where}
                ORDER BY u.id DESC
                LIMIT {$perPage} OFFSET {$offset}";
        $rows = $this->query($sql, $params)->fetchAll();

        return [
            'rows'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage
        ];
    }

    /**
     * Id of a role by name, or null (used to attach the teacher role).
     */
    public function findRoleIdByName($name) {
        $row = $this->query('SELECT id FROM roles WHERE name = ?', [$name])->fetch();
        return $row ? (int) $row['id'] : null;
    }
}