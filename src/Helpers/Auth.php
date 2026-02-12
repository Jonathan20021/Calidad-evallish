<?php

namespace App\Helpers;

use App\Models\QaPermission;
use App\Models\UserPermission;
use App\Models\User;

class Auth
{
    private static $qaPermissionsCache = null;
    private static $userPermissionsCache = [];

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

        self::syncPoncheUsersIfNeeded();

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
        $source = $user['source'] ?? 'quality';
        $userId = (int) ($user['id'] ?? 0);

        if ($role === 'admin') {
            return true;
        }

        // Para usuarios de ponche (excepto agents), verificar permisos individuales
        if ($source === 'ponche' && $role !== 'agent') {
            $userPermissions = self::getUserPermissions($userId);
            if ($userPermissions !== null) {
                return self::checkUserPermission($permission, $userPermissions);
            }
            // Si no tiene permisos configurados, denegar acceso por defecto
            return false;
        }

        if ($role === 'agent') {
            // Agents can only view their own training and evaluations (enforced in controllers/views)
            return in_array($permission, ['training.view', 'evaluations.view'], true);
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
            case 'campaigns.view':
            case 'campaigns.manage':
            case 'evaluations.view':
            case 'evaluations.create':
            case 'reports.view':
            case 'settings.manage':
            case 'training.view':
            case 'training.manage':
            case 'agents.view':
            case 'agents.manage':
            case 'forms.view':
            case 'forms.manage':
            case 'ai_criteria.view':
            case 'ai_criteria.manage':
            case 'calls.view':
            case 'calls.manage':
                return true;
            default:
                return false;
        }
    }

    private static function checkUserPermission(string $permission, array $userPermissions): bool
    {
        switch ($permission) {
            case 'users.view':
                return $userPermissions['can_view_users'] === 1;
            case 'users.create':
                return $userPermissions['can_create_users'] === 1;
            case 'clients.view':
                return $userPermissions['can_view_clients'] === 1;
            case 'clients.manage':
                return $userPermissions['can_manage_clients'] === 1;
            case 'campaigns.view':
                return $userPermissions['can_view_campaigns'] === 1;
            case 'campaigns.manage':
                return $userPermissions['can_manage_campaigns'] === 1;
            case 'evaluations.view':
                return $userPermissions['can_view_evaluations'] === 1;
            case 'evaluations.create':
                return $userPermissions['can_create_evaluations'] === 1;
            case 'reports.view':
                return $userPermissions['can_view_reports'] === 1;
            case 'settings.manage':
                return $userPermissions['can_manage_settings'] === 1;
            case 'training.view':
                return $userPermissions['can_view_training'] === 1;
            case 'training.manage':
                return $userPermissions['can_manage_training'] === 1;
            case 'agents.view':
                return $userPermissions['can_view_agents'] === 1;
            case 'agents.manage':
                return $userPermissions['can_manage_agents'] === 1;
            case 'forms.view':
                return $userPermissions['can_view_forms'] === 1;
            case 'forms.manage':
                return $userPermissions['can_manage_forms'] === 1;
            case 'ai_criteria.view':
                return $userPermissions['can_view_ai_criteria'] === 1;
            case 'ai_criteria.manage':
                return $userPermissions['can_manage_ai_criteria'] === 1;
            case 'calls.view':
                return $userPermissions['can_view_calls'] === 1;
            case 'calls.manage':
                return $userPermissions['can_manage_calls'] === 1;
            default:
                return false;
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

    private static function getUserPermissions(int $userId): ?array
    {
        if (!isset(self::$userPermissionsCache[$userId])) {
            $model = new UserPermission();
            self::$userPermissionsCache[$userId] = $model->getByUserId($userId);
        }
        return self::$userPermissionsCache[$userId];
    }

    private static function syncPoncheUsersIfNeeded(): void
    {
        $lastSync = $_SESSION['ponche_sync_at'] ?? 0;
        $now = time();
        if ($now - (int) $lastSync < 300) {
            return;
        }

        try {
            $userModel = new User();
            $userModel->syncPoncheUsersByRoles(['agent', 'qa'], false);
            $_SESSION['ponche_sync_at'] = $now;
        } catch (\Throwable $e) {
            return;
        }
    }
}
