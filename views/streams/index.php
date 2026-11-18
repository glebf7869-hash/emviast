<?php
/** @var array $items */
/** @var bool $canCreate */
?>
<?php if ($canCreate): ?>
    <div class="topbar-actions" style="margin-bottom:18px">
        <a class="btn btn-primary" href="<?= e(url('/app/streams/create')) ?>">+ Новый поток</a>
    </div>
<?php endif; ?>

<div class="grid grid-2">
    <?php if (!$items): ?>
        <div class="empty" style="grid-column:1/-1">Потоков пока нет<?= $canCreate ? ' — создайте первый' : '' ?>.</div>
    <?php endif; ?>

    <?php foreach ($items as $item): ?>
        <a class="card card-hover" href="<?= e(url('/app/streams/' . $item['id'])) ?>" style="color:inherit">
            <div style="display:flex; justify-content:space-between; gap:12px; align-items:start">
                <div>
                    <h3 style="font-size:19px; margin-bottom:4px"><?= e($item['title']) ?></h3>
                    <div class="muted small"><?= e($item['language']) ?> · <?= e($item['level']) ?></div>
                </div>
                <?= badge($item['status']) ?>
            </div>

            <div class="divider" style="margin:16px 0"></div>

            <div style="display:flex; gap:18px; flex-wrap:wrap; font-size:13.5px">
                <span><strong><?= (int)$item['groups_total'] ?></strong> групп</span>
                <span><strong><?= (int)$item['students_total'] ?></strong> учеников</span>
                <span><?= e(fmt_date($item['starts_on'])) ?> — <?= e(fmt_date($item['ends_on'])) ?></span>
            </div>

            <?php if (!empty($item['curator_first'])): ?>
                <div class="muted small" style="margin-top:8px">Куратор: <?= e($item['curator_first'] . ' ' . $item['curator_last']) ?></div>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>
