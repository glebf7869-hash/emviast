<?php
/** @var array $items */
$role = user_role();
$canReport = in_array($role, ['root', 'admin', 'teacher'], true);
?>
<?php if (!empty($groups)): ?>
    <form class="filters" method="get" action="<?= e(url('/app/schedule')) ?>">
        <?= route_field() ?>
        <label class="field" style="flex:2">
            <span>Группа</span>
            <select name="group_id">
                <option value="0">Все группы</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= (int)$group['id'] ?>" <?= (int)($filters['group_id'] ?? 0) === (int)$group['id'] ? 'selected' : '' ?>>
                        <?= e($group['stream_title'] . ' · ' . $group['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="btn btn-primary" type="submit">Показать</button>
        <a class="btn btn-ghost" href="<?= e(url('/app/schedule')) ?>">Сбросить</a>
    </form>
<?php endif; ?>

<div class="table-wrap">
    <table class="table">
        <thead>
        <tr>
            <th>Когда</th>
            <th>Группа</th>
            <th>Тема</th>
            <th>Преподаватель</th>
            <th>Занятие</th>
            <th>Отчёт</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$items): ?>
            <tr><td colspan="7"><div class="table-empty">Занятий пока нет</div></td></tr>
        <?php endif; ?>

        <?php foreach ($items as $item): ?>
            <tr>
                <td class="nowrap">
                    <strong><?= e(fmt_date($item['scheduled_at'], 'd.m')) ?></strong>
                    <div class="muted small"><?= e(fmt_date($item['scheduled_at'], 'H:i')) ?> · <?= (int)$item['duration_min'] ?> мин</div>
                </td>
                <td>
                    <div><?= e($item['group_name']) ?></div>
                    <div class="muted small"><?= e($item['stream_title']) ?></div>
                </td>
                <td><?= e($item['topic']) ?></td>
                <td class="small"><?= e(trim(($item['teacher_first'] ?? '') . ' ' . ($item['teacher_last'] ?? '')) ?: '—') ?></td>
                <td><?= badge($item['status']) ?></td>
                <td>
                    <?php if ($item['report_id']): ?>
                        <?= badge($item['report_status']) ?>
                    <?php else: ?>
                        <span class="muted small">—</span>
                    <?php endif; ?>
                </td>
                <td><a class="btn btn-sm btn-ghost" href="<?= e(url('/app/lessons/' . $item['id'])) ?>">
                        <?= $canReport ? 'Открыть' : 'Смотреть' ?>
                    </a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
