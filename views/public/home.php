<?php
/** @var array $news */
/** @var array $stats */
?>
<section class="hero">
    <div class="container hero-grid">
        <div>
            <span class="eyebrow"><?= e(t('home.kicker')) ?></span>
            <h1 class="hero-title"><?= e(t('home.title')) ?> <span class="accent"><?= e(t('home.title_accent')) ?></span></h1>
            <p class="hero-text"><?= e(t('home.lead')) ?></p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="<?= e(url('/apply')) ?>"><?= e(t('home.btn_apply')) ?></a>
                <a class="btn btn-ghost" href="<?= e(url('/about')) ?>"><?= e(t('home.btn_about')) ?></a>
            </div>
            <div class="hero-note">
                <span><?= e(t('home.note_free')) ?></span>
                <span>·</span>
                <span><?= e(t('home.note_groups')) ?></span>
                <span>·</span>
                <span><?= e(t('home.note_weeks')) ?></span>
            </div>
        </div>

        <div class="hero-visual">
            <div class="hero-blob"></div>
            <div class="hero-card">
                <span class="hero-card-tag"><?= e(t('home.card_lesson_tag')) ?></span>
                <h3><?= e(t('home.card_lesson_title')) ?></h3>
                <p><?= e(t('home.card_lesson_text')) ?></p>
            </div>
            <div class="hero-card">
                <span class="hero-card-tag"><?= e(t('home.card_progress_tag')) ?></span>
                <h3><?= e(t('home.card_progress_title')) ?></h3>
                <p><?= e(t('home.card_progress_text')) ?></p>
            </div>
        </div>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="stat-band">
            <div class="stat">
                <div class="stat-value"><?= (int)$stats['students'] ?></div>
                <div class="stat-label"><?= e(t('stats.students')) ?></div>
            </div>
            <div class="stat">
                <div class="stat-value"><?= (int)$stats['teachers'] ?></div>
                <div class="stat-label"><?= e(t('stats.teachers')) ?></div>
            </div>
            <div class="stat">
                <div class="stat-value"><?= (int)$stats['groups'] ?></div>
                <div class="stat-label"><?= e(t('stats.groups')) ?></div>
            </div>
            <div class="stat">
                <div class="stat-value"><?= (int)$stats['streams'] ?></div>
                <div class="stat-label"><?= e(t('stats.streams')) ?></div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow"><?= e(t('how.kicker')) ?></span>
            <h2><?= e(t('how.title')) ?></h2>
            <p class="muted"><?= e(t('how.lead')) ?></p>
        </div>

        <div class="grid grid-3">
            <div class="card card-hover feature">
                <div class="feature-icon">✎</div>
                <h3><?= e(t('how.1.title')) ?></h3>
                <p><?= e(t('how.1.text')) ?></p>
            </div>
            <div class="card card-hover feature">
                <div class="feature-icon">◎</div>
                <h3><?= e(t('how.2.title')) ?></h3>
                <p><?= e(t('how.2.text')) ?></p>
            </div>
            <div class="card card-hover feature">
                <div class="feature-icon">✦</div>
                <h3><?= e(t('how.3.title')) ?></h3>
                <p><?= e(t('how.3.text')) ?></p>
            </div>
        </div>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="grid grid-2" style="align-items:center; gap:40px">
            <div>
                <span class="eyebrow"><?= e(t('format.kicker')) ?></span>
                <h2 style="margin-bottom:14px"><?= e(t('format.title')) ?></h2>
                <p class="muted"><?= e(t('format.lead')) ?></p>
                <div class="grid grid-2" style="gap:12px; margin-top:22px">
                    <div class="chip"><?= e(t('format.chip_level')) ?></div>
                    <div class="chip"><?= e(t('format.chip_age')) ?></div>
                    <div class="chip"><?= e(t('format.chip_freq')) ?></div>
                    <div class="chip"><?= e(t('format.chip_size')) ?></div>
                </div>
            </div>
            <div class="card card-soft" style="padding:32px">
                <h3 style="margin-bottom:16px"><?= e(t('format.inside_title')) ?></h3>
                <div class="kv" style="grid-template-columns:1fr">
                    <div><strong><?= e(t('format.step1_title')) ?></strong><div class="muted small"><?= e(t('format.step1_text')) ?></div></div>
                    <div class="divider" style="margin:14px 0"></div>
                    <div><strong><?= e(t('format.step2_title')) ?></strong><div class="muted small"><?= e(t('format.step2_text')) ?></div></div>
                    <div class="divider" style="margin:14px 0"></div>
                    <div><strong><?= e(t('format.step3_title')) ?></strong><div class="muted small"><?= e(t('format.step3_text')) ?></div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($news): ?>
<section class="section">
    <div class="container">
        <div class="section-head" style="display:flex; justify-content:space-between; align-items:flex-end; max-width:none; gap:20px; flex-wrap:wrap">
            <div>
                <span class="eyebrow"><?= e(t('news.kicker')) ?></span>
                <h2 style="margin-bottom:0"><?= e(t('news.title')) ?></h2>
            </div>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/news')) ?>"><?= e(t('news.all')) ?></a>
        </div>

        <div class="grid grid-3">
            <?php foreach ($news as $item): ?>
                <a class="card card-hover news-card" href="<?= e(url('/news/' . $item['slug'])) ?>">
                    <?php if (!empty($item['cover'])): ?>
                        <img class="news-cover" src="<?= e(file_url($item['cover'])) ?>" alt="">
                    <?php endif; ?>
                    <time><?= e(fmt_date($item['published_at'])) ?></time>
                    <h3><?= e(news_title($item)) ?></h3>
                    <p><?= e(news_excerpt($item)) ?></p>
                    <span class="link"><?= e(t('news.read')) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section-sm">
    <div class="container">
        <div class="cta-band">
            <div>
                <img class="cta-logo" src="<?= e(asset('img/logo-white.svg')) ?>" alt="EMVIA Study">
                <h2><?= e(t('cta.title')) ?></h2>
                <p><?= e(t('cta.text')) ?></p>
            </div>
            <div style="display:flex; gap:12px; flex-wrap:wrap">
                <a class="btn btn-ghost" href="<?= e(url('/apply?type=student')) ?>"><?= e(t('cta.student')) ?></a>
                <a class="btn btn-ghost" href="<?= e(url('/apply?type=teacher')) ?>"><?= e(t('cta.teacher')) ?></a>
            </div>
        </div>
    </div>
</section>
