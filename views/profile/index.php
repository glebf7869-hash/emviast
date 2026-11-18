<?php
/** @var array $item */
?>
<div class="split">
    <div class="card">
        <div style="display:flex; align-items:center; gap:14px; margin-bottom:16px">
            <span class="avatar" style="width:52px;height:52px;font-size:18px"><?= e(initials($item['first_name'], $item['last_name'])) ?></span>
            <div>
                <h2 class="card-title" style="margin-bottom:2px"><?= e($item['first_name'] . ' ' . $item['last_name']) ?></h2>
                <div class="muted small"><?= e(role_label($item['role'])) ?> · <?= e($item['login']) ?></div>
            </div>
        </div>
        <dl class="kv">
            <dt>E-mail</dt><dd><?= e($item['email']) ?></dd>
            <dt>Часовой пояс</dt><dd><?= e($item['timezone']) ?></dd>
            <dt>Статус</dt><dd><?= badge($item['status']) ?></dd>
        </dl>
        <?php if ((int)$item['must_change_password'] === 1): ?>
            <div class="alert alert-warning" style="margin-top:14px">Вы вошли с временным паролем — смените его ниже.</div>
        <?php endif; ?>
    </div>

    <form class="card" method="post" action="<?= e(url('/app/profile')) ?>">
        <?= csrf_field() ?>
        <h3 style="font-size:16px; margin-bottom:12px">Смена пароля</h3>
        <div class="form">
            <label class="field">
                <span>Текущий пароль</span>
                <input type="password" name="current_password" autocomplete="current-password" required>
            </label>
            <label class="field">
                <span>Новый пароль</span>
                <input type="password" name="new_password" minlength="8" autocomplete="new-password" required>
            </label>
            <label class="field">
                <span>Повторите пароль</span>
                <input type="password" name="repeat_password" minlength="8" autocomplete="new-password" required>
            </label>
            <button class="btn btn-primary" type="submit">Обновить пароль</button>
        </div>
    </form>
</div>
