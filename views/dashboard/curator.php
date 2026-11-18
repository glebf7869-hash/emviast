<?php
/** @var array $streams */
/** @var array $groups */
/** @var array $upcoming */
?>
<div class="grid grid-4">
    <div class="card">
        <div class="muted small">Новые заявки</div>
        <div class="stat-value" style="margin-top:6px"><?= (int)$applicationsNew ?></div>
        <a class="small" href="<?= e(url('/app/applications')) ?>">Открыть →</a>
    </div>
    <div class="card">
        <div class="muted small">Отчёты на проверке</div>
        <div class="stat-value" style="margin-top:6px"><?= (int)$reportsPending ?></div>
        <a class="small" href="<?= e(url('/app/reports?status=submitted')) ?>">Проверить →</a>
    </div>
    <div class="card">
        <div class="muted small">Ученики в потоках</div>
        <div class="stat-value" style="margin-top:6px"><?= (int)$studentsTotal ?></div>
        <a class="small" href="<?= e(url('/app/streams')) ?>">Потоки →</a>
    </div>
    <div class="card">
        <div class="muted small">Группы</div>
        <div class="stat-value" style="margin-top:6px"><?= count($groups) ?></div>
        <a class="small" href="<?= e(url('/app/streams')) ?>">Смотреть →</a>
    </div>
</div>

<div class="split-even" style="margin-top:20px">
    <div class="card">
        <div class="card-head"><h2 class="card-title">Мои потоки</h2></div>
        <?php if (!$streams): ?>
            <div class="table-empty">Потоки пока не закреплены</div>
        <?php else: ?>
            <?php foreach ($streams as $stream): ?>
                <div style="display:flex; justify-content:space-between; gap:12px; align-items:center; padding:10px 0; border-bottom:1px solid var(--line)">
                    <div>
                        <a href="<?= e(url('/app/streams/' . $stream['id'])) ?>"><strong><?= e($stream['title']) ?></strong></a>
                        <div class="muted small"><?= e($stream['level']) ?> · <?= e(fmt_date($stream['starts_on'])) ?></div>
                    </div>
                    <?= badge($stream['status']) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-head"><h2 class="card-title">Мои группы</h2></div>
        <?php if (!$groups): ?>
            <div class="table-empty">Групп пока нет</div>
        <?php else: ?>
            <?php foreach ($groups as $group): ?>
                <div style="display:flex; justify-content:space-between; gap:12px; align-items:center; padding:10px 0; border-bottom:1px solid var(--line)">
                    <div>
                        <strong><?= e($group['name']) ?></strong>
                        <div class="muted small"><?= e($group['stream_title']) ?></div>
                    </div>
                    <a class="btn btn-sm btn-ghost" href="<?= e(url('/app/streams/' . $group['stream_id'])) ?>">Группа</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-top:20px">
    <div class="card-head">
        <h2 class="card-title">Ближайшие занятия</h2>
        <a class="small" href="<?= e(url('/app/schedule')) ?>">Расписание</a>
    </div>
    <?php if (!$upcoming): ?>
        <div class="table-empty">Пока нет запланированных занятий</div>
    <?php else: ?>
        <div class="table-wrap" style="border:0">
            <table class="table">
                <thead><tr><th>Когда</th><th>Группа</th><th>Тема</th><th>Преподаватель</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($upcoming as $lesson): ?>
                    <tr>
                        <td class="nowrap"><?= e(fmt_date($lesson['scheduled_at'], 'd.m, H:i')) ?></td>
                        <td><?= e($lesson['group_name']) ?></td>
                        <td><?= e($lesson['topic']) ?></td>
                        <td><?= e(trim(($lesson['first_name'] ?? '') . ' ' . ($lesson['last_name'] ?? '')) ?: '—') ?></td>
                        <td><a class="btn btn-sm btn-ghost" href="<?= e(url('/app/lessons/' . $lesson['id'])) ?>">Открыть</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?= View::capture('partials/certificate', ['cert' => $certificate]) ?>
