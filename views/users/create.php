<?php
/** @var array $allowedRoles */
/** @var array|null $application */
/** @var string $prefillRole */
?>
<?php $application = $application ?: []; ?>
<div class="split">
    <form class="card" method="post" action="<?= e(url('/app/users')) ?>">
        <?= csrf_field() ?>
        <?php if ($application): ?>
            <input type="hidden" name="application_id" value="<?= (int)$application['id'] ?>">
            <div class="alert alert-info">
                Аккаунт создаётся по заявке №<?= (int)$application['id'] ?> —
                <?= e($application['first_name'] . ' ' . $application['last_name']) ?>.
                После сохранения заявка станет одобренной.
            </div>
        <?php endif; ?>

        <h2 class="card-title" style="margin-bottom:16px">Новый аккаунт</h2>

        <div class="form">
            <label class="field">
                <span>Роль</span>
                <select name="role" required>
                    <?php foreach ($allowedRoles as $roleKey): ?>
                        <option value="<?= e($roleKey) ?>" <?= $prefillRole === $roleKey ? 'selected' : '' ?>><?= e(role_label($roleKey)) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <div class="form-row">
                <label class="field">
                    <span>Имя</span>
                    <input type="text" name="first_name" value="<?= e(old('first_name', $application['first_name'] ?? '')) ?>" required>
                </label>
                <label class="field">
                    <span>Фамилия</span>
                    <input type="text" name="last_name" value="<?= e(old('last_name', $application['last_name'] ?? '')) ?>" required>
                </label>
            </div>

            <label class="field">
                <span>E-mail</span>
                <input type="email" name="email" value="<?= e(old('email', $application['email'] ?? '')) ?>" required>
                <span class="hint">На этот адрес уйдёт письмо с логином и паролем</span>
            </label>

            <div class="form-row">
                <label class="field">
                    <span>Логин</span>
                    <input type="text" name="login" value="<?= e(old('login')) ?>" placeholder="ivan.petrov">
                    <span class="hint">Пусто — сгенерируем по имени и фамилии</span>
                </label>
                <label class="field">
                    <span>Телефон</span>
                    <input type="text" name="phone" value="<?= e(old('phone', $application['phone'] ?? '')) ?>">
                </label>
            </div>

            <label class="field">
                <span>Часовой пояс</span>
                <input type="text" name="timezone" value="<?= e(old('timezone', 'Europe/Moscow')) ?>" placeholder="Europe/Moscow">
            </label>

            <label class="checkbox">
                <input type="checkbox" name="generate_password" value="1" checked data-toggle-target="#password-row">
                <span>Сгенерировать случайный пароль (10 символов)</span>
            </label>

            <div id="password-row">
                <label class="field">
                    <span>Пароль</span>
                    <input type="text" name="password" placeholder="Минимум 8 символов">
                </label>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Создать аккаунт</button>
                <a class="btn btn-ghost" href="<?= e(url('/app/users')) ?>">Отмена</a>
            </div>
        </div>
    </form>

    <div class="card card-soft" style="padding:26px">
        <h3 style="font-size:16px">Как это работает</h3>
        <ul class="muted small" style="padding-left:18px; line-height:1.7">
            <li>Логин собирается из имени и фамилии: <code>ivan.petrov</code>.</li>
            <li>Если такой логин занят, добавится число.</li>
            <li>Пароль генерируется из безопасного алфавита.</li>
            <li>Участник получит письмо и сменит пароль при первом входе.</li>
            <li>Кураторы создают только учеников.</li>
        </ul>
    </div>
</div>
