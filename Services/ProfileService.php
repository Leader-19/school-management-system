<?php

class ProfileService {
    private $userRepo;

    public function __construct() {
        $this->userRepo = new UserRepository();
    }

    public function getProfile($userId) {
        $user = $this->userRepo->findWithRole($userId);
        if (!$user) {
            throw new RuntimeException('Your account could not be found.');
        }
        unset($user['password']);
        return $user;
    }

    public function updateProfile($userId, array $input) {
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            throw new RuntimeException('Your account could not be found.');
        }

        $username = trim((string) ($input['username'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $newPassword = (string) ($input['new_password'] ?? '');
        $currentPassword = (string) ($input['current_password'] ?? '');
        $confirmPassword = (string) ($input['confirm_password'] ?? '');

        if (strlen($username) < 3 || strlen($username) > 50) {
            throw new RuntimeException('Username must be between 3 and 50 characters.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Please enter a valid email address.');
        }
        if ($this->userRepo->usernameExists($username, $userId)) {
            throw new RuntimeException('That username is already in use.');
        }
        if ($this->userRepo->emailExists($email, $userId)) {
            throw new RuntimeException('That email address is already in use.');
        }

        $changes = ['username' => $username, 'email' => $email];
        $passwordFieldsUsed = $newPassword !== '' || $currentPassword !== '' || $confirmPassword !== '';
        if ($passwordFieldsUsed) {
            if ($newPassword === '' || $currentPassword === '' || $confirmPassword === '') {
                throw new RuntimeException('To change your password, complete all three password fields.');
            }
            if (strlen($newPassword) < 6) {
                throw new RuntimeException('The new password must be at least 6 characters.');
            }
            if ($newPassword !== $confirmPassword) {
                throw new RuntimeException('The new password and confirmation do not match.');
            }
            if (!$this->userRepo->authenticate((string) $user['username'], $currentPassword)) {
                throw new RuntimeException('Enter your current password to set a new password.');
            }
            $changes['password'] = $newPassword;
        }

        if (!$this->userRepo->update($userId, $changes)) {
            throw new RuntimeException('Your profile could not be updated. Please try again.');
        }

        $updated = $this->userRepo->findWithRole($userId);
        unset($updated['password']);
        Session::set('user', $updated);
        return $updated;
    }
}
