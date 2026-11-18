<?php
/** @var array $groups */
/** @var array $upcoming */
/** @var array $toReport */
?>
<div class="grid grid-3">
    <div class="card">
        <div class="muted small">Мои группы</div>
        <div class="stat-value" style="margin-top:6px"><?= count($groups) ?></div>
    </div>
    <div class="card">
        <div class="muted small">Работы на проверку</div>
        <div class="stat-value" style="margin-top:6px"><?= (int)$toCheck ?></div>
        <a class="small" href="<?= e(url('/app/homework')) ?>">Домашние задания →</a>
    </div>
    <div class="card">
        <div class="muted small">Отчёты к заполнению</div>
        <div class="stat-value" style="margin-top:6px"><?= count($toReport) ?></div>
        <a class="small" href="<?= e(url('/app/schedule')) ?>">Расписание →</a>
    </div>
</div>

<div class="card" style="margin-top:20px">
    <div class="card-head">
        <h2 class="card-title">Моя неделя</h2>
        <div style="display:flex; gap:6px">
            <a class="btn btn-sm btn-ghost" href="<?= e(url('/app?week=' . date('Y-m-d', strtotime('-7 days', $weekStart)))) ?>">←</a>
            <a class="btn btn-sm btn-ghost" href="<?= e(url('/app?week=' . date('Y-m-d', strtotime('+7 days', $weekStart)))) ?>">→</a>
        </div>
    </div>
    <div class="week-grid">
        <?php foreach ($weekDays as $i => $day): ?>
            <?php $key = date('Y-m-d', $day); ?>
            <div class="week-day">
                <div class="week-day-head">
                    <span><?= e(['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'][$i]) ?></span>
                    <span class="muted small"><?= e(date('d.m', $day)) ?></span>
                </div>
                <?php foreach ($lessonsByDay[$key] ?? [] as $lesson): ?>
                    <a class="week-lesson" href="<?= e(url('/app/lessons/' . $lesson['id'])) ?>">
                        <strong><?= e(date('H:i', strtotime($lesson['scheduled_at']))) ?></strong>
                        <span><?= e($lesson['group_name']) ?></span>
                    </a>
                <?php endforeach; ?>
                <?php if (empty($lessonsByDay[$key])): ?>
                    <div class="week-empty">—</div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="grid grid-2" style="margin-top:20px; align-items:start">
    <div class="card">
        <div class="card-head"><h2 class="card-title">Ближайшие занятия</h2></div>
        <?php if (!$upcoming): ?>
            <div class="table-empty">Пока нет запланированных занятий</div>
        <?php else: ?>
            <?php foreach ($upcoming as $lesson): ?>
                <a href="<?= e(url('/app/lessons/' . $lesson['id'])) ?>"
                   style="display:flex; justify-content:space-between; gap:12px; padding:12px 0; border-bottom:1px solid var(--line); color:inherit">
                    <div>
                        <strong><?= e($lesson['group_name']) ?></strong>
                        <div class="muted small"><?= e($lesson['topic']) ?></div>
                    </div>
                    <div class="nowrap small muted"><?= e(fmt_date($lesson['scheduled_at'], 'd.m, H:i')) ?></div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-head"><h2 class="card-title">Заполнить отчёты</h2></div>
        <?php if (!$toReport): ?>
            <div class="table-empty">Все отчёты заполнены — красиво</div>
        <?php else: ?>
            <?php foreach ($toReport as $lesson): ?>
                <div style="display:flex; justify-content:space-between; gap:12px; align-items:center; padding:12px 0; border-bottom:1px solid var(--line)">
                    <div>
                        <strong><?= e($lesson['group_name']) ?></strong>
                        <div class="muted small"><?= e(fmt_date($lesson['scheduled_at'], 'd.m, H:i')) ?> · <?= e($lesson['topic']) ?></div>
                    </div>
                    <a class="btn btn-sm btn-primary" href="<?= e(url('/app/lessons/' . $lesson['id'])) ?>">Заполнить</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-top:20px">
    <div class="card-head">
        <h2 class="card-title">Мои группы</h2>
        <a class="small" href="<?= e(url('/app/streams')) ?>">Все потоки</a>
    </div>
    <?php if (!$groups): ?>
        <div class="table-empty">Группы ещё не назначены</div>
    <?php else: ?>
        <div class="table-wrap" style="border:0">
            <table class="table">
                <thead><tr><th>Группа</th><th>Поток</th><th>Ученики</th><th>Расписание</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($groups as $group): ?>
                    <tr>
                        <td><strong><?= e($group['name']) ?></strong></td>
                        <td><?= e($group['stream_title']) ?></td>
                        <td><?= (int)$group['students_total'] ?> / <?= (int)$group['capacity'] ?></td>
                        <td class="muted small"><?= e($group['schedule_note'] ?: '—') ?></td>
                        <td><a class="btn btn-sm btn-ghost" href="<?= e(url('/app/streams/' . $group['stream_id'])) ?>">Открыть</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?= View::capture('partials/certificate', ['cert' => $certificate]) ?>
