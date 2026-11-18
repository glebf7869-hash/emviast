<article class="article">
    <a class="link small" href="<?= e(url('/news')) ?>"><?= e(t('news.back')) ?></a>
    <h1 style="margin-top:16px"><?= e(news_title($item)) ?></h1>
    <div class="article-meta">
        <?= e(fmt_date($item['published_at'])) ?>
        <?php if (!empty($item['first_name'])): ?>
            · <?= e($item['first_name'] . ' ' . $item['last_name']) ?>
        <?php endif; ?>
    </div>

    <?php if (!empty($item['cover'])): ?>
        <img class="article-cover" src="<?= e(file_url($item['cover'])) ?>" alt="">
    <?php endif; ?>

    <?php if (news_excerpt($item) !== ''): ?>
        <p class="muted" style="font-size:17px"><?= e(news_excerpt($item)) ?></p>
    <?php endif; ?>

    <div class="article-body">
        <?php foreach (preg_split('/\R{2,}/', news_body($item)) as $paragraph): ?>
            <p><?= nl2br(e(trim($paragraph))) ?></p>
        <?php endforeach; ?>
    </div>

    <div class="divider"></div>
    <div style="display:flex; gap:12px; flex-wrap:wrap">
        <a class="btn btn-primary" href="<?= e(url('/apply')) ?>"><?= e(t('news.apply')) ?></a>
        <a class="btn btn-ghost" href="<?= e(url('/news')) ?>"><?= e(t('news.all')) ?></a>
    </div>
</article>
