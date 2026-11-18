<section class="page-head">
    <div class="container">
        <span class="eyebrow"><?= e(t('news.kicker')) ?></span>
        <h1><?= e(t('news.title')) ?></h1>
        <p><?= e(t('news.lead')) ?></p>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <?php if (!$news): ?>
            <div class="empty"><?= e(t('news.empty')) ?></div>
        <?php else: ?>
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
        <?php endif; ?>
    </div>
</section>
