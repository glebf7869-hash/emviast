<?php
/** @var array $items */
/** @var array $counts */
/** @var array $filters */
$typeLabels = ['student' => 'Ученик', 'teacher' => 'Преподаватель', 'curator' => 'Куратор', 'smm' => 'SMM'];
?>
<div class="grid grid-4" style="margin-bottom:20px">
    <div class="card">
        <div class="muted small">Новые</div>
        <div class="stat-value" style="margin-top:6px"><?= (int)($counts['new'] ?? 0) ?></div>
    </div>
    <div class="card">
        <div class="muted small">На рассмотрении</div>
        <div class="stat-value" style="margin-top:6px"><?= (int)($counts['in_review'] ?? 0) ?></div>
    </div>
    <div class="card">
        <div class="muted small">Одобрены</div>
        <div class="stat-value" style="margin-top:6px"><?= (int)($counts['approved'] ?? 0) ?></div>
    </div>
    <div class="card">
        <div class="muted small">Лист ожидания</div>
        <div class="stat-value" style="margin-top:6px"><?= (int)($counts['waitlist'] ?? 0) ?></div>
    </div>
</div>

<form class="filters" method="get" action="<?= e(url('/app/applications')) ?>">
    <?= route_field() ?>
    <label class="field">
        <span>Тип</span>
        <select name="type">
            <option value="">Все</option>
            <?php foreach ($typeLabels as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $filters['type'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="field">
        <span>Статус</span>
        <select name="status">
            <option value="">Любой</option>
            <?php foreach (['new' => 'Новая', 'in_review' => 'На рассмотрении', 'approved' => 'Одобрена', 'waitlist' => 'Лист ожидания', 'rejected' => 'Отклонена'] as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="field" style="flex:2">
        <span>Поиск</span>
        <input type="text" name="q" value="<?= e($filters['q']) ?>" placeholder="Имя или e-mail">
    </label>
    <button class="btn btn-primary" type="submit">Показать</button>
    <a class="btn btn-ghost" href="<?= e(url('/app/applications')) ?>">Сбросить</a>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
        <tr>
            <th>Кто</th>
            <th>Тип</th>
            <th>Контакты</th>
            <th>Дата</th>
            <th>Статус</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$items): ?>
            <tr><td colspan="6"><div class="table-empty">Заявок по этим фильтрам нет</div></td></tr>
        <?php endif; ?>
        <?php foreach ($items as $item): ?>
            <tr>
                <td>
                    <strong><?= e($item['first_name'] . ' ' . $item['last_name']) ?></strong>
                    <?php if ($item['age']): ?><div class="muted small"><?= (int)$item['age'] ?> лет</div><?php endif; ?>
                    <?php if (!empty($item['schedule_days'])): ?>
                        <div class="muted small">
                            <?= e(format_schedule_days($item['schedule_days'])) ?>
                            <?= $item['schedule_time'] ? ' · ' . e(format_time_slots($item['schedule_time'])) : '' ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td><?= e($typeLabels[$item['type']] ?? $item['type']) ?></td>
                <td>
                    <div><?= e($item['email']) ?></div>
                    <?php if ($item['phone']): ?><div class="muted small"><?= e($item['phone']) ?></div><?php endif; ?>
                </td>
                <td class="nowrap"><?= e(fmt_date($item['created_at'])) ?></td>
                <td><?= badge($item['status']) ?></td>
                <td><a class="btn btn-sm btn-ghost" href="<?= e(url('/app/applications/' . $item['id'])) ?>">Открыть</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
