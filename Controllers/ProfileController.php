<?php

class ProfileController extends Controller {
    private $profileService;

    public function __construct() {
        $this->profileService = new ProfileService();
    }

    public function index() {
        $userId = Auth::userId();
        try {
            $profile = $this->profileService->getProfile($userId);
        } catch (Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            $this->redirect('dashboard');
        }

        $this->view('profile/index', [
            'profile' => $profile,
            'data' => ['username' => $profile['username'] ?? '', 'email' => $profile['email'] ?? ''],
            'errors' => []
        ]);
    }

    public function update() {
        $userId = Auth::userId();
        $data = [
            'username' => trim((string) ($_POST['username'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? ''))
        ];

        try {
            $profile = $this->profileService->updateProfile($userId, $_POST);
            Session::setFlash('success', 'Your profile has been updated.');
            $this->redirect('profile');
        } catch (Throwable $e) {
            try {
                $profile = $this->profileService->getProfile($userId);
            } catch (Throwable $ignored) {
                $this->redirect('dashboard');
            }
            $this->view('profile/index', ['profile' => $profile, 'data' => $data, 'errors' => [$e->getMessage()]]);
        }
    }
}
