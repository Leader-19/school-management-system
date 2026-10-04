<?php
class Auth {
    public static function check() {
        return Session::has('user');
    }
    
    public static function user() {
        return Session::get('user');
    }
    
    public static function login($user, $permissions) {
        Session::set('user', $user);
        Session::set('permissions', $permissions);
    }
    
    public static function logout() {
        Session::remove('user');
        Session::remove('permissions');
        Session::destroy();
    }
    
    public static function hasPermission($permission) {
        $permissions = Session::get('permissions', []);
        return in_array($permission, $permissions);
    }
    
    public static function hasRole($roleName) {
        $user = self::user();
        return isset($user['role_name']) && $user['role_name'] === $roleName;
    }
    
    public static function userId() {
        $user = self::user();
        return isset($user['id']) ? $user['id'] : null;
    }
    
    public static function userRole() {
        $user = self::user();
        return isset($user['role_id']) ? $user['role_id'] : null;
    }
}
