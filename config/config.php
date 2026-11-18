<?php
/**
 * Настройки проекта EMVIA Study.
 * После загрузки на хостинг заполните db и base_url.
 */
return [
    'app' => [
        'name' => 'EMVIA Study',
        'tagline' => 'Волонтёрская школа английского',
        // Пусто — путь определится сам (корень домена или подпапка).
        // Указывать нужно только если автоопределение не сработало: '/emvia'
        'base_url' => '',
        // Как строить ссылки:
        // query  — /index.php?r=/about — работает на любом хостинге (по умолчанию)
        // pretty — /about — только если точно работает mod_rewrite
        // auto   — попробовать pretty и переключиться, если сервер отдаёт 404
        'url_style' => 'query',
        'timezone' => 'Europe/Moscow',
        'debug' => false,             // true только на время разработки
        'session_name' => 'emvia_session',
    ],

    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'emvia_study',
        'user' => 'emvia_user',
        'pass' => '',
    ],

    'mail' => [
        // log — письма пишутся в storage/logs/mail.log и не отправляются
        // mail — отправка через стандартную функцию mail() хостинга
        'transport' => 'log',
        'from' => 'no-reply@emvia.study',
        'from_name' => 'EMVIA Study',
    ],

    'uploads' => [
        'max_size' => 5 * 1024 * 1024,
        'types' => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'],
    ],
];
