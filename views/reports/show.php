<?php
/** @var array $report */
/** @var bool $canReview */
?>
<div class="split">
    <div class="card">
        <div class="card-head">
            <div>
                <div class="muted small"><?= e($report['stream_title']) ?> · <?= e($report['group_name']) ?></div>
                <h2 class="card-title" style="font-size:22px; margin-top:4px">Занятие <?= (int)$report['lesson_no'] ?> · <?= e($report['topic']) ?></h2>
            </div>
            <?= badge($report['status']) ?>
        </div>

        <div class="muted small" style="margin-bottom:16px">
            <?= e(fmt_date($report['scheduled_at'], 'd.m.Y, H:i')) ?> ·
            преподаватель <?= e(trim(($report['teacher_first'] ?? '') . ' ' . ($report['teacher_last'] ?? '')) ?: '—') ?>
            <?php if ($report['submitted_at']): ?> · отправлен <?= e(fmt_dt($report['submitted_at'])) ?><?php endif; ?>
        </div>

        <div class="grid grid-2" style="gap:14px; margin-bottom:18px">
            <div class="card card-soft" style="padding:16px">
                <div class="muted small">Присутствовали</div>
                <div class="stat-value" style="font-size:24px"><?= (int)$report['attendance_present'] ?></div>
            </div>
            <div class="card card-soft" style="padding:16px">
                <div class="muted small">Отсутствовали</div>
                <div class="stat-value" style="font-size:24px"><?= (int)$report['attendance_absent'] ?></div>
            </div>
        </div>

        <h3 style="font-size:15px">Что прошли</h3>
        <p class="muted"><?= nl2br(e($report['summary'] ?: '—')) ?></p>

        <h3 style="font-size:15px">Домашнее задание</h3>
        <p class="muted"><?= nl2br(e($report['homework_text'] ?: '—')) ?></p>

        <?php if ($report['issues']): ?>
            <h3 style="font-size:15px">Замечания</h3>
            <p class="muted"><?= nl2br(e($report['issues'])) ?></p>
        <?php endif; ?>

        <?php if ($report['photo_start'] || $report['photo_end']): ?>
            <h3 style="font-size:15px">Фотоотчёт</h3>
            <div style="display:flex; gap:16px; margin-top:8px; flex-wrap:wrap">
                <?php if ($report['photo_start']): ?>
                    <div>
                        <a href="<?= e(file_url($report['photo_start'])) ?>" target="_blank" rel="noopener">
                            <img class="thumb" src="<?= e(file_url($report['photo_start'])) ?>" alt="Начало урока">
                        </a>
                        <div class="muted small" style="margin-top:6px">
                            Начало · <?= e(fmt_dt($report['photo_start_at'] ?? null)) ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($report['photo_end']): ?>
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
    </div>

    <div class="side-stack">
        <?php if ($canReview): ?>
            <form class="card" method="post" action="<?= e(url('/app/reports/' . $report['id'] . '/review')) ?>">
                <?= csrf_field() ?>
                <h3 style="font-size:16px">Проверка</h3>
                <label class="field" style="margin-top:10px">
                    <span>Комментарий</span>
                    <textarea name="comment" rows="3"><?= e($report['review_comment'] ?? '') ?></textarea>
                </label>
                <div class="form-actions" style="margin-top:12px">
                    <button class="btn btn-primary" type="submit" name="decision" value="approved">Принять</button>
                    <button class="btn btn-danger" type="submit" name="decision" value="returned">Вернуть</button>
                </div>
            </form>
        <?php endif; ?>

        <div class="card card-soft">
            <h3 style="font-size:16px">Служебное</h3>
            <dl class="kv" style="grid-template-columns:1fr; margin-top:8px">
                <dt>Проверил</dt>
                <dd><?= e($report['reviewed_at'] ? trim(($report['reviewer_first'] ?? '') . ' ' . ($report['reviewer_last'] ?? '')) . ' · ' . fmt_dt($report['reviewed_at']) : '—') ?></dd>
                <dt>Комментарий проверки</dt>
                <dd><?= e($report['review_comment'] ?: '—') ?></dd>
            </dl>
            <a class="btn btn-ghost btn-block" style="margin-top:12px" href="<?= e(url('/app/reports')) ?>">← Все отчёты</a>
        </div>
    </div>
</div>
