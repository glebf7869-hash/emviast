<?php
/** @var array $user */
/** @var array $stream */
/** @var array $certificate */
/** @var string $role */
/** @var string $roleLabel */
/** @var array $activity */
/** @var string $issuedAt */
?>
<div class="cert-toolbar">
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/app')) ?>">← В кабинет</a>
    <button class="btn btn-primary btn-sm" type="button" onclick="window.print()">Скачать / распечатать</button>
</div>

<div class="cert-sheet">
    <div class="cert-frame">
        <div class="cert-top">
            <img src="<?= e(asset('img/logo.svg')) ?>" alt="EMVIA Study" style="height:30px">
            <div class="cert-number">
                № <?= e($certificate['number']) ?><br>
                <?= e($issuedAt) ?>
            </div>
        </div>

        <div class="cert-title">Сертификат</div>
        <div class="cert-sub">подтверждает участие в волонтёрской школе английского EMVIA Study</div>

        <div class="cert-name"><?= e($user['first_name'] . ' ' . $user['last_name']) ?></div>

        <p class="cert-text">
            Участвовал(-а) в потоке «<?= e($stream['title']) ?>»
            (<?= e($stream['language']) ?>, <?= e($stream['level']) ?>)
            в качестве <?= e($roleLabel) ?>.
            Период потока: <?= e(fmt_date($stream['starts_on'])) ?> — <?= e(fmt_date($stream['ends_on'])) ?>.
        </p>

        <div class="cert-stats">
            <?php foreach ($activity as $item): ?>
                <div class="cert-stat">
                    <b><?= e($item['value']) ?></b>
                    <span><?= e($item['label']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="cert-text" style="margin-top:22px">
            Продолжайте говорить, ошибаться и пробовать снова — язык любит тех, кто не бросает.
            Спасибо, что были частью EMVIA Study.
        </p>

        <div class="cert-bottom">
            <div class="cert-sign">
                Руководитель EMVIA Study<br>
                <span class="muted small">подпись и дата</span>
            </div>
            <div class="cert-number">
                Проверить подлинность: <?= e($certificate['number']) ?>
            </div>
        </div>
    </div>
</div>
