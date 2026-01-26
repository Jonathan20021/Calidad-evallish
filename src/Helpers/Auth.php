<?php

namespace App\Helpers;

class Auth
{
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
}
