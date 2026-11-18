<?php
/** @var array $myTasks */
/** @var array $latestStats */
?>
<div class="grid grid-3">
    <div class="card">
        <div class="muted small">Задачи на сегодня и просроченные</div>
        <div class="stat-value" style="margin-top:6px"><?= (int)$overdue ?></div>
        <div class="muted small">просрочено</div>
    </div>
    <div class="card">
        <div class="muted small">Закрыто за неделю</div>
        <div class="stat-value" style="margin-top:6px"><?= (int)$doneThisWeek ?></div>
    </div>
    <div class="card">
        <div class="muted small">Черновики новостей</div>
        <div class="stat-value" style="margin-top:6px"><?= (int)$drafts ?></div>
        <a class="small" href="<?= e(url('/app/news')) ?>">К новостям →</a>
    </div>
</div>

<div class="grid grid-2" style="margin-top:20px; align-items:start">
    <div class="card">
        <div class="card-head">
            <h2 class="card-title">Мои задачи</h2>
            <a class="small" href="<?= e(url('/app/smm')) ?>">Все</a>
        </div>
        <?php if (!$myTasks): ?>
            <div class="table-empty">Задач пока нет</div>
        <?php else: ?>
            <?php foreach ($myTasks as $task): ?>
                <div style="padding:12px 0; border-bottom:1px solid var(--line)">
                    <div style="display:flex; justify-content:space-between; gap:10px; align-items:start">
                        <div>
                            <strong><?= e($task['title']) ?></strong>
                            <div class="muted small">
                                <?= e($task['platform'] ?: 'Без площадки') ?>
                                <?= $task['due_on'] ? ' · до ' . e(fmt_date($task['due_on'])) : '' ?>
                            </div>
                        </div>
                        <?= badge($task['status']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-head">
            <h2 class="card-title">Соцсети</h2>
            <a class="small" href="<?= e(url('/app/smm/stats')) ?>">Статистика</a>
        </div>
        <?php if (!$latestStats): ?>
            <div class="table-empty">Данных пока нет — добавьте первые показатели</div>
        <?php else: ?>
            <?php foreach ($latestStats as $row): ?>
                <div style="padding:12px 0; border-bottom:1px solid var(--line)">
                    <div style="display:flex; justify-content:space-between; gap:10px">
                        <strong><?= e($row['platform']) ?></strong>
                        <span class="muted small"><?= e(fmt_date($row['stat_date'])) ?></span>
                    </div>
                    <div class="muted small">
                        <?= (int)$row['followers'] ?> подписчиков · <?= (int)$row['reach'] ?> охват ·
                        <?= (int)$row['likes'] ?> лайков
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?= View::capture('partials/certificate', ['cert' => $certificate]) ?>
