<?php
/** @var array $group */
/** @var array $students */
/** @var array $availableStudents */
/** @var array $attached */
/** @var array $materials */
/** @var array $lessons */
/** @var array $teachers */
/** @var array $curators */
/** @var bool $canManage */
/** @var bool $canEdit */
/** @var bool $canDelete */
$role = user_role();
$canStudents = in_array($role, ['root', 'admin', 'curator'], true);
?>
<div class="card" style="margin-bottom:20px">
    <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start">
        <div>
            <div class="muted small">
                <a href="<?= e(url('/app/streams/' . $group['stream_id'])) ?>"><?= e($group['stream_title']) ?></a>
                · <?= e($group['level']) ?>
            </div>
            <div style="display:flex; gap:10px; align-items:center; margin:8px 0">
                <h2 style="margin:0; font-size:24px"><?= e($group['name']) ?></h2>
                <?= badge($group['status']) ?>
            </div>
            <div class="muted">
                Преподаватель: <?= e(trim(($group['teacher_first'] ?? '') . ' ' . ($group['teacher_last'] ?? '')) ?: 'не назначен') ?>
                · Куратор: <?= e(trim(($group['curator_first'] ?? '') . ' ' . ($group['curator_last'] ?? '')) ?: '—') ?>
                · <?= count($students) ?> / <?= (int)$group['capacity'] ?> учеников
            </div>
            <?php if ($group['schedule_note']): ?>
                <div class="muted small">Расписание: <?= e($group['schedule_note']) ?></div>
            <?php endif; ?>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap">
            <a class="btn btn-sm btn-ghost" href="<?= e(url('/app/schedule')) ?>">Расписание</a>
            <a class="btn btn-sm btn-ghost" href="<?= e(url('/app/groups')) ?>">Все группы</a>
        </div>
    </div>

    <?php if ($canEdit): ?>
        <details style="margin-top:16px">
            <summary class="small" style="cursor:pointer; color:var(--brand); font-weight:700">Настройки группы</summary>
            <form method="post" action="<?= e(url('/app/groups/' . $group['id'])) ?>" class="form" style="margin-top:14px; max-width:760px">
                <?= csrf_field() ?>
                <div class="form-row">
                    <label class="field">
                        <span>Название</span>
                        <input type="text" name="name" value="<?= e($group['name']) ?>" required>
                    </label>
                    <label class="field">
                        <span>Мест</span>
                        <input type="number" name="capacity" min="1" max="20" value="<?= (int)$group['capacity'] ?>">
                    </label>
                </div>
                <div class="form-row">
                    <label class="field">
                        <span>Преподаватель</span>
                        <select name="teacher_id">
                            <option value="0">Не назначен</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= (int)$teacher['id'] ?>" <?= (int)$group['teacher_id'] === (int)$teacher['id'] ? 'selected' : '' ?>>
                                    <?= e($teacher['first_name'] . ' ' . $teacher['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="field">
                        <span>Куратор</span>
                        <select name="curator_id">
                            <option value="0">Не назначен</option>
                            <?php foreach ($curators as $curator): ?>
                                <option value="<?= (int)$curator['id'] ?>" <?= (int)$group['curator_id'] === (int)$curator['id'] ? 'selected' : '' ?>>
                                    <?= e($curator['first_name'] . ' ' . $curator['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div class="form-row">
                    <label class="field">
                        <span>Расписание</span>
                        <input type="text" name="schedule_note" value="<?= e($group['schedule_note'] ?? '') ?>">
                    </label>
                    <label class="field">
                        <span>Статус</span>
                        <select name="status">
                            <?php foreach (['forming' => 'Формируется', 'active' => 'Активная', 'finished' => 'Завершена'] as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= $group['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </form>
        </details>
    <?php endif; ?>
</div>

<div class="split">
    <div class="side-stack">
        <div class="card">
            <div class="card-head">
                <h2 class="card-title">Ученики</h2>
                <span class="muted small"><?= count($students) ?> из <?= (int)$group['capacity'] ?></span>
            </div>

            <div class="table-wrap" style="border:0">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Ученик</th>
                        <th>Логин</th>
                        <th>Прогресс</th>
                        <?php if ($canStudents): ?><th></th><?php endif; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!$students): ?>
                        <tr><td colspan="<?= $canStudents ? 4 : 3 ?>"><div class="table-empty">В группе пока нет учеников</div></td></tr>
                    <?php endif; ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><strong><?= e($student['first_name'] . ' ' . $student['last_name']) ?></strong></td>
                            <td><code><?= e($student['login']) ?></code></td>
                            <td style="min-width:220px">
                                <?php if ($canManage): ?>
                                    <form method="post" action="<?= e(url('/app/groups/' . $group['id'] . '/progress')) ?>" style="display:flex; gap:6px; align-items:center">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="student_id" value="<?= (int)$student['id'] ?>">
                                        <input type="number" name="percent" min="0" max="100" value="<?= (int)($student['percent'] ?? 0) ?>" style="width:74px; padding:7px 9px">
                                        <input type="text" name="comment" value="<?= e($student['comment'] ?? '') ?>" placeholder="комментарий" style="min-width:120px; padding:7px 9px">
                                        <button class="btn btn-sm btn-soft" type="submit">OK</button>
                                    </form>
                                <?php else: ?>
                                    <div class="progress" style="max-width:180px"><span style="width:<?= (int)($student['percent'] ?? 0) ?>%"></span></div>
                                <?php endif; ?>
                            </td>
                            <?php if ($canStudents): ?>
                                <td>
                                    <form method="post" action="<?= e(url('/app/groups/' . $group['id'] . '/students/' . $student['id'] . '/remove')) ?>"
                                          data-confirm="Убрать ученика из группы?">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-danger" type="submit">Убрать</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($canStudents): ?>
                <form method="post" action="<?= e(url('/app/groups/' . $group['id'] . '/students')) ?>" style="display:flex; gap:8px; margin-top:14px; flex-wrap:wrap">
                    <?= csrf_field() ?>
                    <select name="student_id" required style="max-width:340px">
                        <option value="">Добавить ученика…</option>
                        <?php foreach ($availableStudents as $student): ?>
                            <option value="<?= (int)$student['id'] ?>">
                                <?= e($student['first_name'] . ' ' . $student['last_name'] . ' · ' . $student['login']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-sm btn-primary" type="submit">Добавить</button>
                </form>
                <?php if (!$availableStudents): ?>
                    <p class="muted small" style="margin:8px 0 0">Свободных учеников нет — создайте аккаунты в разделе «Аккаунты».</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-head">
                <h2 class="card-title">Пособия группы</h2>
                <?php if (in_array($role, ['root', 'admin'], true)): ?>
                    <a class="small" href="<?= e(url('/app/materials')) ?>">Каталог</a>
                <?php endif; ?>
            </div>

            <?php if (!materials_ready()): ?>
                <div class="alert alert-warning">
                    Каталог пособий ещё не создан.
                    <a href="<?= e(url('/upgrade.php')) ?>">Выполните upgrade.php</a>.
                </div>
            <?php endif; ?>

            <?php if (!$attached): ?>
                <div class="table-empty">Пособия пока не выбраны</div>
            <?php else: ?>
                <?php foreach ($attached as $material): ?>
                    <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; padding:12px 0; border-bottom:1px solid var(--line)">
                        <div>
                            <strong>
                                <?php if ($material['url']): ?>
                                    <a href="<?= e($material['url']) ?>" target="_blank" rel="noopener"><?= e($material['title']) ?></a>
                                <?php else: ?>
                                    <?= e($material['title']) ?>
                                <?php endif; ?>
                            </strong>
                            <div class="muted small">
                                <?= e($material['author'] ?: 'без автора') ?>
                                <?= $material['level'] ? ' · ' . e($material['level']) : '' ?>
                            </div>
                        </div>
                        <?php if ($canManage): ?>
                            <form method="post" action="<?= e(url('/app/groups/' . $group['id'] . '/materials/' . $material['id'] . '/delete')) ?>"
                                  data-confirm="Убрать пособие из группы?">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-ghost" type="submit">Убрать</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($canManage && $materials): ?>
                <form method="post" action="<?= e(url('/app/groups/' . $group['id'] . '/materials')) ?>" class="form" style="margin-top:14px">
                    <?= csrf_field() ?>
                    <label class="field">
                        <span>Добавить пособие</span>
                        <select name="material_id" required>
                            <option value="">Выберите из каталога…</option>
                            <?php foreach ($materials as $material): ?>
                                <option value="<?= (int)$material['id'] ?>">
                                    <?= e($material['title'] . ($material['level'] ? ' · ' . $material['level'] : '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="field">
                        <span>Заметка для группы</span>
                        <input type="text" name="note" placeholder="Например: с 3-й недели, только раздел 2">
                    </label>
                    <button class="btn btn-soft" type="submit">Добавить в группу</button>
                </form>
            <?php elseif ($canManage && !$materials): ?>
                <p class="muted small" style="margin-top:10px">Каталог пуст. Добавьте пособия в разделе «Пособия».</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="side-stack">
        <div class="card card-soft">
            <h3 style="font-size:16px">О группе</h3>
            <dl class="kv" style="grid-template-columns:1fr; margin-top:10px">
                <dt>Поток</dt><dd><?= e($group['stream_title']) ?></dd>
                <dt>Уровень</dt><dd><?= e($group['level']) ?></dd>
                <dt>Начало</dt><dd><?= e(fmt_date($group['starts_on'])) ?></dd>
                <dt>Окончание</dt><dd><?= e(fmt_date($group['ends_on'])) ?></dd>
            </dl>
        </div>

        <div class="card">
            <div class="card-head">
                <h3 class="card-title" style="font-size:16px">Занятия</h3>
                <span class="muted small"><?= count($lessons) ?></span>
            </div>
            <?php if (!$lessons): ?>
                <div class="table-empty">Занятий нет</div>
            <?php else: ?>
                <?php foreach ($lessons as $lesson): ?>
                    <a href="<?= e(url('/app/lessons/' . $lesson['id'])) ?>"
                       style="display:flex; justify-content:space-between; gap:10px; padding:10px 0; border-bottom:1px solid var(--line); color:inherit">
                        <span><?= (int)$lesson['lesson_no'] ?>. <?= e($lesson['topic']) ?></span>
                        <span class="muted small nowrap"><?= e(fmt_date($lesson['scheduled_at'], 'd.m H:i')) ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($canDelete): ?>
            <form class="card card-soft" method="post" action="<?= e(url('/app/groups/' . $group['id'] . '/delete')) ?>"
                  data-confirm="Удалить группу вместе с занятиями и составом?">
                <?= csrf_field() ?>
                <h3 style="font-size:16px">Удаление</h3>
                <p class="muted small">Группа, занятия, состав и привязка пособий будут удалены.</p>
                <button class="btn btn-danger btn-block" type="submit">Удалить группу</button>
            </form>
        <?php endif; ?>
    </div>
</div>
