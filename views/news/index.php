<?php
/** @var array $items */
?>
<div class="card" style="margin-bottom:18px; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap">
    <div>
        <h2 class="card-title" style="margin-bottom:2px">Публикации</h2>
        <div class="muted small">Черновики и опубликованные новости школы</div>
    </div>
    <a class="btn btn-primary" href="<?= e(url('/app/news/create')) ?>">+ Новая публикация</a>
</div>

<div class="table-wrap">
    <table class="table">
        <thead><tr><th>Заголовок</th><th>Автор</th><th>Дата</th><th>Статус</th><th></th></tr></thead>
        <tbody>
        <?php if (!$items): ?>
            <tr><td colspan="5"><div class="table-empty">Публикаций пока нет</div></td></tr>
        <?php endif; ?>
        <?php foreach ($items as $item): ?>
            <tr>
                <td>
                    <strong><?= e($item['title']) ?></strong>
                    <div class="muted small">/news/<?= e($item['slug']) ?></div>
                </td>
                <td class="small"><?= e(trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? '')) ?: '—') ?></td>
                <td class="small nowrap"><?= e(fmt_date($item['created_at'])) ?></td>
                <td><?= badge($item['status']) ?></td>
                <td style="display:flex; gap:6px">
                    <a class="btn btn-sm btn-ghost" href="<?= e(url('/app/news/' . $item['id'] . '/edit')) ?>">Изменить</a>
                    <?php if ($item['status'] === 'published'): ?>
                        <a class="btn btn-sm btn-soft" href="<?= e(url('/news/' . $item['slug'])) ?>" target="_blank" rel="noopener">Открыть</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
