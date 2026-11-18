<?php
declare(strict_types=1);

final class ErrorController extends Controller
{
    public function notFound(): void
    {
        http_response_code(404);
        $this->view('errors/404', ['title' => 'Страница не найдена'], Auth::check() ? 'app' : 'public');
    }

    public function methodNotAllowed(): void
    {
        http_response_code(405);
        $this->view('errors/405', ['title' => 'Метод не поддерживается'], Auth::check() ? 'app' : 'public');
    }
}
