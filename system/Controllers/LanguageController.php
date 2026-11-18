<?php
declare(strict_types=1);

final class LanguageController extends Controller
{
    public function switch(string $code): void
    {
        if (in_array($code, ['ru', 'en'], true)) {
            $_SESSION['lang'] = $code;
        }

        $referer = (string)($_SERVER['HTTP_REFERER'] ?? '');
        if ($referer !== '' && str_starts_with($referer, 'http')) {
            header('Location: ' . $referer);
            exit;
        }

        $this->redirect('/');
    }
}
