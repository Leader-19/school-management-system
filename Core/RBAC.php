<?php
class RBAC {
    public static function loadPermissions($userId) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT p.name FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                JOIN users u ON rp.role_id = u.role_id
                WHERE u.id = :user_id";
                
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return $permissions ?: [];
    }
    
    public static function checkAccess($permission) {
        return Auth::hasPermission($permission);
    }
    
    public static function requirePermission($permission) {
        if (!self::checkAccess($permission)) {
            Session::setFlash('error', 'You do not have permission to access this resource.');
            header("Location: " . BASE_URL . "/dashboard");
            exit();
        }
    }
}
