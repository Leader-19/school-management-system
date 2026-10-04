<?php
class Controller {
    protected function view($viewName, $data = []) {
        $this->renderView($viewName, $data);
    }

    /**
     * Render a view inside the main layout.
     *
     * Public so the router can render an error view returned by middleware.
     */
    public function renderView($viewName, $data = []) {
        extract($data, EXTR_SKIP);
        $viewFile = BASE_PATH . '/Views/' . $viewName . '.php';
        $layoutFile = BASE_PATH . '/Views/layouts/main.php';

        if (!file_exists($viewFile)) {
            die("View {$viewName} not found.");
        }

        ob_start();
        require $viewFile;
        $viewContent = ob_get_clean();

        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $viewContent;
        }
    }

    protected function redirect($url) {
        $target = preg_match('#^https?://#i', (string) $url)
            ? $url
            : BASE_URL . '/' . ltrim($url, '/');

        header('Location: ' . $target);
        exit();
    }

    protected function checkPermission($permission) {
        if (!RBAC::checkAccess($permission)) {
            Session::setFlash('error', 'You do not have permission to access this page.');
            $this->redirect('/dashboard');
        }
    }

    protected function isLoggedIn() {
        return Auth::check();
    }

    protected function jsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
}