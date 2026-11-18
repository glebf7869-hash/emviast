<?php
declare(strict_types=1);

/**
 * Диагностика ссылок и маршрутизации.
 * Откройте https://ваш-домен/diag.php и пришлите скриншот.
 * После проверки файл можно удалить.
 */

require __DIR__ . '/system/bootstrap.php';

$base = base_path();
$style = url_style();

$prettyUrl = $base . '/about';
$queryUrl = $base . '/index.php?r=' . rawurlencode('/about');

$rows = [
    'SERVER_SOFTWARE' => $_SERVER['SERVER_SOFTWARE'] ?? '—',
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? '—',
    'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? '—',
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? '—',
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? '—',
    'PHP version' => PHP_VERSION,
];
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Диагностика EMVIA Study</title>
    <style>
        body { font: 14px/1.5 -apple-system, Arial, sans-serif; background: #fbf7f2; color: #1b2030; margin: 0; padding: 24px; }
        .card { max-width: 820px; margin: 0 auto; background: #fff; border: 1px solid #ece4db; border-radius: 18px; padding: 24px; }
        h1 { font-size: 22px; margin: 0 0 6px; }
        p.muted { color: #6e7486; margin: 0 0 18px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid #ece4db; vertical-align: top; }
        th { width: 42%; color: #6e7486; font-weight: 600; }
        code { background: #f4eee7; padding: 2px 6px; border-radius: 6px; word-break: break-all; }
        .ok { color: #1e6b3a; font-weight: 700; }
        .bad { color: #ba0000; font-weight: 700; }
        .box { background: #fdfaf6; border: 1px solid #ece4db; border-radius: 12px; padding: 14px 16px; margin-bottom: 16px; }
        a { color: #001f83; }
    </style>
</head>
<body>
<div class="card">
    <h1>Диагностика EMVIA Study</h1>
    <p class="muted">Проверка путей и маршрутизации. После решения проблемы удалите этот файл.</p>

    <table>
        <?php foreach ($rows as $label => $value): ?>
            <tr><th><?= htmlspecialchars($label) ?></th><td><code><?= htmlspecialchars((string)$value) ?></code></td></tr>
        <?php endforeach; ?>
        <tr><th>Базовый путь (base)</th><td><code><?= htmlspecialchars($base === '' ? '(корень домена)' : $base) ?></code></td></tr>
        <tr><th>Режим ссылок</th><td><code><?= htmlspecialchars($style) ?></code></td></tr>
        <tr><th>.htaccess в корне</th><td><?= is_file(__DIR__ . '/.htaccess') ? '<span class="ok">есть</span>' : '<span class="bad">нет</span>' ?></td></tr>
    </table>

    <div class="box">
        <div><strong>Ссылка для проверки (красивая):</strong></div>
        <a href="<?= htmlspecialchars($prettyUrl) ?>" target="_blank"><?= htmlspecialchars($prettyUrl) ?></a>
        <div id="pretty-result" class="muted">проверяем…</div>
    </div>

    <div class="box">
        <div><strong>Ссылка для проверки (без rewrite):</strong></div>
        <a href="<?= htmlspecialchars($queryUrl) ?>" target="_blank"><?= htmlspecialchars($queryUrl) ?></a>
        <div id="query-result" class="muted">проверяем…</div>
    </div>

    <p>Сейчас по умолчанию включён режим <code>query</code> — ссылки вида
        <code>index.php?r=/about</code> работают на любом хостинге.
        Если «красивая» ссылка тоже открывается, можно включить
        <code>'url_style' =&gt; 'pretty'</code> в <code>config/config.php</code>.</p>
</div>

<script>
async function check(url, id) {
    const el = document.getElementById(id);
    try {
        const res = await fetch(url, { method: 'GET', redirect: 'follow' });
        if (res.ok) {
            el.innerHTML = '<span class="ok">работает (HTTP ' + res.status + ')</span>';
        } else {
            el.innerHTML = '<span class="bad">не работает (HTTP ' + res.status + ')</span>';
        }
    } catch (e) {
        el.innerHTML = '<span class="bad">ошибка запроса</span>';
    }
}
check(<?= json_encode($prettyUrl) ?>, 'pretty-result');
check(<?= json_encode($queryUrl) ?>, 'query-result');
</script>
</body>
</html>
