<section class="page-head">
    <div class="container">
        <span class="eyebrow"><?= e(t('rules.kicker')) ?></span>
        <h1><?= e(t('rules.title')) ?></h1>
        <p><?= e(t('rules.lead')) ?></p>
    </div>
</section>

<section class="section-sm">
    <div class="container grid grid-2" style="gap:22px; align-items:start">
        <div class="card">
            <h3><?= e(t('rules.c1_title')) ?></h3>
            <ul class="prose" style="padding-left:20px; color:var(--ink-soft)">
                <li><?= e(t('rules.c1_1')) ?></li>
                <li><?= e(t('rules.c1_2')) ?></li>
                <li><?= e(t('rules.c1_3')) ?></li>
                <li><?= e(t('rules.c1_4')) ?></li>
            </ul>
        </div>

        <div class="card">
            <h3><?= e(t('rules.c2_title')) ?></h3>
            <ul class="prose" style="padding-left:20px; color:var(--ink-soft)">
                <li><?= e(t('rules.c2_1')) ?></li>
                <li><?= e(t('rules.c2_2')) ?></li>
                <li><?= e(t('rules.c2_3')) ?></li>
            </ul>
        </div>

        <div class="card">
            <h3><?= e(t('rules.c3_title')) ?></h3>
            <ul class="prose" style="padding-left:20px; color:var(--ink-soft)">
                <li><?= e(t('rules.c3_1')) ?></li>
                <li><?= e(t('rules.c3_2')) ?></li>
                <li><?= e(t('rules.c3_3')) ?></li>
                <li><?= e(t('rules.c3_4')) ?></li>
            </ul>
        </div>

        <div class="card">
            <h3><?= e(t('rules.c4_title')) ?></h3>
            <ul class="prose" style="padding-left:20px; color:var(--ink-soft)">
                <li><?= e(t('rules.c4_1')) ?></li>
                <li><?= e(t('rules.c4_2')) ?></li>
                <li><?= e(t('rules.c4_3')) ?></li>
                <li><?= e(t('rules.c4_4')) ?></li>
            </ul>
        </div>
    </div>
</section>

<?php if (!empty($documents)): ?>
<section class="section-sm">
    <div class="container">
        <h2 style="margin-bottom:16px"><?= e(t('rules.docs_title')) ?></h2>
        <div class="grid grid-2">
            <?php foreach ($documents as $document): ?>
                <?php $href = !empty($document['file']) ? file_url($document['file']) : (string)$document['url']; ?>
                <div class="card" style="display:flex; justify-content:space-between; gap:16px; align-items:center">
                    <div>
                        <strong><?= e($document['title']) ?></strong>
                        <?php if ($document['description']): ?>
                            <div class="muted small"><?= e($document['description']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php if ($href !== ''): ?>
                        <a class="btn btn-sm btn-primary" href="<?= e($href) ?>" target="_blank" rel="noopener"><?= e(t('rules.docs_open')) ?></a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section-sm">
    <div class="container">
        <div class="card card-soft" style="display:flex; gap:24px; align-items:center; justify-content:space-between; flex-wrap:wrap; padding:32px">
            <div>
                <h3 style="margin-bottom:6px"><?= e(t('rules.cta_title')) ?></h3>
                <p class="muted" style="margin:0"><?= e(t('rules.cta_text')) ?></p>
            </div>
            <a class="btn btn-primary" href="<?= e(url('/apply')) ?>"><?= e(t('rules.cta_btn')) ?></a>
        </div>
    </div>
</section>
