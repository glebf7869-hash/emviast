<?php
declare(strict_types=1);

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return (string)$_SESSION['_csrf'];
    }

    public static function check(?string $token): bool
    {
        return is_string($token)
            && $token !== ''
            && !empty($_SESSION['_csrf'])
            && hash_equals((string)$_SESSION['_csrf'], $token);
    }
}
