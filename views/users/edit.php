<?php
/** @var array $item */
/** @var array $allowedRoles */
?>
<div class="split">
    <form class="card" method="post" action="<?= e(url('/app/users/' . $item['id'])) ?>">
        <?= csrf_field() ?>
        <div class="card-head">
            <div>
                <h2 class="card-title" style="margin-bottom:2px"><?= e($item['first_name'] . ' ' . $item['last_name']) ?></h2>
                <div class="muted small">Логин: <code><?= e($item['login']) ?></code></div>
            </div>
            <?= badge($item['status']) ?>
        </div>

        <div class="form">
            <div class="form-row">
                <label class="field">
                    <span>Имя</span>
                    <input type="text" name="first_name" value="<?= e($item['first_name']) ?>" required>
                </label>
                <label class="field">
                    <span>Фамилия</span>
                    <input type="text" name="last_name" value="<?= e($item['last_name']) ?>" required>
                </label>
            </div>

            <label class="field">
                <span>E-mail</span>
                <input type="email" name="email" value="<?= e($item['email']) ?>" required>
            </label>

            <?php if (user_role() === 'root'): ?>
                <label class="field">
                    <span>Роль</span>
                    <select name="role">
                        <?php foreach (array_merge(['root'], $allowedRoles) as $roleKey): ?>
                            <option value="<?= e($roleKey) ?>" <?= $item['role'] === $roleKey ? 'selected' : '' ?>><?= e(role_label($roleKey)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="hint">Смена роли доступна руководителю</span>
                </label>
            <?php endif; ?>

            <label class="field">
                <span>Статус</span>
                <select name="status">
                    <option value="active" <?= $item['status'] === 'active' ? 'selected' : '' ?>>Активен</option>
                    <option value="blocked" <?= $item['status'] === 'blocked' ? 'selected' : '' ?>>Заблокирован</option>
                </select>
            </label>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Сохранить</button>
                <a class="btn btn-ghost" href="<?= e(url('/app/users')) ?>">К списку</a>
            </div>
        </div>
    </form>

    <div class="side-stack">
        <div class="card card-soft">
            <h3 style="font-size:16px">Пароль</h3>
            <p class="muted small">Сброс создаёт новый случайный пароль и отправляет его на почту.</p>
            <form method="post" action="<?= e(url('/app/users/' . $item['id'] . '/reset')) ?>"
                  data-confirm="Сбросить пароль и отправить новый на почту?">
                <?= csrf_field() ?>
                <button class="btn btn-soft btn-block" type="submit">Сбросить пароль</button>
            </form>
        </div>

        <div class="card card-soft">
            <h3 style="font-size:16px">Информация</h3>
            <dl class="kv" style="grid-template-columns:1fr">
                <dt>Роль</dt><dd><?= e(role_label($item['role'])) ?></dd>
                <dt>Часовой пояс</dt><dd><?= e($item['timezone']) ?></dd>
                <dt>Создан</dt><dd><?= e(fmt_dt($item['created_at'])) ?></dd>
                <dt>Последний вход</dt><dd><?= e($item['last_login_at'] ? fmt_dt($item['last_login_at']) : '—') ?></dd>
            </dl>
        </div>
    </div>
</div>
