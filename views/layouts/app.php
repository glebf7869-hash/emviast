<?php
/** @var string $content */
/** @var string $title */
$user = current_user() ?? [];
$role = $user['role'] ?? 'student';
$path = current_path();

$primary = [['/app', 'Обзор', '◈']];

if (in_array($role, ['root', 'admin', 'curator'], true)) {
    $primary[] = ['/app/applications', 'Заявки', '✉'];
    $primary[] = ['/app/users', $role === 'curator' ? 'Ученики' : 'Аккаунты', '☺'];
}
if ($role !== 'smm') {
    $primary[] = ['/app/streams', 'Потоки', '▤'];
    $primary[] = ['/app/groups', 'Группы', '▦'];
    $primary[] = ['/app/schedule', 'Расписание', '◷'];
    if (in_array($role, ['root', 'admin', 'curator', 'teacher'], true)) {
        $primary[] = ['/app/resources', 'Презентации', '▤'];
    }
}
if (in_array($role, ['root', 'admin', 'curator', 'teacher'], true)) {
    $primary[] = ['/app/reports', 'Отчёты', '✓'];
}
if (in_array($role, ['root', 'admin', 'curator', 'teacher', 'student'], true)) {
    $primary[] = ['/app/homework', 'Домашние задания', '✎'];
}

$secondary = [];
if (in_array($role, ['root', 'admin', 'curator', 'teacher'], true)) {
    $secondary[] = ['/app/materials', 'Пособия', '▥'];
}
if (in_array($role, ['root', 'admin'], true)) {
    $secondary[] = ['/app/documents', 'Документы', '▥'];
}
if (in_array($role, ['root', 'admin', 'smm'], true)) {
    $secondary[] = ['/app/smm', 'SMM-задачи', '✷'];
    $secondary[] = ['/app/smm/stats', 'Статистика', '↗'];
    $secondary[] = ['/app/news', 'Новости', '☰'];
}
if ($role === 'root') {
    $secondary[] = ['/app/logs', 'Журнал', '≡'];
}

$heading = $heading ?? preg_replace('/\s+—\s+EMVIA Study$/u', '', (string)($title ?? 'Кабинет'));
?>
<!doctype html>
<html lang="ru" data-base="<?= e(base_path()) ?>" data-style="<?= e(url_style()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Кабинет') ?></title>
    <link rel="preload" href="<?= e(asset('fonts/manrope-cyrillic.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= e(asset('fonts/manrope-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="icon" href="<?= e(asset('img/mark.svg')) ?>" type="image/svg+xml">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="<?= e(url('/app')) ?>">
            <img class="mark-img" src="<?= e(asset('img/mark.svg')) ?>" alt="">
            <span>EMVIA <em>Study</em></span>
        </a>

        <nav class="side-nav">
            <?php foreach ($primary as [$href, $label, $icon]): ?>
                <?php $active = $href === '/app' ? $path === '/app' : str_starts_with($path, $href); ?>
                <a class="side-link <?= $active ? 'is-active' : '' ?>" href="<?= e(url($href)) ?>">
                    <span class="ico"><?= e($icon) ?></span><?= e($label) ?>
                </a>
            <?php endforeach; ?>

            <?php if ($secondary): ?>
                <div class="side-label">Управление</div>
                <?php foreach ($secondary as [$href, $label, $icon]): ?>
                    <?php $active = str_starts_with($path, $href) && !($href === '/app/smm' && str_starts_with($path, '/app/smm/stats')); ?>
                    <a class="side-link <?= $active ? 'is-active' : '' ?>" href="<?= e(url($href)) ?>">
                        <span class="ico"><?= e($icon) ?></span><?= e($label) ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="side-label">Аккаунт</div>
            <a class="side-link <?= str_starts_with($path, '/app/profile') ? 'is-active' : '' ?>" href="<?= e(url('/app/profile')) ?>">
                <span class="ico">⚙</span>Профиль
            </a>
            <a class="side-link" href="<?= e(url('/')) ?>">
                <span class="ico">↗</span>На сайт
            </a>
        </nav>

        <div class="side-user">
            <span class="avatar"><?= e(initials((string)$user['first_name'], (string)$user['last_name'])) ?></span>
            <div style="min-width:0">
                <div class="side-user-name"><?= e($user['first_name'] . ' ' . $user['last_name']) ?></div>
                <div class="side-user-role"><?= e(role_label($role)) ?></div>
            </div>
            <form method="post" action="<?= e(url('/logout')) ?>" style="margin-left:auto">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-ghost" type="submit" title="Выйти">Выйти</button>
            </form>
        </div>
    </aside>

    <div class="main">
        <div class="topbar">
            <div>
                <h1 class="page-title"><?= e($heading) ?></h1>
                <?php if (!empty($subtitle)): ?>
                    <div class="page-sub"><?= e($subtitle) ?></div>
                <?php endif; ?>
            </div>
            <?php if (!empty($actions)): ?>
                <div class="topbar-actions"><?= $actions ?></div>
            <?php endif; ?>
        </div>

        <?php foreach (Flash::pull() as $message): ?>
            <div class="alert alert-<?= e($message['type']) ?>"><?= e($message['message']) ?></div>
        <?php endforeach; ?>

        <?= $content ?>
    </div>
</div>
<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
