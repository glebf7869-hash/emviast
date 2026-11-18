<?php
declare(strict_types=1);

require __DIR__ . '/system/bootstrap.php';

// Автопереключение на ссылки без mod_rewrite (?style=query).
if (($_GET['style'] ?? '') === 'query') {
    $_SESSION['_url_style'] = 'query';
}

$router = new Router();
require __DIR__ . '/system/routes.php';

// 1. Маршрут из ?r= (режим без mod_rewrite: /index.php?r=/about)
$routeParam = $_GET['r'] ?? null;

if (is_string($routeParam) && $routeParam !== '') {
    $path = '/' . ltrim(rawurldecode($routeParam), '/');
} else {
    $requestPath = (string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

    // 2. Сайт может лежать в подпапке: /emvia, /site, папка с пробелом и т.п.
    $basePath = rawurldecode(base_path());
    $decodedPath = rawurldecode($requestPath);
    if ($basePath !== '' && ($decodedPath === $basePath || str_starts_with($decodedPath, $basePath . '/'))) {
        $decodedPath = substr($decodedPath, strlen($basePath));
    }

    // 3. Если хостинг открыл сайт как /index.php — считаем это главной.
    $path = preg_replace('#/index\.php$#', '/', $decodedPath) ?: '/';
}

if ($path === '') {
    $path = '/';
}

$GLOBALS['current_path'] = $path;

$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$router->dispatch($method, $path);
