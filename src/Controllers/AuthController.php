<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\PoncheUser;
use App\Helpers\Auth;
use App\Config\Config;

class AuthController
{

    public function showLogin()
    {
        if (Auth::check()) {
            $role = Auth::user()['role'] ?? 'admin';
            $redirect = $role === 'client' ? 'client-portal' : 'dashboard';
            header('Location: ' . Config::BASE_URL . $redirect);
            exit;
        }

        // Simple view rendering
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function login()
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $userModel = new User();
        $user = $userModel->findByUsername($username);

        if ($user && $userModel->verifyPassword($password, $user['password_hash'])) {
            Auth::login($user);
            $redirect = $user['role'] === 'client' ? 'client-portal' : 'dashboard';
            header('Location: ' . Config::BASE_URL . $redirect);
            exit;
        }

        $poncheUserModel = new PoncheUser();
        $poncheUser = $poncheUserModel->findByUsername($username);
        if ($poncheUser && $poncheUserModel->verifyPassword($password, $poncheUser['password'])) {
            Auth::login($poncheUserModel->toSessionUser($poncheUser));
            header('Location: ' . Config::BASE_URL . 'dashboard');
            exit;
        }

        $error = "Credenciales inválidas. Por favor intente de nuevo.";
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function logout()
    {
        Auth::logout();
        header('Location: ' . Config::BASE_URL . 'login');
        exit;
    }
}
