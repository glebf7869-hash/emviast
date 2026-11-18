<?php
/** @var array $rows */
/** @var array $summary */
?>
<div class="grid grid-auto" style="margin-bottom:20px">
    <?php if (!$summary): ?>
        <div class="empty" style="grid-column:1/-1">Показателей пока нет — добавьте первую запись.</div>
    <?php endif; ?>
    <?php foreach ($summary as $row): ?>
        <div class="card">
            <div class="muted small"><?= e($row['platform']) ?> · <?= e(fmt_date($row['stat_date'])) ?></div>
            <div class="stat-value" style="margin-top:6px"><?= number_format((int)$row['followers'], 0, ',', ' ') ?></div>
            <div class="muted small">подписчиков</div>
            <div class="divider" style="margin:12px 0"></div>
            <div class="small muted">
                Охват <?= number_format((int)$row['reach'], 0, ',', ' ') ?> ·
                Лайки <?= number_format((int)$row['likes'], 0, ',', ' ') ?> ·
                Комментарии <?= number_format((int)$row['comments'], 0, ',', ' ') ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="split">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Дата</th><th>Площадка</th><th>Подписчики</th><th>Охват</th><th>Лайки</th><th>Комм.</th></tr></thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="6"><div class="table-empty">Данных пока нет</div></td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td class="nowrap"><?= e(fmt_date($row['stat_date'])) ?></td>
                    <td><strong><?= e($row['platform']) ?></strong></td>
                    <td><?= (int)$row['followers'] ?></td>
                    <td><?= (int)$row['reach'] ?></td>
                    <td><?= (int)$row['likes'] ?></td>
                    <td><?= (int)$row['comments'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <form class="card" method="post" action="<?= e(url('/app/smm/stats')) ?>">
        <?= csrf_field() ?>
        <h3 style="font-size:16px; margin-bottom:12px">Добавить показатели</h3>
        <div class="form">
            <label class="field">
                <span>Площадка</span>
                <input type="text" name="platform" value="TikTok" required>
            </label>
            <label class="field">
                <span>Дата</span>
                <input type="date" name="stat_date" value="<?= e(date('Y-m-d')) ?>" required>
            </label>
            <div class="form-row">
                <label class="field">
                    <span>Подписчики</span>
                    <input type="number" name="followers" min="0" value="0">
                </label>
                <label class="field">
                    <span>Охват</span>
                    <input type="number" name="reach" min="0" value="0">
                </label>
            </div>
            <div class="form-row">
                <label class="field">
                    <span>Лайки</span>
                    <input type="number" name="likes" min="0" value="0">
                </label>
                <label class="field">
                    <span>Комментарии</span>
                    <input type="number" name="comments" min="0" value="0">
                </label>
            </div>
            <label class="field">
                <span>Заметка</span>
                <input type="text" name="notes" placeholder="Что вышло, что заметили">
            </label>
            <button class="btn btn-primary" type="submit">Сохранить</button>
        </div>
    </form>
</div>
