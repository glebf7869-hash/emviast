<?php
declare(strict_types=1);

final class Mailer
{
    /**
     * Отправляет письмо или пишет его в лог (transport = log).
     * Возвращает true, если письмо передано транспорту.
     */
    public static function send(string $to, string $subject, string $text): bool
    {
        $fromEmail = (string)config('mail.from');
        $fromName = (string)config('mail.from_name');
        $transport = (string)config('mail.transport', 'log');

        $logLine = sprintf(
            "[%s] to: %s | subject: %s\n%s\n%s\n",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            str_repeat('-', 60),
            $text
        );
        @file_put_contents(STORAGE_PATH . '/logs/mail.log', $logLine, FILE_APPEND);

        if ($transport !== 'mail') {
            return true;
        }

        $headers = [
            'From' => sprintf('%s <%s>', '=?UTF-8?B?' . base64_encode($fromName) . '?=', $fromEmail),
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Transfer-Encoding' => '8bit',
        ];

        $headerLines = [];
        foreach ($headers as $key => $value) {
            $headerLines[] = $key . ': ' . $value;
        }

        return @mail(
            $to,
            '=?UTF-8?B?' . base64_encode($subject) . '?=',
            $text,
            implode("\r\n", $headerLines)
        );
    }

    public static function credentials(string $to, string $name, string $login, string $password): bool
    {
        $app = (string)config('app.name');
        $url = absolute_url('/login');
        $text = <<<TEXT
Здравствуйте, {$name}!

Для вас создан аккаунт в проекте {$app}.

Логин: {$login}
Пароль: {$password}

Войти: {$url}

Пожалуйста, смените пароль после первого входа в разделе «Профиль».
Если вы не подавали заявку, просто проигнорируйте письмо.
TEXT;
        return self::send($to, 'Доступ в ' . $app, $text);
    }
}
