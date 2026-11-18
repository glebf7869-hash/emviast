<?php
/** @var array $counts */
/** @var array $streams */
/** @var array $recentApplications */
/** @var array $upcoming */
?>
<div class="grid grid-4">
    <div class="card">
        <div class="muted small">Новые заявки</div>
        <div class="stat-value" style="margin-top:6px"><?= (int)$applicationsNew ?></div>
        <a class="small" href="<?= e(url('/app/applications')) ?>">Разобрать →</a>
    </div>
    <div class="card">
        <div class="muted small">Отчёты на проверке</div>
        <div class="stat-value" style="margin-top:6px"><?= (int)$reportsPending ?></div>
        <a class="small" href="<?= e(url('/app/reports?status=submitted')) ?>">Проверить →</a>
    </div>
    <div class="card">
        <div class="muted small">Ученики</div>
        <div class="stat-value" style="margin-top:6px"><?= (int)$counts['student'] ?></div>
        <a class="small" href="<?= e(url('/app/users?role=student')) ?>">Список →</a>
    </div>
    <div class="card">
        <div class="muted small">Открытые SMM-задачи</div>
        <div class="stat-value" style="margin-top:6px"><?= (int)$openTasks ?></div>
        <a class="small" href="<?= e(url('/app/smm')) ?>">Перейти →</a>
    </div>
</div>

<div class="split" style="margin-top:20px">
    <div class="card">
        <div class="card-head">
            <h2 class="card-title">Последние заявки</h2>
            <a class="small" href="<?= e(url('/app/applications')) ?>">Все</a>
        </div>

        <?php if (!$recentApplications): ?>
            <div class="table-empty">Пока заявок нет</div>
        <?php else: ?>
            <div class="table-wrap" style="border:0">
                <table class="table">
                    <thead>
                        <tr><th>Кто</th><th>Тип</th><th>Статус</th><th></th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentApplications as $app): ?>
                        <tr>
                            <td>
                                <strong><?= e($app['first_name'] . ' ' . $app['last_name']) ?></strong>
                                <div class="muted small"><?= e($app['email']) ?></div>
                            </td>
                            <td><?= e(['student' => 'Ученик', 'teacher' => 'Преподаватель', 'curator' => 'Куратор', 'smm' => 'SMM'][$app['type']] ?? $app['type']) ?></td>
                            <td><?= badge($app['status']) ?></td>
                            <td><a class="btn btn-sm btn-ghost" href="<?= e(url('/app/applications/' . $app['id'])) ?>">Открыть</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-head"><h2 class="card-title">Команда</h2></div>
        <div class="kv" style="grid-template-columns:1fr auto">
            <?php foreach (['admin' => 'Администраторы', 'curator' => 'Кураторы', 'teacher' => 'Преподаватели', 'smm' => 'SMM', 'student' => 'Ученики'] as $key => $label): ?>
                <dt><?= e($label) ?></dt>
                <dd><?= (int)($counts[$key] ?? 0) ?></dd>
            <?php endforeach; ?>
        </div>
        <div class="divider"></div>
        <a class="btn btn-soft btn-block" href="<?= e(url('/app/users/create')) ?>">Создать аккаунт</a>
    </div>
</div>

<div class="card" style="margin-top:20px">
    <div class="card-head">
        <h2 class="card-title">Ближайшие занятия</h2>
        <a class="small" href="<?= e(url('/app/schedule')) ?>">Расписание</a>
    </div>
    <?php if (!$upcoming): ?>
        <div class="table-empty">Занятий пока нет</div>
    <?php else: ?>
        <div class="table-wrap" style="border:0">
            <table class="table">
                <thead><tr><th>Когда</th><th>Группа</th><th>Тема</th><th>Преподаватель</th></tr></thead>
                <tbody>
                <?php foreach ($upcoming as $lesson): ?>
                    <tr>
                        <td class="nowrap"><?= e(fmt_date($lesson['scheduled_at'], 'd.m, H:i')) ?></td>
                        <td><?= e($lesson['group_name']) ?></td>
                        <td><a href="<?= e(url('/app/lessons/' . $lesson['id'])) ?>"><?= e($lesson['topic']) ?></a></td>
                        <td><?= e(trim(($lesson['first_name'] ?? '') . ' ' . ($lesson['last_name'] ?? '')) ?: '—') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="card" style="margin-top:20px">
    <div class="card-head">
        <h2 class="card-title">Кураторы</h2>
        <a class="small" href="<?= e(url('/app/users?role=curator')) ?>">Аккаунты кураторов</a>
    </div>
    <?php if (empty($curators)): ?>
        <div class="table-empty">Кураторов пока нет</div>
    <?php else: ?>
        <div class="table-wrap" style="border:0">
            <table class="table">
                <thead><tr><th>Куратор</th><th>Потоки</th><th>Группы</th><th>Отчёты на проверке</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($curators as $curator): ?>
                    <tr>
                        <td><strong><?= e($curator['first_name'] . ' ' . $curator['last_name']) ?></strong></td>
                        <td><?= (int)$curator['streams_total'] ?></td>
                        <td><?= (int)$curator['groups_total'] ?></td>
                        <td><?= (int)$curator['pending_total'] ?></td>
                        <td><a class="btn btn-sm btn-ghost" href="<?= e(url('/app/users/' . $curator['id'] . '/edit')) ?>">Открыть</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?= View::capture('partials/certificate', ['cert' => $certificate]) ?>
