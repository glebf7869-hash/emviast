<?php
/** @var array $items */
?>
<div class="card card-soft" style="margin-bottom:18px; display:flex; gap:16px; justify-content:space-between; align-items:center; flex-wrap:wrap">
    <div>
        <h2 class="card-title" style="margin-bottom:2px">Документы на странице правил</h2>
        <p class="muted small" style="margin:0">PDF, DOC, DOCX или ссылка. На сайте появится кнопка «Перейти».</p>
    </div>
    <a class="btn btn-primary" href="<?= e(url('/app/documents/create')) ?>">+ Добавить документ</a>
</div>

<div class="table-wrap">
    <table class="table">
        <thead><tr><th>Документ</th><th>Тип</th><th>Порядок</th><th></th></tr></thead>
        <tbody>
        <?php if (!$items): ?>
            <tr><td colspan="4"><div class="table-empty">Документов пока нет</div></td></tr>
        <?php endif; ?>
        <?php foreach ($items as $item): ?>
            <tr>
                <td>
                    <strong><?= e($item['title']) ?></strong>
                    <?php if ($item['description']): ?><div class="muted small"><?= e($item['description']) ?></div><?php endif; ?>
                </td>
                <td class="small"><?= $item['file'] ? 'файл' : 'ссылка' ?></td>
                <td class="small"><?= (int)$item['sort_order'] ?></td>
                <td style="display:flex; gap:6px; flex-wrap:wrap">
                    <?php $href = $item['file'] ? file_url($item['file']) : (string)$item['url']; ?>
                    <?php if ($href): ?>
                        <a class="btn btn-sm btn-ghost" href="<?= e($href) ?>" target="_blank" rel="noopener">Открыть</a>
                    <?php endif; ?>
                    <a class="btn btn-sm btn-ghost" href="<?= e(url('/app/documents/' . $item['id'] . '/edit')) ?>">Изменить</a>
                    <form method="post" action="<?= e(url('/app/documents/' . $item['id'] . '/delete')) ?>" data-confirm="Удалить документ?">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-danger" type="submit">Удалить</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
