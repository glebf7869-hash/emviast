<?php
/** @var array $items */
/** @var array $textbooks */
/** @var array $topics */
/** @var array $filters */
/** @var bool $canManage */
?>
<div class="card card-soft" style="margin-bottom:18px; display:flex; gap:16px; align-items:flex-start; flex-wrap:wrap">
    <div style="flex:1; min-width:240px">
        <h2 class="card-title" style="margin-bottom:4px">Каталог презентаций</h2>
        <p class="muted small" style="margin:0">
            Материалы лежат на Google Диске. Откройте папку или презентацию — доступ уже настроен.
            Если папка не отображается внутри страницы, нажмите «Открыть на Диске».
        </p>
    </div>
    <?php if ($canManage): ?>
        <a class="btn btn-primary" href="<?= e(url('/app/resources/create')) ?>">+ Добавить ресурс</a>
    <?php endif; ?>
</div>

<form class="filters" method="get" action="<?= e(url('/app/resources')) ?>">
    <?= route_field() ?>
    <label class="field" style="flex:2">
        <span>Учебник</span>
        <select name="textbook">
            <option value="">Все учебники</option>
            <?php foreach ($textbooks as $textbook): ?>
                <option value="<?= e($textbook) ?>" <?= $filters['textbook'] === $textbook ? 'selected' : '' ?>><?= e($textbook) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="field" style="flex:2">
        <span>Тема</span>
        <select name="topic">
            <option value="">Все темы</option>
            <?php foreach ($topics as $topic): ?>
                <option value="<?= e($topic) ?>" <?= $filters['topic'] === $topic ? 'selected' : '' ?>><?= e($topic) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button class="btn btn-primary" type="submit">Показать</button>
    <a class="btn btn-ghost" href="<?= e(url('/app/resources')) ?>">Сбросить</a>
</form>

<?php if (!$items): ?>
    <div class="empty">Пока нет материалов<?= $canManage ? ' — добавьте первую папку с презентациями' : '' ?>.</div>
<?php endif; ?>

<div class="grid grid-2">
    <?php foreach ($items as $item): ?>
        <div class="card">
            <div class="card-head" style="margin-bottom:8px">
                <h3 class="card-title" style="font-size:17px"><?= e($item['title']) ?></h3>
                <div style="display:flex; gap:6px; align-items:center">
                    <?php if ($item['access'] === 'admins'): ?>
                        <span class="badge badge-amber">только админы</span>
                    <?php else: ?>
                        <span class="badge badge-green">для преподавателей</span>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:8px">
                <?php if ($item['textbook']): ?><span class="chip"><?= e($item['textbook']) ?></span><?php endif; ?>
                <?php if ($item['topic']): ?><span class="chip"><?= e($item['topic']) ?></span><?php endif; ?>
                <?php if ($item['level']): ?><span class="chip"><?= e($item['level']) ?></span><?php endif; ?>
            </div>

            <?php if ($item['description']): ?>
                <p class="muted small"><?= e($item['description']) ?></p>
            <?php endif; ?>

            <?php if ($item['kind'] === 'folder' && drive_embed_url($item) !== ''): ?>
                <iframe class="drive-frame" src="<?= e(drive_embed_url($item)) ?>" loading="lazy" title="<?= e($item['title']) ?>"></iframe>
            <?php endif; ?>

            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:12px">
                <a class="btn btn-sm btn-primary" href="<?= e($item['url']) ?>" target="_blank" rel="noopener">
                    Открыть на Диске
                </a>
                <?php if ($canManage): ?>
                    <a class="btn btn-sm btn-ghost" href="<?= e(url('/app/resources/' . $item['id'] . '/edit')) ?>">Изменить</a>
                    <form method="post" action="<?= e(url('/app/resources/' . $item['id'] . '/delete')) ?>" data-confirm="Удалить ресурс из каталога?">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-danger" type="submit">Удалить</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
