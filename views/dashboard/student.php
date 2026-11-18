<?php
/** @var array $upcoming */
/** @var array $homework */
/** @var array $progress */
?>
<div class="grid grid-3">
    <div class="card">
        <div class="muted small">Ближайшие занятия</div>
        <div class="stat-value" style="margin-top:6px"><?= count($upcoming) ?></div>
    </div>
    <div class="card">
        <div class="muted small">Задания</div>
        <div class="stat-value" style="margin-top:6px"><?= count($homework) ?></div>
    </div>
    <div class="card">
        <div class="muted small">Мой прогресс</div>
        <div class="stat-value" style="margin-top:6px">
            <?= $progress ? (int)$progress[0]['percent'] . '%' : '—' ?>
        </div>
    </div>
</div>

<div class="grid grid-2" style="margin-top:20px; align-items:start">
    <div class="card">
        <div class="card-head">
            <h2 class="card-title">Расписание</h2>
            <a class="small" href="<?= e(url('/app/schedule')) ?>">Все занятия</a>
        </div>
        <?php if (!$upcoming): ?>
            <div class="table-empty">Занятий пока нет — загляните позже</div>
        <?php else: ?>
            <?php foreach ($upcoming as $lesson): ?>
                <div style="padding:12px 0; border-bottom:1px solid var(--line)">
                    <div style="display:flex; justify-content:space-between; gap:10px">
                        <strong><?= e($lesson['topic']) ?></strong>
                        <span class="nowrap small muted"><?= e(fmt_date($lesson['scheduled_at'], 'd.m, H:i')) ?></span>
                    </div>
                    <div class="muted small"><?= e($lesson['group_name']) ?></div>
                    <?php if (!empty($lesson['meeting_url'])): ?>
                        <a class="btn btn-sm btn-soft" style="margin-top:8px" href="<?= e($lesson['meeting_url']) ?>" target="_blank" rel="noopener">Ссылка на урок</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-head">
            <h2 class="card-title">Домашние задания</h2>
            <a class="small" href="<?= e(url('/app/homework')) ?>">Все</a>
        </div>
        <?php if (!$homework): ?>
            <div class="table-empty">Пока нет заданий</div>
        <?php else: ?>
            <?php foreach ($homework as $item): ?>
                <?php [$label, $class] = status_meta($item['submission_status'] ?: 'assigned'); ?>
                <div style="padding:12px 0; border-bottom:1px solid var(--line)">
                    <div style="display:flex; justify-content:space-between; gap:10px; align-items:start">
                        <div>
                            <a href="<?= e(url('/app/homework/' . $item['id'])) ?>"><strong><?= e($item['title']) ?></strong></a>
                            <div class="muted small">
                                <?= e($item['group_name']) ?>
                                <?= $item['due_at'] ? ' · до ' . e(fmt_date($item['due_at'], 'd.m H:i')) : '' ?>
                                <?= $item['grade'] !== null ? ' · оценка ' . (int)$item['grade'] : '' ?>
                            </div>
                        </div>
                        <span class="badge <?= e($class) ?>"><?= e($label) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php if ($progress): ?>
<div class="card" style="margin-top:20px">
    <div class="card-head"><h2 class="card-title">Прогресс по группам</h2></div>
    <?php foreach ($progress as $row): ?>
        <div style="padding:12px 0; border-bottom:1px solid var(--line)">
            <div style="display:flex; justify-content:space-between; gap:12px; margin-bottom:8px">
                <strong><?= e($row['group_name']) ?></strong>
                <span class="small muted"><?= (int)$row['percent'] ?>%</span>
            </div>
            <div class="progress"><span style="width:<?= (int)$row['percent'] ?>%"></span></div>
            <?php if (!empty($row['comment'])): ?>
                <div class="muted small" style="margin-top:6px"><?= e($row['comment']) ?></div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?= View::capture('partials/certificate', ['cert' => $certificate]) ?>
