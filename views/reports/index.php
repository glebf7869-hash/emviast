<?php
/** @var array $items */
/** @var bool $canReview */
/** @var array $groups */
/** @var array $filters */
$filterStatus = (string)($filters['status'] ?? '');
$filterGroup = (int)($filters['group_id'] ?? 0);
?>
<form class="filters" method="get" action="<?= e(url('/app/reports')) ?>">
    <?= route_field() ?>
    <?php if (!empty($groups)): ?>
        <label class="field" style="flex:2">
            <span>Группа</span>
            <select name="group_id">
                <option value="0">Все группы</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= (int)$group['id'] ?>" <?= $filterGroup === (int)$group['id'] ? 'selected' : '' ?>>
                        <?= e($group['stream_title'] . ' · ' . $group['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    <?php endif; ?>
    <label class="field">
        <span>Статус</span>
        <select name="status">
            <option value="">Любой</option>
            <?php foreach (['draft' => 'Черновик', 'submitted' => 'Отправлен', 'approved' => 'Принят', 'returned' => 'Возвращён'] as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $filterStatus === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button class="btn btn-primary" type="submit">Показать</button>
    <a class="btn btn-ghost" href="<?= e(url('/app/reports')) ?>">Сбросить</a>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
        <tr><th>Урок</th><th>Группа</th><th>Преподаватель</th><th>Посещение</th><th>Статус</th><th></th></tr>
        </thead>
        <tbody>
        <?php if (!$items): ?>
            <tr><td colspan="6"><div class="table-empty">Отчётов пока нет</div></td></tr>
        <?php endif; ?>
        <?php foreach ($items as $item): ?>
            <tr>
                <td>
                    <strong>№<?= (int)$item['lesson_no'] ?> · <?= e($item['topic']) ?></strong>
                    <div class="muted small"><?= e(fmt_date($item['scheduled_at'], 'd.m.Y, H:i')) ?></div>
                </td>
                <td>
                    <div><?= e($item['group_name']) ?></div>
                    <div class="muted small"><?= e($item['stream_title']) ?></div>
                </td>
                <td class="small"><?= e(trim(($item['teacher_first'] ?? '') . ' ' . ($item['teacher_last'] ?? '')) ?: '—') ?></td>
                <td class="small"><?= (int)$item['attendance_present'] ?> / <?= (int)$item['attendance_present'] + (int)$item['attendance_absent'] ?></td>
                <td><?= badge($item['status']) ?></td>
                <td><a class="btn btn-sm btn-ghost" href="<?= e(url('/app/reports/' . $item['id'])) ?>">Открыть</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
