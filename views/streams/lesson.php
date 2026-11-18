<?php
/** @var array $lesson */
/** @var array|null $report */
/** @var array $students */
/** @var bool $canManage */
$statusLabels = ['planned' => 'Запланировано', 'done' => 'Проведено', 'canceled' => 'Отменено'];
$reportStatus = $report['status'] ?? null;
?>
<div class="split">
    <div class="side-stack">
        <div class="card">
            <div style="display:flex; justify-content:space-between; gap:14px; flex-wrap:wrap; align-items:flex-start">
                <div>
                    <div class="muted small">Занятие <?= (int)$lesson['lesson_no'] ?> · <?= e($lesson['stream_title']) ?></div>
                    <h2 style="margin:6px 0 8px; font-size:24px"><?= e($lesson['topic']) ?></h2>
                    <div class="muted">
                        <?= e(fmt_date($lesson['scheduled_at'], 'd.m.Y, H:i')) ?> ·
                        <?= (int)$lesson['duration_min'] ?> минут · <?= e($lesson['group_name']) ?>
                    </div>
                    <div class="muted small" style="margin-top:6px">
                        Преподаватель: <?= e(trim(($lesson['teacher_first'] ?? '') . ' ' . ($lesson['teacher_last'] ?? '')) ?: 'не назначен') ?>
                    </div>
                </div>
                <div style="text-align:right">
                    <?= badge($lesson['status']) ?>
                    <?php if (!empty($lesson['meeting_url'])): ?>
                        <div style="margin-top:10px">
                            <a class="btn btn-sm btn-primary" href="<?= e($lesson['meeting_url']) ?>" target="_blank" rel="noopener">Ссылка на урок</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($canManage): ?>
            <div class="card">
                <div class="card-head"><h3 class="card-title" style="font-size:17px">Отчёт по уроку</h3></div>

                <?php if ($report && $reportStatus === 'submitted'): ?>
                    <div class="alert alert-info">Отчёт отправлен куратору и ждёт проверки. Можно отредактировать и сохранить заново.</div>
                <?php elseif ($report && $reportStatus === 'approved'): ?>
                    <div class="alert alert-success">Отчёт принят куратором<?= $report['review_comment'] ? ': ' . e($report['review_comment']) : '' ?>.</div>
                <?php elseif ($report && $reportStatus === 'returned'): ?>
                    <div class="alert alert-warning">
                        Отчёт возвращён на доработку<?= $report['review_comment'] ? ': ' . e($report['review_comment']) : '' ?>.
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= e(url('/app/lessons/' . $lesson['id'] . '/report')) ?>" enctype="multipart/form-data" class="form">
                    <?= csrf_field() ?>

                    <div class="form-row">
                        <label class="field">
                            <span>Присутствовали</span>
                            <input type="number" name="attendance_present" min="0" max="50" value="<?= (int)($report['attendance_present'] ?? count($students)) ?>">
                        </label>
                        <label class="field">
                            <span>Отсутствовали</span>
                            <input type="number" name="attendance_absent" min="0" max="50" value="<?= (int)($report['attendance_absent'] ?? 0) ?>">
                        </label>
                    </div>

                    <label class="field">
                        <span>Что прошли на занятии</span>
                        <textarea name="summary" placeholder="Тема, упражнения, что получилось"><?= e($report['summary'] ?? '') ?></textarea>
                    </label>

                    <label class="field">
                        <span>Домашнее задание</span>
                        <textarea name="homework_text" placeholder="Что задать ученикам"><?= e($report['homework_text'] ?? '') ?></textarea>
                    </label>

                    <label class="field">
                        <span>Замечания и сложности</span>
                        <textarea name="issues" rows="2" placeholder="Необязательно"><?= e($report['issues'] ?? '') ?></textarea>
                    </label>

                    <div class="form-row">
                        <label class="field">
                            <span>Фото начала урока</span>
                            <input type="file" name="photo_start" accept="image/jpeg,image/png,image/webp">
                        </label>
                        <label class="field">
                            <span>Фото завершения урока</span>
                            <input type="file" name="photo_end" accept="image/jpeg,image/png,image/webp">
                        </label>
                    </div>
                    <p class="hint muted small" style="margin:0">
                        Для отправки отчёта нужны оба фото. Дата и время фиксируются автоматически при загрузке.
                    </p>

                    <?php if (!empty($report['photo_start']) || !empty($report['photo_end'])): ?>
                        <div style="display:flex; gap:16px; flex-wrap:wrap">
                            <?php if (!empty($report['photo_start'])): ?>
                                <div>
                                    <a href="<?= e(file_url($report['photo_start'])) ?>" target="_blank" rel="noopener">
                                        <img class="thumb" src="<?= e(file_url($report['photo_start'])) ?>" alt="Начало урока">
                                    </a>
                                    <div class="muted small" style="margin-top:6px">
                                        Начало · <?= e(fmt_dt($report['photo_start_at'] ?? null)) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($report['photo_end'])): ?>
                                <div>
                                    <a href="<?= e(file_url($report['photo_end'])) ?>" target="_blank" rel="noopener">
                                        <img class="thumb" src="<?= e(file_url($report['photo_end'])) ?>" alt="Конец урока">
                                    </a>
                                    <div class="muted small" style="margin-top:6px">
                                        Конец · <?= e(fmt_dt($report['photo_end_at'] ?? null)) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit" name="action" value="submit">Отправить отчёт</button>
                        <button class="btn btn-ghost" type="submit" name="action" value="draft">Сохранить черновик</button>
                        <?php if ($report): ?>
                            <a class="btn btn-ghost" href="<?= e(url('/app/reports/' . $report['id'])) ?>">Карточка отчёта</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        <?php elseif ($report): ?>
            <div class="card">
                <div class="card-head"><h3 class="card-title" style="font-size:17px">Отчёт преподавателя</h3></div>
                <dl class="kv">
                    <dt>Статус</dt><dd><?= badge($report['status']) ?></dd>
                    <dt>Посещение</dt><dd><?= (int)$report['attendance_present'] ?> из <?= count($students) ?></dd>
                    <dt>Что прошли</dt><dd><?= nl2br(e($report['summary'] ?: '—')) ?></dd>
                    <dt>Домашнее задание</dt><dd><?= nl2br(e($report['homework_text'] ?: '—')) ?></dd>
                </dl>
                <a class="btn btn-ghost btn-sm" style="margin-top:12px" href="<?= e(url('/app/reports/' . $report['id'])) ?>">Открыть отчёт</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="side-stack">
        <div class="card">
            <div class="card-head"><h3 class="card-title" style="font-size:16px">Ученики группы</h3></div>
            <?php if (!$students): ?>
                <div class="table-empty">В группе пока никого нет</div>
            <?php else: ?>
                <?php foreach ($students as $student): ?>
                    <div style="padding:9px 0; border-bottom:1px solid var(--line); display:flex; gap:10px; align-items:center">
                        <span class="avatar" style="width:30px;height:30px;font-size:11px"><?= e(initials($student['first_name'], $student['last_name'])) ?></span>
                        <div>
                            <div style="font-weight:600"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></div>
                            <div class="muted small"><?= e($student['login']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($canManage): ?>
            <form class="card" method="post" action="<?= e(url('/app/lessons/' . $lesson['id'])) ?>">
                <?= csrf_field() ?>
                <h3 style="font-size:16px; margin-bottom:12px">Настройки занятия</h3>
                <div class="form">
                    <label class="field">
                        <span>Тема</span>
                        <input type="text" name="topic" value="<?= e($lesson['topic']) ?>">
                    </label>
                    <label class="field">
                        <span>Дата и время</span>
                        <input type="datetime-local" name="scheduled_at" value="<?= e(date('Y-m-d\TH:i', strtotime($lesson['scheduled_at']))) ?>">
                    </label>
                    <div class="form-row">
                        <label class="field">
                            <span>Минут</span>
                            <input type="number" name="duration_min" min="15" max="240" value="<?= (int)$lesson['duration_min'] ?>">
                        </label>
                        <label class="field">
                            <span>Статус</span>
                            <select name="status">
                                <?php foreach ($statusLabels as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= $lesson['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                    <label class="field">
                        <span>Ссылка на урок</span>
                        <input type="url" name="meeting_url" value="<?= e($lesson['meeting_url'] ?? '') ?>" placeholder="https://...">
                    </label>
                    <button class="btn btn-soft" type="submit">Сохранить</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
