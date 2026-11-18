<?php
/** @var string $content */
/** @var string $title */
$path = current_path();
$navItems = [
    ['/', 'nav.home'],
    ['/about', 'nav.about'],
    ['/news', 'nav.news'],
    ['/rules', 'nav.rules'],
];
$user = current_user();
$lang = current_lang();
?>
<!doctype html>
<html lang="<?= e($lang) ?>" data-base="<?= e(base_path()) ?>" data-style="<?= e(url_style()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e(t('home.lead')) ?>">
    <title><?= e($title ?? 'EMVIA Study') ?></title>
    <link rel="preload" href="<?= e(asset('fonts/manrope-cyrillic.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= e(asset('fonts/manrope-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="icon" href="<?= e(asset('img/mark.svg')) ?>" type="image/svg+xml">
</head>
<body>
<header class="site-header">
    <div class="container site-header-inner">
        <a class="brand brand-link" href="<?= e(url('/')) ?>" aria-label="EMVIA Study">
            <img class="logo-full" src="<?= e(asset('img/logo.svg')) ?>" alt="EMVIA Study">
            <img class="logo-mark" src="<?= e(asset('img/mark.svg')) ?>" alt="EMVIA Study">
        </a>

        <nav class="site-nav">
            <?php foreach ($navItems as [$href, $key]): ?>
                <a href="<?= e(url($href)) ?>" class="<?= $path === $href || ($href !== '/' && str_starts_with($path, $href)) ? 'is-active' : '' ?>">
                    <?= e(t($key)) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="site-actions">
            <div class="lang-switch" aria-label="<?= e(t('lang.label')) ?>">
                <a href="<?= e(url('/lang/ru')) ?>" class="<?= $lang === 'ru' ? 'is-active' : '' ?>">RU</a>
                <a href="<?= e(url('/lang/en')) ?>" class="<?= $lang === 'en' ? 'is-active' : '' ?>">EN</a>
            </div>
            <?php if ($user): ?>
                <a class="btn btn-ghost btn-sm" href="<?= e(url('/app')) ?>"><?= e(t('nav.cabinet')) ?></a>
            <?php else: ?>
                <a class="btn btn-ghost btn-sm" href="<?= e(url('/login')) ?>"><?= e(t('nav.login')) ?></a>
                <a class="btn btn-primary btn-sm" href="<?= e(url('/apply')) ?>"><?= e(t('nav.apply')) ?></a>
            <?php endif; ?>
            <button class="nav-toggle" type="button" aria-label="Menu">☰</button>
        </div>
    </div>
</header>

<main>
    <div class="container" style="padding-top:14px">
        <?php foreach (Flash::pull() as $message): ?>
            <div class="alert alert-<?= e($message['type']) ?>"><?= e($message['message']) ?></div>
        <?php endforeach; ?>
    </div>
    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <a class="brand brand-link" href="<?= e(url('/')) ?>" style="margin-bottom:10px">
                <img class="logo-full" src="<?= e(asset('img/logo.svg')) ?>" alt="EMVIA Study" style="height:24px">
            </a>
            <div><?= e(t('footer.tagline')) ?></div>
        </div>
        <div class="footer-links">
            <a href="<?= e(url('/about')) ?>"><?= e(t('nav.about')) ?></a>
            <a href="<?= e(url('/rules')) ?>"><?= e(t('nav.rules')) ?></a>
            <a href="<?= e(url('/news')) ?>"><?= e(t('nav.news')) ?></a>
            <a href="<?= e(url('/apply')) ?>"><?= e(t('nav.apply')) ?></a>
            <a href="<?= e(url('/login')) ?>"><?= e(t('nav.login')) ?></a>
        </div>
    </div>
</footer>

<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
