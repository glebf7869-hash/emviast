<?php
declare(strict_types=1);

final class Router
{
    /** @var array<int, array{method:string, regex:string, handler:array}> */
    private array $routes = [];

    public function get(string $pattern, array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, array $handler): void
    {
        $regex = '#^' . preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $pattern) . '$#u';
        $this->routes[] = [
            'method' => $method,
            'regex' => $regex,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $path): void
    {
        $path = '/' . trim($path, '/');
        if ($path === '/') {
            $path = '/';
        }

        $pathMatched = false;
        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }
            $pathMatched = true;
            if ($route['method'] !== $method) {
                continue;
            }
            array_shift($matches);
            [$class, $action] = $route['handler'];
            $controller = new $class();
            $controller->{$action}(...$matches);
            return;
        }

        http_response_code($pathMatched ? 405 : 404);
        $controller = new ErrorController();
        $pathMatched ? $controller->methodNotAllowed() : $controller->notFound();
    }
}
