<?php
/** @var array $stream */
/** @var array $groups */
/** @var array $membersByGroup */
/** @var array $availableStudents */
/** @var array $teachers */
/** @var array $curators */
/** @var bool $canManage */
/** @var bool $canEdit */
?>
<div class="card" style="margin-bottom:20px">
    <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start">
        <div>
            <div style="display:flex; gap:10px; align-items:center; margin-bottom:8px">
                <h2 style="margin:0; font-size:24px"><?= e($stream['title']) ?></h2>
                <?= badge($stream['status']) ?>
            </div>
            <div class="muted">
                <?= e($stream['language']) ?> · <?= e($stream['level']) ?> ·
                <?= e(fmt_date($stream['starts_on'])) ?> — <?= e(fmt_date($stream['ends_on'])) ?> ·
                <?= (int)$stream['lessons_total'] ?> занятий
            </div>
            <?php if (!empty($stream['curator_first'])): ?>
                <div class="muted small">Куратор потока: <?= e($stream['curator_first'] . ' ' . $stream['curator_last']) ?></div>
            <?php endif; ?>
            <?php if ($stream['description']): ?>
                <p class="muted" style="margin-top:12px; max-width:720px"><?= e($stream['description']) ?></p>
            <?php endif; ?>
        </div>

        <?php if ($canManage): ?>
            <form method="post" action="<?= e(url('/app/streams/' . $stream['id'])) ?>" style="display:flex; gap:8px; align-items:center">
                <?= csrf_field() ?>
                <input type="hidden" name="title" value="<?= e($stream['title']) ?>">
                <input type="hidden" name="starts_on" value="<?= e($stream['starts_on']) ?>">
                <input type="hidden" name="ends_on" value="<?= e($stream['ends_on']) ?>">
                <input type="hidden" name="lessons_total" value="<?= (int)$stream['lessons_total'] ?>">
                <input type="hidden" name="curator_id" value="<?= (int)$stream['curator_id'] ?>">
                <select name="status" style="width:auto">
                    <?php foreach (['draft' => 'Черновик', 'recruiting' => 'Набор', 'active' => 'Активный', 'finished' => 'Завершён'] as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= $stream['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-soft" type="submit">Сохранить</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if ($canEdit): ?>
        <details style="margin-top:18px">
            <summary class="small" style="cursor:pointer; color:var(--brand); font-weight:700">Редактировать поток</summary>
            <form method="post" action="<?= e(url('/app/streams/' . $stream['id'])) ?>" class="form" style="margin-top:14px; max-width:760px">
                <?= csrf_field() ?>
                <div class="form-row">
                    <label class="field">
                        <span>Название</span>
                        <input type="text" name="title" value="<?= e($stream['title']) ?>">
                    </label>
                    <label class="field">
                        <span>Куратор</span>
                        <select name="curator_id">
                            <option value="0">Не назначен</option>
                            <?php foreach ($curators as $curator): ?>
                                <option value="<?= (int)$curator['id'] ?>" <?= (int)$stream['curator_id'] === (int)$curator['id'] ? 'selected' : '' ?>>
                                    <?= e($curator['first_name'] . ' ' . $curator['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div class="form-row">
                    <label class="field">
                        <span>Начало</span>
                        <input type="date" name="starts_on" value="<?= e($stream['starts_on']) ?>">
                    </label>
                    <label class="field">
                        <span>Окончание</span>
                        <input type="date" name="ends_on" value="<?= e($stream['ends_on']) ?>">
                    </label>
                </div>
                <div class="form-row">
                    <label class="field">
                        <span>Занятий</span>
                        <input type="number" name="lessons_total" min="1" max="60" value="<?= (int)$stream['lessons_total'] ?>">
                    </label>
                    <label class="field">
                        <span>Статус</span>
                        <select name="status">
                            <?php foreach (['draft' => 'Черновик', 'recruiting' => 'Набор', 'active' => 'Активный', 'finished' => 'Завершён'] as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= $stream['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <button class="btn btn-primary" type="submit">Сохранить изменения</button>
            </form>
        </details>
    <?php endif; ?>
</div>

<div class="split">
    <div class="side-stack">
        <?php if (!$groups): ?>
            <div class="empty">В потоке пока нет групп<?= $canManage ? ' — добавьте первую справа' : '' ?>.</div>
        <?php endif; ?>

        <?php foreach ($groups as $group): ?>
            <?php $members = $membersByGroup[(int)$group['id']] ?? []; ?>
            <div class="card">
                <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; flex-wrap:wrap">
                    <div>
                        <h3 style="margin-bottom:4px; font-size:18px"><?= e($group['name']) ?></h3>
                        <div class="muted small">
                            <?= (int)$group['capacity'] ?> мест ·
                            <?= e($group['schedule_note'] ?: 'расписание не задано') ?> ·
                            учеников: <?= count($members) ?>
                        </div>
                    </div>
                    <div style="display:flex; gap:8px; align-items:center">
                        <?= badge($group['status']) ?>
                        <a class="btn btn-sm btn-ghost" href="<?= e(url('/app/groups/' . $group['id'])) ?>">Открыть</a>
                    </div>
                </div>

                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-top:14px">
                    <?php if ($canManage): ?>
                        <form method="post" action="<?= e(url('/app/groups/' . $group['id'] . '/teacher')) ?>" style="display:flex; gap:8px; align-items:center">
                            <?= csrf_field() ?>
                            <select name="teacher_id" style="width:auto">
                                <option value="0">Без преподавателя</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= (int)$teacher['id'] ?>" <?= (int)$group['teacher_id'] === (int)$teacher['id'] ? 'selected' : '' ?>>
                                        <?= e($teacher['first_name'] . ' ' . $teacher['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-sm btn-soft" type="submit">Назначить</button>
                        </form>
                    <?php else: ?>
                        <span class="chip">Преподаватель: <?= e(trim(($group['teacher_first'] ?? '') . ' ' . ($group['teacher_last'] ?? '')) ?: 'не назначен') ?></span>
                    <?php endif; ?>
                </div>

                <div class="table-wrap" style="margin-top:14px">
                    <table class="table">
                        <thead>
                        <tr><th>Ученик</th><th>Логин</th><th>Прогресс</th><?php if ($canManage): ?><th></th><?php endif; ?></tr>
                        </thead>
                        <tbody>
                        <?php if (!$members): ?>
                            <tr><td colspan="<?= $canManage ? 4 : 3 ?>"><div class="table-empty">В группе пока нет учеников</div></td></tr>
                        <?php endif; ?>

                        <?php foreach ($members as $member): ?>
                            <tr>
                                <td><strong><?= e($member['first_name'] . ' ' . $member['last_name']) ?></strong></td>
                                <td><code><?= e($member['login']) ?></code></td>
                                <td style="min-width:240px">
                                    <?php if ($canManage): ?>
                                        <form method="post" action="<?= e(url('/app/groups/' . $group['id'] . '/progress')) ?>" style="display:flex; gap:6px; align-items:center">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="student_id" value="<?= (int)$member['id'] ?>">
                                            <input type="number" name="percent" min="0" max="100" value="<?= (int)($member['percent'] ?? 0) ?>"
                                                   style="width:78px; padding:7px 9px">
                                            <input type="text" name="comment" value="<?= e($member['comment'] ?? '') ?>" placeholder="комментарий"
                                                   style="width:150px; padding:7px 9px">
                                            <button class="btn btn-sm btn-soft" type="submit">OK</button>
                                        </form>
                                    <?php else: ?>
                                        <div class="progress" style="max-width:200px"><span style="width:<?= (int)($member['percent'] ?? 0) ?>%"></span></div>
                                    <?php endif; ?>
                                </td>
                                <?php if ($canManage): ?>
                                    <td>
                                        <form method="post" action="<?= e(url('/app/groups/' . $group['id'] . '/students/' . $member['id'] . '/remove')) ?>"
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

                <?php if ($canManage): ?>
                    <form method="post" action="<?= e(url('/app/groups/' . $group['id'] . '/students')) ?>"
                          style="display:flex; gap:8px; margin-top:14px; flex-wrap:wrap; align-items:center">
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
                        <?php if (!$availableStudents): ?>
                            <span class="muted small">Свободных учеников нет — сначала создайте аккаунты.</span>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="side-stack">
        <?php if ($canManage): ?>
            <form class="card" method="post" action="<?= e(url('/app/streams/' . $stream['id'] . '/groups')) ?>">
                <?= csrf_field() ?>
                <h3 style="font-size:16px">Новая группа</h3>
                <div class="form" style="margin-top:12px">
                    <label class="field">
                        <span>Название</span>
                        <input type="text" name="name" placeholder="Группа C" required>
                    </label>
                    <label class="field">
                        <span>Преподаватель</span>
                        <select name="teacher_id">
                            <option value="0">Позже</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= (int)$teacher['id'] ?>"><?= e($teacher['first_name'] . ' ' . $teacher['last_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="form-row">
                        <label class="field">
                            <span>Мест</span>
                            <input type="number" name="capacity" min="1" max="20" value="5">
                        </label>
                        <label class="field">
                            <span>Расписание</span>
                            <input type="text" name="schedule_note" placeholder="Пн и Ср, 18:00">
                        </label>
                    </div>
                    <button class="btn btn-primary" type="submit">Создать группу</button>
                    <p class="hint muted small" style="margin:0">Занятия появятся в расписании автоматически.</p>
                </div>
            </form>
        <?php endif; ?>

        <div class="card card-soft">
            <h3 style="font-size:16px">Итоги потока</h3>
            <dl class="kv" style="grid-template-columns:1fr; margin-top:10px">
                <dt>Групп</dt><dd><?= count($groups) ?></dd>
                <dt>Учеников</dt>
                <dd><?= array_sum(array_map(static fn (array $m): int => count($m), $membersByGroup)) ?></dd>
                <dt>Занятий в плане</dt><dd><?= (int)$stream['lessons_total'] ?></dd>
            </dl>
            <a class="btn btn-ghost btn-block" style="margin-top:14px" href="<?= e(url('/app/schedule')) ?>">Расписание</a>
        </div>
    </div>
</div>
