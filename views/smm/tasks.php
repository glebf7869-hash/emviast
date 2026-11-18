<?php
/** @var array $items */
/** @var array $team */
/** @var bool $isSmm */
/** @var string $today */
?>
<div class="split">
    <div class="card">
        <div class="card-head"><h2 class="card-title">Задачи</h2></div>

        <?php if (!$items): ?>
            <div class="table-empty">Задач пока нет</div>
        <?php endif; ?>

        <?php foreach (['in_progress' => 'В работе', 'todo' => 'К выполнению', 'done' => 'Готово'] as $groupStatus => $groupLabel): ?>
            <?php $groupItems = array_values(array_filter($items, static fn (array $i): bool => $i['status'] === $groupStatus)); ?>
            <?php if ($groupItems): ?>
                <div class="side-label" style="padding-left:0"><?= e($groupLabel) ?></div>
                <?php foreach ($groupItems as $item): ?>
                    <div style="padding:12px 0; border-bottom:1px solid var(--line)">
                        <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start">
                            <div>
                                <strong><?= e($item['title']) ?></strong>
                                <div class="muted small">
                                    <?= e($item['platform'] ?: 'Без площадки') ?>
                                    <?= $item['due_on'] ? ' · до ' . e(fmt_date($item['due_on'])) : '' ?>
                                    <?php if (!$isSmm): ?>
                                        · <?= e(trim(($item['assignee_first'] ?? '') . ' ' . ($item['assignee_last'] ?? '')) ?: 'не назначена') ?>
                                    <?php endif; ?>
                                </div>
                                <?php if ($item['description']): ?>
                                    <div class="muted small" style="margin-top:4px"><?= e($item['description']) ?></div>
                                <?php endif; ?>
                            </div>
                            <?= badge($item['status']) ?>
                        </div>

                        <?php if ($item['status'] !== 'done'): ?>
                            <form method="post" action="<?= e(url('/app/smm/tasks/' . $item['id'])) ?>" style="margin-top:8px; display:flex; gap:8px">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-soft" type="submit" name="status" value="in_progress">В работу</button>
                                <button class="btn btn-sm btn-ghost" type="submit" name="status" value="done">Готово</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <form class="card" method="post" action="<?= e(url('/app/smm/tasks')) ?>">
        <?= csrf_field() ?>
        <h3 style="font-size:16px; margin-bottom:12px">Новая задача</h3>
        <div class="form">
            <label class="field">
                <span>Что сделать</span>
                <input type="text" name="title" placeholder="Снять ролик про…" required>
            </label>
            <label class="field">
                <span>Описание</span>
                <textarea name="description" rows="3"></textarea>
            </label>
            <label class="field">
                <span>Площадка</span>
                <input type="text" name="platform" placeholder="TikTok / Telegram">
            </label>
            <label class="field">
                <span>Срок</span>
                <input type="date" name="due_on" value="<?= e(date('Y-m-d', strtotime('+1 day'))) ?>">
            </label>
            <?php if (!$isSmm): ?>
                <label class="field">
                    <span>Исполнитель</span>
                    <select name="assignee_id">
                        <option value="0">Не назначен</option>
                        <?php foreach ($team as $member): ?>
                            <option value="<?= (int)$member['id'] ?>"><?= e($member['first_name'] . ' ' . $member['last_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endif; ?>
            <button class="btn btn-primary" type="submit">Добавить задачу</button>
        </div>
    </form>
</div>
