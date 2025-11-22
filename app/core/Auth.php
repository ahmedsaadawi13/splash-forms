<?php
// FILE: /app/core/Auth.php

class Auth {
    private static $user = null;

    public static function attempt($email, $password, $tenantId = null) {
        $db = Database::getInstance();

        $sql = "SELECT * FROM users WHERE email = ?";
        $params = [$email];

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = ?";
            $params[] = $tenantId;
        }

        $sql .= " AND is_active = 1 LIMIT 1";

        $user = $db->fetchOne($sql, $params);

        if ($user && password_verify($password, $user['password'])) {
            self::login($user);

            $db->update('users', ['last_login_at' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);

            return true;
        }

        return false;
    }

    public static function login($user) {
        Session::set('user_id', $user['id']);
        Session::set('tenant_id', $user['tenant_id']);
        Session::set('user_role', $user['role']);
        Session::regenerate();
        self::$user = $user;
    }

    public static function logout() {
        Session::destroy();
        self::$user = null;
    }

    public static function check() {
        return Session::has('user_id');
    }

    public static function guest() {
        return !self::check();
    }

    public static function user() {
        if (self::$user === null && self::check()) {
            $db = Database::getInstance();
            $userId = Session::get('user_id');
            self::$user = $db->fetchOne("SELECT * FROM users WHERE id = ? LIMIT 1", [$userId]);
        }
        return self::$user;
    }

    public static function id() {
        return Session::get('user_id');
    }

    public static function tenantId() {
        return Session::get('tenant_id');
    }

    public static function role() {
        return Session::get('user_role');
    }

    public static function isPlatformAdmin() {
        return self::role() === 'platform_admin';
    }

    public static function isTenantAdmin() {
        return self::role() === 'tenant_admin';
    }

    public static function isStaff() {
        return in_array(self::role(), ['staff', 'tenant_admin', 'platform_admin']);
    }

    public static function can($permission) {
        $role = self::role();

        if ($role === 'platform_admin') {
            return true;
        }

        $permissions = [
            'tenant_admin' => ['manage_forms', 'manage_users', 'view_submissions', 'manage_settings', 'manage_webhooks'],
            'staff' => ['manage_forms', 'view_submissions'],
            'user' => ['view_forms']
        ];

        return isset($permissions[$role]) && in_array($permission, $permissions[$role]);
    }

    public static function authorize($permission) {
        if (!self::can($permission)) {
            http_response_code(403);
            die('Forbidden');
        }
    }

    public static function requireAuth() {
        if (self::guest()) {
            Session::setFlash('error', 'Please login to continue');
            header('Location: /login');
            exit;
        }
    }

    public static function requireRole($role) {
        self::requireAuth();

        if (self::role() !== $role && !self::isPlatformAdmin()) {
            http_response_code(403);
            die('Forbidden');
        }
    }
}
