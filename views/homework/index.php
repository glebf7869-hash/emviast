<?php
/** @var array $items */
/** @var bool $canCreate */
$role = user_role();
?>
<?php if ($canCreate): ?>
    <div class="topbar-actions" style="margin-bottom:18px">
        <a class="btn btn-primary" href="<?= e(url('/app/homework/create')) ?>">+ Новое задание</a>
    </div>
<?php endif; ?>

<div class="table-wrap">
    <table class="table">
        <thead>
        <tr>
            <th>Задание</th>
            <th>Группа</th>
            <th>Дедлайн</th>
            <th>Сдали</th>
            <th>Статус</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$items): ?>
            <tr><td colspan="6"><div class="table-empty">Заданий пока нет</div></td></tr>
        <?php endif; ?>

        <?php foreach ($items as $item): ?>
            <?php
            $status = $item['my_status'] ?: 'assigned';
            ?>
            <tr>
                <td>
                    <strong><?= e($item['title']) ?></strong>
                    <div class="muted small"><?= e(mb_substr(strip_tags((string)$item['description']), 0, 90)) ?></div>
                </td>
                <td>
                    <div><?= e($item['group_name']) ?></div>
                    <div class="muted small"><?= e($item['stream_title']) ?></div>
                </td>
                <td class="nowrap small"><?= e($item['due_at'] ? fmt_date($item['due_at'], 'd.m, H:i') : '—') ?></td>
                <td class="small"><?= (int)$item['submitted_total'] ?> / <?= (int)$item['students_total'] ?></td>
                <td><?= badge($status) ?></td>
                <td><a class="btn btn-sm btn-ghost" href="<?= e(url('/app/homework/' . $item['id'])) ?>">
                        <?= $role === 'student' ? 'Открыть' : 'Проверить' ?>
                    </a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
