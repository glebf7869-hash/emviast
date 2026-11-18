<?php
/** @var array $items */
/** @var array $filters */
/** @var bool $canCreate */
$levels = ['A1', 'A2', 'B1', 'B2', 'C1'];
?>
<?php if ($canCreate): ?>
    <div class="topbar-actions" style="margin-bottom:18px">
        <a class="btn btn-primary" href="<?= e(url('/app/materials/create')) ?>">+ Новое пособие</a>
    </div>
<?php endif; ?>

<form class="filters" method="get" action="<?= e(url('/app/materials')) ?>">
    <?= route_field() ?>
    <label class="field">
        <span>Уровень</span>
        <select name="level">
            <option value="">Любой</option>
            <?php foreach ($levels as $level): ?>
                <option value="<?= e($level) ?>" <?= $filters['level'] === $level ? 'selected' : '' ?>><?= e($level) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="field" style="flex:2">
        <span>Поиск</span>
        <input type="text" name="q" value="<?= e($filters['q']) ?>" placeholder="Название, автор, описание">
    </label>
    <button class="btn btn-primary" type="submit">Показать</button>
    <a class="btn btn-ghost" href="<?= e(url('/app/materials')) ?>">Сбросить</a>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
        <tr><th>Пособие</th><th>Автор</th><th>Уровень</th><th>В группах</th><th></th></tr>
        </thead>
        <tbody>
        <?php if (!$items): ?>
            <tr><td colspan="5"><div class="table-empty">Каталог пуст<?= $canCreate ? ' — добавьте первое пособие' : '' ?>.</div></td></tr>
        <?php endif; ?>
        <?php foreach ($items as $item): ?>
            <tr>
                <td>
                    <strong>
                        <?php if ($item['url']): ?>
                            <a href="<?= e($item['url']) ?>" target="_blank" rel="noopener"><?= e($item['title']) ?></a>
                        <?php else: ?>
                            <?= e($item['title']) ?>
                        <?php endif; ?>
                    </strong>
                    <?php if ($item['description']): ?>
                        <div class="muted small"><?= e(mb_substr($item['description'], 0, 110)) ?></div>
                    <?php endif; ?>
                </td>
                <td class="small"><?= e($item['author'] ?: '—') ?></td>
                <td><?= $item['level'] ? '<span class="chip">' . e($item['level']) . '</span>' : '—' ?></td>
                <td><?= (int)$item['groups_total'] ?></td>
                <td style="display:flex; gap:6px">
                    <?php if ($canCreate): ?>
                        <a class="btn btn-sm btn-ghost" href="<?= e(url('/app/materials/' . $item['id'] . '/edit')) ?>">Изменить</a>
                        <form method="post" action="<?= e(url('/app/materials/' . $item['id'] . '/delete')) ?>" data-confirm="Удалить пособие из каталога?">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-danger" type="submit">Удалить</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p class="muted small" style="margin-top:12px">
    Пособия привязываются к группам на странице группы: там же видно, что уже выбрано.
</p>
