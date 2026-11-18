<?php
/** @var array $items */
/** @var string $filterRole */
/** @var string $q */
/** @var array|null $credentials */
$roles = ['root' => 'Руководитель', 'admin' => 'Администратор', 'curator' => 'Куратор', 'teacher' => 'Преподаватель', 'smm' => 'SMM', 'student' => 'Ученик'];
$isCurator = user_role() === 'curator';
?>
<?php if ($credentials): ?>
    <div class="card" style="border-color:#CDE7D9; background:#F6FBF8; margin-bottom:20px">
        <div class="card-head" style="margin-bottom:10px">
            <h2 class="card-title">Данные для входа</h2>
            <span class="badge badge-green">только что создано</span>
        </div>
        <p class="muted small">Передайте их участнику. Пароль показан один раз — на почту отправлена копия.</p>
        <div class="grid grid-2" style="margin-top:12px">
            <div class="card card-soft" style="padding:16px">
                <div class="muted small">Логин</div>
                <div style="font-weight:800; font-size:18px"><?= e($credentials['login']) ?></div>
            </div>
            <div class="card card-soft" style="padding:16px">
                <div class="muted small">Пароль</div>
                <div style="font-weight:800; font-size:18px"><?= e($credentials['password']) ?></div>
            </div>
        </div>
        <div class="small muted" style="margin-top:10px">
            <?= e($credentials['name']) ?> · <?= e(role_label($credentials['role'])) ?> · <?= e($credentials['email']) ?>
        </div>
        <button class="btn btn-sm btn-ghost" style="margin-top:12px" type="button"
                data-copy="Логин: <?= e($credentials['login']) ?> Пароль: <?= e($credentials['password']) ?>">Скопировать</button>
    </div>
<?php endif; ?>

<form class="filters" method="get" action="<?= e(url('/app/users')) ?>">
    <?= route_field() ?>
    <?php if (!$isCurator): ?>
        <label class="field">
            <span>Роль</span>
            <select name="role">
                <option value="">Все</option>
                <?php foreach ($roles as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $filterRole === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    <?php endif; ?>
    <label class="field" style="flex:2">
        <span>Поиск</span>
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Имя, логин или e-mail">
    </label>
    <button class="btn btn-primary" type="submit">Показать</button>
    <a class="btn btn-ghost" href="<?= e(url('/app/users')) ?>">Сбросить</a>
    <a class="btn btn-soft" href="<?= e(url('/app/users/create')) ?>">+ Создать аккаунт</a>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
        <tr><th>Участник</th><th>Логин</th><th>Роль</th><th>Статус</th><th>Последний вход</th><th></th></tr>
        </thead>
        <tbody>
        <?php if (!$items): ?>
            <tr><td colspan="6"><div class="table-empty">Аккаунтов по этим условиям нет</div></td></tr>
        <?php endif; ?>
        <?php foreach ($items as $item): ?>
            <tr>
                <td>
                    <div style="display:flex; align-items:center; gap:10px">
                        <span class="avatar" style="width:32px;height:32px;font-size:12px"><?= e(initials($item['first_name'], $item['last_name'])) ?></span>
                        <div>
                            <strong><?= e($item['first_name'] . ' ' . $item['last_name']) ?></strong>
                            <div class="muted small"><?= e($item['email']) ?></div>
                        </div>
                    </div>
                </td>
                <td><code><?= e($item['login']) ?></code></td>
                <td><?= e(role_label($item['role'])) ?></td>
                <td><?= badge($item['status']) ?></td>
                <td class="nowrap small muted"><?= e($item['last_login_at'] ? fmt_dt($item['last_login_at']) : 'ещё не входил') ?></td>
                <td><a class="btn btn-sm btn-ghost" href="<?= e(url('/app/users/' . $item['id'] . '/edit')) ?>">Изменить</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
