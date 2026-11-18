<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/system');
define('VIEW_PATH', BASE_PATH . '/views');
define('STORAGE_PATH', BASE_PATH . '/storage');

$GLOBALS['config'] = require BASE_PATH . '/config/config.php';

/**
 * Доступ к настройкам по ключу с точкой: config('db.host').
 */
function config(string $key, mixed $default = null): mixed
{
    $value = $GLOBALS['config'];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

spl_autoload_register(static function (string $class): void {
    foreach (['/Core/', '/Controllers/'] as $dir) {
        $file = APP_PATH . $dir . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

require APP_PATH . '/Core/helpers.php';

date_default_timezone_set((string)config('app.timezone', 'Europe/Moscow'));

if (config('app.debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', STORAGE_PATH . '/logs/php-error.log');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_name((string)config('app.session_name', 'emvia_session'));
    session_start();
}

// Одноразовые старые значения форм живут один запрос.
register_shutdown_function(static function (): void {
    unset($_SESSION['_old']);
});
