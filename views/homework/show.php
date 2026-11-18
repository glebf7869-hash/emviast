<?php
/** @var array $homework */
/** @var array $submissions */
/** @var array|null $mySubmission */
/** @var bool $isStudent */
?>
<div class="split">
    <div class="card">
        <div class="muted small"><?= e($homework['stream_title']) ?> · <?= e($homework['group_name']) ?></div>
        <h2 style="margin:8px 0 10px; font-size:24px"><?= e($homework['title']) ?></h2>
        <div class="muted small" style="margin-bottom:16px">
            Опубликовано <?= e(fmt_dt($homework['created_at'])) ?>
            <?php if ($homework['due_at']): ?> · сдать до <?= e(fmt_dt($homework['due_at'])) ?><?php endif; ?>
        </div>

        <p class="muted" style="font-size:15.5px"><?= nl2br(e($homework['description'] ?: 'Описание не добавлено.')) ?></p>

        <?php if ($isStudent): ?>
            <div class="divider"></div>
            <h3 style="font-size:16px">Мой ответ</h3>
            <?php if ($mySubmission): ?>
                <div class="alert alert-<?= $mySubmission['status'] === 'checked' ? 'success' : 'info' ?>">
                    <?= $mySubmission['status'] === 'checked' ? 'Работа проверена' : 'Работа отправлена' ?>
                    <?php if ($mySubmission['grade'] !== null): ?> · оценка <?= (int)$mySubmission['grade'] ?><?php endif; ?>
                    <?php if ($mySubmission['comment']): ?><br><?= e($mySubmission['comment']) ?><?php endif; ?>
                </div>
            <?php endif; ?>

            <form class="form" method="post" action="<?= e(url('/app/homework/' . $homework['id'] . '/submit')) ?>">
                <?= csrf_field() ?>
                <label class="field">
                    <span>Ответ или ссылка на работу</span>
                    <textarea name="answer" required placeholder="Напишите ответ сюда"><?= e($mySubmission['answer'] ?? '') ?></textarea>
                </label>
                <button class="btn btn-primary" type="submit"><?= $mySubmission ? 'Отправить заново' : 'Отправить работу' ?></button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (!$isStudent): ?>
        <div class="card">
            <div class="card-head"><h3 class="card-title" style="font-size:16px">Работы учеников</h3></div>
            <?php if (!$submissions): ?>
                <div class="table-empty">В группе нет учеников</div>
            <?php endif; ?>

            <?php foreach ($submissions as $row): ?>
                <div style="padding:12px 0; border-bottom:1px solid var(--line)">
                    <div style="display:flex; justify-content:space-between; gap:8px; align-items:center">
                        <strong><?= e($row['first_name'] . ' ' . $row['last_name']) ?></strong>
                        <?= badge($row['status'] ?: 'assigned') ?>
                    </div>

                    <?php if ($row['answer']): ?>
                        <div class="muted small" style="margin:8px 0"><?= nl2br(e(mb_substr($row['answer'], 0, 200))) ?></div>
                    <?php endif; ?>

                    <?php if ($row['submission_id']): ?>
                        <form method="post" action="<?= e(url('/app/homework/' . $homework['id'] . '/check')) ?>"
                              style="display:flex; gap:6px; align-items:center; margin-top:8px">
                            <?= csrf_field() ?>
                            <input type="hidden" name="student_id" value="<?= (int)$row['student_id'] ?>">
                            <input type="number" name="grade" min="0" max="100" value="<?= (int)($row['grade'] ?? 0) ?>" style="width:74px; padding:6px 8px">
                            <input type="text" name="comment" value="<?= e($row['comment'] ?? '') ?>" placeholder="комментарий" style="padding:6px 8px">
                            <button class="btn btn-sm btn-soft" type="submit">OK</button>
                        </form>
                    <?php else: ?>
                        <div class="muted small" style="margin-top:6px">Работа не сдана</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
