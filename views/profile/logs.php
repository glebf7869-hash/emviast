<?php
/** @var array $items */
?>
<div class="table-wrap">
    <table class="table">
        <thead><tr><th>Когда</th><th>Кто</th><th>Действие</th><th>Объект</th><th>Детали</th><th>IP</th></tr></thead>
        <tbody>
        <?php if (!$items): ?>
            <tr><td colspan="6"><div class="table-empty">Журнал пуст</div></td></tr>
        <?php endif; ?>
        <?php foreach ($items as $item): ?>
            <tr>
                <td class="nowrap small"><?= e(fmt_dt($item['created_at'])) ?></td>
                <td class="small">
                    <?= e(trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? '')) ?: 'система') ?>
                    <?php if (!empty($item['role'])): ?>
                        <div class="muted small"><?= e(role_label($item['role'])) ?></div>
                    <?php endif; ?>
                </td>
                <td><code><?= e($item['action']) ?></code></td>
                <td class="small"><?= e($item['entity'] ?: '—') ?><?= $item['entity_id'] ? ' #' . (int)$item['entity_id'] : '' ?></td>
                <td class="small muted"><?= e($item['meta'] ?: '—') ?></td>
                <td class="small muted"><?= e($item['ip'] ?: '—') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
