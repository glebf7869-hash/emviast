<?php
/** @var array $items */
/** @var array $streams */
/** @var bool $canCreate */
/** @var array $filters */
$role = user_role();
?>
<?php if ($canCreate): ?>
    <div class="topbar-actions" style="margin-bottom:18px">
        <a class="btn btn-primary" href="<?= e(url('/app/groups/create')) ?>">+ Новая группа</a>
    </div>
<?php endif; ?>

<form class="filters" method="get" action="<?= e(url('/app/groups')) ?>">
    <?= route_field() ?>
    <label class="field">
        <span>Поток</span>
        <select name="stream_id">
            <option value="0">Все потоки</option>
            <?php foreach ($streams as $stream): ?>
                <option value="<?= (int)$stream['id'] ?>" <?= (int)$filters['stream_id'] === (int)$stream['id'] ? 'selected' : '' ?>>
                    <?= e($stream['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="field">
        <span>Статус</span>
        <select name="status">
            <option value="">Любой</option>
            <?php foreach (['forming' => 'Формируется', 'active' => 'Активная', 'finished' => 'Завершена'] as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button class="btn btn-primary" type="submit">Показать</button>
    <a class="btn btn-ghost" href="<?= e(url('/app/groups')) ?>">Сбросить</a>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
        <tr>
            <th>Группа</th>
            <th>Поток</th>
            <th>Преподаватель</th>
            <th>Куратор</th>
            <th>Ученики</th>
            <th>Расписание</th>
            <th>Статус</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$items): ?>
            <tr><td colspan="8"><div class="table-empty">Групп пока нет<?= $canCreate ? ' — создайте первую' : '' ?>.</div></td></tr>
        <?php endif; ?>
        <?php foreach ($items as $item): ?>
            <tr>
                <td>
                    <a href="<?= e(url('/app/groups/' . $item['id'])) ?>"><strong><?= e($item['name']) ?></strong></a>
                </td>
                <td>
                    <a href="<?= e(url('/app/streams/' . $item['stream_id'])) ?>"><?= e($item['stream_title']) ?></a>
                </td>
                <td class="small"><?= e(trim(($item['teacher_first'] ?? '') . ' ' . ($item['teacher_last'] ?? '')) ?: 'не назначен') ?></td>
                <td class="small"><?= e(trim(($item['curator_first'] ?? '') . ' ' . ($item['curator_last'] ?? '')) ?: '—') ?></td>
                <td><?= (int)$item['students_total'] ?> / <?= (int)$item['capacity'] ?></td>
                <td class="small muted"><?= e($item['schedule_note'] ?: '—') ?></td>
                <td><?= badge($item['status']) ?></td>
                <td><a class="btn btn-sm btn-ghost" href="<?= e(url('/app/groups/' . $item['id'])) ?>">Открыть</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
