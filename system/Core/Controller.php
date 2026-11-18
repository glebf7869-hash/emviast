<?php
declare(strict_types=1);

abstract class Controller
{
    protected function view(string $template, array $data = [], string $layout = 'app'): void
    {
        View::render($template, $data, $layout);
    }

    protected function redirect(string $path): never
    {
        redirect($path);
    }

    protected function user(): array
    {
        return Auth::user() ?? [];
    }

    protected function guardCsrf(): void
    {
        csrf_check_or_fail();
    }

    protected function guardAuth(): void
    {
        if (!Auth::check()) {
            flash_set('error', 'Войдите в аккаунт, чтобы продолжить.');
            redirect('/login');
        }
    }

    protected function guardRole(array $roles): void
    {
        $this->guardAuth();
        if (!in_array($this->user()['role'], $roles, true)) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Доступ закрыт'], 'app');
            exit;
        }
    }

    protected function ok(string $message): void
    {
        flash_set('success', $message);
    }

    protected function fail(string $message): never
    {
        flash_set('error', $message);
        back();
    }

    protected function input(string $key, string $default = ''): string
    {
        return trim((string)($_POST[$key] ?? $default));
    }

    protected function intInput(string $key, int $default = 0): int
    {
        return (int)($_POST[$key] ?? $default);
    }

    protected function errors(array $errors): never
    {
        $_SESSION['_old'] = $_POST;
        flash_set('error', implode(' ', $errors));
        back();
    }

    protected function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }
}
