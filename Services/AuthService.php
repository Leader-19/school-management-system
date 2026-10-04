<?php

class AuthService {
    private $userRepo;

    /** @var string|null Set by login() to explain *why* a login failed. */
    private $error = null;

    public function __construct() {
        $this->userRepo = new UserRepository();
    }

    /**
     * @param string $login    Username or email address.
     * @param string $password Plain text password.
     */
    public function login($login, $password) {
        $this->error = null;

        $login = trim((string) $login);
        $password = (string) $password;

        if ($login === '' || $password === '') {
            $this->error = 'Please enter both a username and a password.';
            return false;
        }

        $user = $this->userRepo->authenticate($login, $password);

        if (!$user) {
            $this->error = 'Invalid username or password.';
            return false;
        }

        if (($user['status'] ?? 'active') !== 'active') {
            $this->error = 'This account has been deactivated. Please contact the administrator.';
            return false;
        }

        if (Auth::userId() !== null) {
            Session::destroy();
        }

        $userWithRole = $this->userRepo->findWithRole($user['id']);

        if (!$userWithRole) {
            $this->error = 'This account is not linked to a valid role.';
            return false;
        }

        // Never keep the password hash in the session.
        unset($userWithRole['password']);

        $permissions = RBAC::loadPermissions($user['id']);
        Auth::login($userWithRole, $permissions);

        // New session token set: any pre-login CSRF token is stale.
        CsrfMiddleware::regenerate();

        return true;
    }

    public function logout() {
        Auth::logout();
    }

    public function getCurrentUser() {
        return Auth::user();
    }

    public function getError() {
        return $this->error;
    }
}