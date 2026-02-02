<?php

namespace App\Helpers;

use App\Models\QaPermission;

class Auth
{
    private static $qaPermissionsCache = null;

    public static function check()
    {
        return isset($_SESSION['user_id']);
    }

    public static function user()
    {
        return $_SESSION['user'] ?? null;
    }

    public static function login($user)
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = $user;
    }

    public static function logout()
    {
        session_destroy();
    }

    public static function requireAuth()
    {
        if (!self::check()) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'login');
            exit;
        }

        if ((self::user()['role'] ?? '') === 'client') {
            header('Location: ' . \App\Config\Config::BASE_URL . 'client-portal');
            exit;
        }
    }

    public static function requireRole($role)
    {
        self::requireAuth();
        if (self::user()['role'] !== $role) {
            http_response_code(403);
            die('Access denied');
        }
    }

    public static function hasPermission(string $permission): bool
    {
        $user = self::user();
        $role = $user['role'] ?? '';

        if ($role === 'admin') {
            return true;
        }

        if ($role !== 'qa') {
            return false;
        }

        $permissions = self::getQaPermissions();

        switch ($permission) {
            case 'users.view':
                return $permissions['can_view_users'] === 1;
            case 'users.create':
                return $permissions['can_create_users'] === 1;
            case 'clients.view':
                return $permissions['can_view_clients'] === 1;
            case 'clients.manage':
                return $permissions['can_manage_clients'] === 1;
            default:
                return true;
        }
    }

    public static function requirePermission(string $permission)
    {
        self::requireAuth();

        if (!self::hasPermission($permission)) {
            http_response_code(403);
            die('Access denied');
        }
    }

    public static function requireAnyRole(array $roles)
    {
        if (!self::check()) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'login');
            exit;
        }

        if (!in_array(self::user()['role'] ?? '', $roles, true)) {
            http_response_code(403);
            die('Access denied');
        }
    }

    private static function getQaPermissions(): array
    {
        if (self::$qaPermissionsCache === null) {
            $model = new QaPermission();
            self::$qaPermissionsCache = $model->get();
        }
        return self::$qaPermissionsCache;
    }
}
