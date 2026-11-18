<?php
declare(strict_types=1);

final class View
{
    public static function render(string $template, array $data = [], string $layout = 'public'): void
    {
        $data['title'] = $data['title'] ?? config('app.name');
        $data['content'] = self::capture($template, $data);
        extract($data, EXTR_SKIP);
        require VIEW_PATH . '/layouts/' . $layout . '.php';
    }

    public static function capture(string $template, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require VIEW_PATH . '/' . $template . '.php';
        return (string)ob_get_clean();
    }
}
