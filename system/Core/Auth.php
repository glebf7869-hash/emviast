<?php
declare(strict_types=1);

final class Auth
{
    private static ?array $user = null;
    private static bool $resolved = false;

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int)$user['id'] : null;
    }

    public static function user(): ?array
    {
        if (self::$resolved) {
            return self::$user;
        }
        self::$resolved = true;

        $id = (int)($_SESSION['user_id'] ?? 0);
        if ($id <= 0) {
            return null;
        }

        $user = Database::fetch(
            'SELECT id, role, login, email, first_name, last_name, timezone, status, must_change_password
             FROM users WHERE id = ?',
            [$id]
        );

        if ($user === null || $user['status'] !== 'active') {
            unset($_SESSION['user_id']);
            return null;
        }

        self::$user = $user;
        return $user;
    }

    public static function attempt(string $login, string $password): bool
    {
        $user = Database::fetch(
            'SELECT * FROM users WHERE login = ? OR email = ? LIMIT 1',
            [$login, $login]
        );

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        if ($user['status'] !== 'active') {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        self::$user = $user;
        self::$resolved = true;

        Database::update('users', ['last_login_at' => date('Y-m-d H:i:s')], 'id = ?', [(int)$user['id']]);
        Activity::log('auth.login', 'users', (int)$user['id']);

        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();
        self::$user = null;
        self::$resolved = true;
    }

    public static function passwordHash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
