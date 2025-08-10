<?php

namespace Core\Auth;

class Auth
{
    private $user = null;

    public function __construct()
    {
        $this->check();
    }

    public function check(): bool
    {
        // Session'ı başlat
        \Core\Session::start();
        
        if (isset($_SESSION['user_id'])) {
            // Session'dan kullanıcı bilgilerini al
            if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
                $this->user = $_SESSION['user'];
            } else {
                // Fallback: Basit kullanıcı bilgileri
                $this->user = [
                    'user_id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username'] ?? 'User',
                    'email' => $_SESSION['email'] ?? '',
                    'role' => $_SESSION['role'] ?? 'user'
                ];
            }
            return true;
        }
        return false;
    }

    public function user()
    {
        return $this->user;
    }

    public function login($credentials): bool
    {
        // Basit login implementasyonu
        // Gerçek uygulamada veritabanı kontrolü yapılır
        if ($credentials['username'] === 'admin' && $credentials['password'] === 'password') {
            $_SESSION['user_id'] = 1;
            $_SESSION['username'] = 'admin';
            $_SESSION['email'] = 'admin@example.com';
            $_SESSION['role'] = 'admin';
            $this->user = [
                'user_id' => 1,
                'username' => 'admin',
                'email' => 'admin@example.com',
                'role' => 'admin'
            ];
            return true;
        }
        return false;
    }

    public function logout(): void
    {
        unset($_SESSION['user_id']);
        unset($_SESSION['user']);
        unset($_SESSION['username']);
        unset($_SESSION['email']);
        unset($_SESSION['role']);
        $this->user = null;
    }

    public function can(string $permission): bool
    {
        // Basit permission kontrolü
        return $this->user && $this->user['role'] === 'admin';
    }

    public function hasRole(string $role): bool
    {
        return $this->user && $this->user['role'] === $role;
    }

    public function hasPermission(string $permission): bool
    {
        return $this->can($permission);
    }
}
