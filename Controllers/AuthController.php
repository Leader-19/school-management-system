<?php

class AuthController extends Controller {
    private $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    public function index() {
        // Only bounce logged-in users to the dashboard when their session
        // has a valid role; otherwise always show the login page. This
        // prevents infinite /auth <-> /dashboard redirect loops with
        // stale or corrupted sessions.
        if ($this->isLoggedIn()
            && (Auth::hasRole('admin') || Auth::hasRole('teacher') || Auth::hasRole('student'))) {
            $this->redirect('dashboard');
        }
        $this->view('auth/login');
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('login');
        }

        $login = $_POST['login'] ?? ($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($this->authService->login($login, $password)) {
            $this->redirect('dashboard');
        }

        Session::setFlash('error', $this->authService->getError() ?: 'Invalid username or password.');
        $this->redirect('login');
    }

    public function logout() {
        $this->authService->logout();
        $this->redirect('login');
    }
}