<?php
declare(strict_types=1);

/**
 * Обновление базы: каталог пособий и привязка пособий к группам.
 * Запускается один раз, после — удалите файл.
 */

require __DIR__ . '/system/bootstrap.php';

$done = false;
$error = '';

try {
    $pdo = Database::pdo();

    $sql = (string)file_get_contents(__DIR__ . '/database/upgrade_2026_11_materials.sql');
    $statements = array_filter(array_map('trim', preg_split('/;\s*(?:\r?\n|$)/', preg_replace('/^\s*--.*$/m', '', $sql) ?? '')));
    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }

    // Каталог презентаций, документы и сертификаты.
    $extraSql = (string)file_get_contents(__DIR__ . '/database/upgrade_2026_11_resources.sql');
    $extraStatements = array_filter(array_map('trim', preg_split('/;\s*(?:\r?\n|$)/', preg_replace('/^\s*--.*$/m', '', $extraSql) ?? '')));
    foreach ($extraStatements as $statement) {
        $pdo->exec($statement);
    }

    // Английские версии новостей.
    $newsColumns = array_column($pdo->query('SHOW COLUMNS FROM news')->fetchAll(), 'Field');
    if (!in_array('title_en', $newsColumns, true)) {
        $pdo->exec('ALTER TABLE news ADD COLUMN title_en VARCHAR(190) DEFAULT NULL AFTER title');
    }
    if (!in_array('excerpt_en', $newsColumns, true)) {
        $pdo->exec('ALTER TABLE news ADD COLUMN excerpt_en VARCHAR(255) DEFAULT NULL AFTER excerpt');
    }
    if (!in_array('body_en', $newsColumns, true)) {
        $pdo->exec('ALTER TABLE news ADD COLUMN body_en MEDIUMTEXT DEFAULT NULL AFTER body');
    }

    // Пожелания по расписанию в заявках.
    $appColumns = array_column($pdo->query('SHOW COLUMNS FROM applications')->fetchAll(), 'Field');
    if (!in_array('groups_ready', $appColumns, true)) {
        $pdo->exec('ALTER TABLE applications ADD COLUMN groups_ready TINYINT UNSIGNED DEFAULT NULL AFTER hours');
    }
    if (!in_array('schedule_days', $appColumns, true)) {
        $pdo->exec('ALTER TABLE applications ADD COLUMN schedule_days VARCHAR(60) DEFAULT NULL AFTER groups_ready');
    }
    if (!in_array('schedule_time', $appColumns, true)) {
        $pdo->exec('ALTER TABLE applications ADD COLUMN schedule_time VARCHAR(120) DEFAULT NULL AFTER schedule_days');
    }

    // Даты и время фото начала и конца урока.
    $columns = array_column($pdo->query('SHOW COLUMNS FROM lesson_reports')->fetchAll(), 'Field');
    if (!in_array('photo_start_at', $columns, true)) {
        $pdo->exec('ALTER TABLE lesson_reports ADD COLUMN photo_start_at DATETIME DEFAULT NULL AFTER photo_start');
    }
    if (!in_array('photo_end_at', $columns, true)) {
        $pdo->exec('ALTER TABLE lesson_reports ADD COLUMN photo_end_at DATETIME DEFAULT NULL AFTER photo_end');
    }

    $done = true;
} catch (Throwable $e) {
    $error = $e->getMessage();
}

$hasMaterials = false;
try {
    $hasMaterials = (bool)Database::value("SHOW TABLES LIKE 'materials'", [], false);
} catch (Throwable $e) {
    $hasMaterials = false;
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Обновление базы · EMVIA Study</title>
    <style>
        body { font: 15px/1.6 -apple-system, Arial, sans-serif; background: #fbf7f2; color: #1b2030; margin: 0; padding: 40px 20px; }
        .card { max-width: 620px; margin: 0 auto; background: #fff; border: 1px solid #ece4db; border-radius: 20px; padding: 28px; }
        h1 { font-size: 22px; margin: 0 0 12px; }
        .ok { color: #1e6b3a; }
        .bad { color: #ba0000; }
        code { background: #f4eee7; padding: 2px 6px; border-radius: 6px; }
        a.btn { display: inline-block; margin-top: 14px; padding: 10px 18px; border-radius: 999px; background: #001f83; color: #fff; text-decoration: none; }
    </style>
</head>
<body>
<div class="card">
    <h1>Обновление базы EMVIA Study</h1>
    <?php if ($error !== ''): ?>
        <p class="bad">Ошибка: <?= htmlspecialchars($error) ?></p>
        <p>Проверьте доступ к базе в <code>config/config.php</code>.</p>
    <?php elseif ($done): ?>
        <p class="ok">Готово. Таблицы пособий созданы (или уже существовали).</p>
        <p>Теперь можно удалить файл <code>upgrade.php</code> с сервера.</p>
    <?php endif; ?>
    <p>Статус: каталог пособий <?= $hasMaterials ? '<strong class="ok">на месте</strong>' : '<strong class="bad">не найден</strong>' ?>.</p>
    <a class="btn" href="<?= htmlspecialchars(url('/app')) ?>">Перейти в кабинет</a>
</div>
</body>
</html>
