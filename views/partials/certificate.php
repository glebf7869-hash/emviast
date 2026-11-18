<?php
/** @var array $cert */
$state = $cert['state'] ?? 'none';
$stream = $cert['stream'] ?? null;
?>
<div class="card" style="margin-top:20px">
    <div class="card-head" style="margin-bottom:10px">
        <h2 class="card-title">Сертификат</h2>
        <?php if ($state === 'issued'): ?>
            <span class="badge badge-green">получен</span>
        <?php elseif ($state === 'ready'): ?>
            <span class="badge badge-blue">доступен</span>
        <?php else: ?>
            <span class="badge badge-grey">закрыт</span>
        <?php endif; ?>
    </div>

    <?php if ($state === 'none'): ?>
        <p class="muted small" style="margin:0">
            Сертификат появится после участия в потоке — как ученик, преподаватель или куратор.
        </p>
    <?php elseif ($state === 'pending'): ?>
        <p class="muted small" style="margin:0 0 8px">
            Поток «<?= e($stream['title']) ?>» заканчивается <?= e(fmt_date($stream['ends_on'])) ?>.
            После этой даты сертификат можно будет получить.
        </p>
        <button class="btn btn-ghost btn-sm" type="button" disabled>Получить сертификат</button>
    <?php elseif ($state === 'ready'): ?>
        <p class="muted small" style="margin:0 0 10px">
            Поток «<?= e($stream['title']) ?>» завершён. Сгенерируйте сертификат — в нём будут имя,
            роль и краткие итоги участия.
        </p>
        <form method="post" action="<?= e(url('/app/certificate/' . $stream['id'] . '/issue')) ?>">
            <?= csrf_field() ?>
            <button class="btn btn-primary btn-sm" type="submit">Получить сертификат</button>
        </form>
    <?php else: ?>
        <p class="muted small" style="margin:0 0 10px">
            Поток «<?= e($stream['title']) ?>» · выдан <?= e(fmt_date($cert['certificate']['issued_at'])) ?>
            · № <?= e($cert['certificate']['number']) ?>
        </p>
        <a class="btn btn-primary btn-sm" href="<?= e(url('/app/certificate/' . $stream['id'])) ?>">Открыть сертификат</a>
    <?php endif; ?>
</div>
