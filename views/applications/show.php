<?php
/** @var array $item */
$typeLabels = ['student' => 'Ученик', 'teacher' => 'Преподаватель', 'curator' => 'Куратор', 'smm' => 'SMM'];
$canReview = in_array(user_role(), ['root', 'admin', 'curator'], true);
$canCreateAccount = !$item['user_id'] && ($item['type'] === 'student' || in_array(user_role(), ['root', 'admin'], true));
?>
<div class="split">
    <div class="card">
        <div class="card-head">
            <div>
                <h2 class="card-title" style="margin-bottom:2px"><?= e($item['first_name'] . ' ' . $item['last_name']) ?></h2>
                <div class="muted small"><?= e($typeLabels[$item['type']] ?? $item['type']) ?> · заявка №<?= (int)$item['id'] ?></div>
            </div>
            <?= badge($item['status']) ?>
        </div>

        <dl class="kv">
            <dt>E-mail</dt><dd><?= e($item['email']) ?></dd>
            <dt>Телефон</dt><dd><?= e($item['phone'] ?: '—') ?></dd>
            <?php if ($item['age']): ?><dt>Возраст</dt><dd><?= (int)$item['age'] ?></dd><?php endif; ?>
            <?php if ($item['level']): ?><dt>Уровень</dt><dd><?= e($item['level']) ?></dd><?php endif; ?>
            <?php if ($item['timezone']): ?><dt>Часовой пояс</dt><dd><?= e($item['timezone']) ?></dd><?php endif; ?>
            <?php if ($item['hours']): ?><dt>Часов в неделю</dt><dd><?= (int)$item['hours'] ?></dd><?php endif; ?>
            <?php if (!empty($item['groups_ready'])): ?><dt>Готов взять групп</dt><dd><?= (int)$item['groups_ready'] ?></dd><?php endif; ?>
            <?php if (!empty($item['schedule_days'])): ?><dt>Удобные дни</dt><dd><?= e(format_schedule_days($item['schedule_days'])) ?></dd><?php endif; ?>
            <?php if (!empty($item['schedule_time'])): ?><dt>Удобное время</dt><dd><?= e(format_time_slots($item['schedule_time'])) ?></dd><?php endif; ?>
            <dt>Дата заявки</dt><dd><?= e(fmt_dt($item['created_at'])) ?></dd>
            <?php if ($item['reviewed_at']): ?><dt>Рассмотрена</dt><dd><?= e(fmt_dt($item['reviewed_at'])) ?></dd><?php endif; ?>
            <?php if ($item['review_comment']): ?><dt>Комментарий</dt><dd><?= e($item['review_comment']) ?></dd><?php endif; ?>
        </dl>

        <?php if ($item['goal']): ?>
            <div class="divider"></div>
            <h3 style="font-size:15px">Цель</h3>
            <p class="muted"><?= nl2br(e($item['goal'])) ?></p>
        <?php endif; ?>

        <?php if ($item['experience']): ?>
            <div class="divider"></div>
            <h3 style="font-size:15px">Опыт</h3>
            <p class="muted"><?= nl2br(e($item['experience'])) ?></p>
        <?php endif; ?>

        <?php if ($item['message']): ?>
            <div class="divider"></div>
            <h3 style="font-size:15px">Комментарий</h3>
            <p class="muted"><?= nl2br(e($item['message'])) ?></p>
        <?php endif; ?>
    </div>

    <div class="side-stack">
        <?php if ($canReview): ?>
            <form class="card" method="post" action="<?= e(url('/app/applications/' . $item['id'] . '/status')) ?>">
                <?= csrf_field() ?>
                <h3 style="font-size:16px">Решение по заявке</h3>
                <label class="field" style="margin-top:10px">
                    <span>Статус</span>
                    <select name="status">
                        <?php foreach (['new' => 'Новая', 'in_review' => 'На рассмотрении', 'approved' => 'Одобрена', 'waitlist' => 'Лист ожидания', 'rejected' => 'Отклонена'] as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $item['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="field" style="margin-top:10px">
                    <span>Комментарий</span>
                    <textarea name="comment" rows="3"><?= e($item['review_comment']) ?></textarea>
                </label>
                <button class="btn btn-primary btn-block" type="submit" style="margin-top:12px">Сохранить</button>
            </form>
        <?php endif; ?>

        <?php if ($canCreateAccount): ?>
            <form class="card card-soft" method="post" action="<?= e(url('/app/applications/' . $item['id'] . '/account')) ?>">
                <?= csrf_field() ?>
                <h3 style="font-size:16px">Аккаунт</h3>
                <p class="muted small">Логин сгенерируется по имени и фамилии, пароль — случайный. Данные придут на почту.</p>
                <button class="btn btn-soft btn-block" type="submit">Создать аккаунт</button>
            </form>
        <?php elseif ($item['user_id']): ?>
            <div class="card card-soft">
                <h3 style="font-size:16px">Аккаунт создан</h3>
                <p class="muted small">Пользователь привязан к заявке.</p>
                <a class="btn btn-ghost btn-block" href="<?= e(url('/app/users/' . $item['user_id'] . '/edit')) ?>">Открыть аккаунт</a>
            </div>
        <?php endif; ?>

        <a class="btn btn-ghost" href="<?= e(url('/app/applications')) ?>">← Ко всем заявкам</a>
    </div>
</div>
