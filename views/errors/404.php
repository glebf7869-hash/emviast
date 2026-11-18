<div class="container" style="padding:80px 0 40px">
    <div class="card" style="max-width:560px; margin:0 auto; text-align:center; padding:48px">
        <div class="eyebrow" style="margin-bottom:16px">404</div>
        <h1 style="font-size:34px"><?= e(t('error.404_title')) ?></h1>
        <p class="muted"><?= e(t('error.404_text')) ?></p>
        <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap; margin-top:18px">
            <a class="btn btn-primary" href="<?= e(url('/')) ?>"><?= e(t('error.home')) ?></a>
            <a class="btn btn-ghost" href="<?= e(url('/news')) ?>"><?= e(t('nav.news')) ?></a>
        </div>
    </div>
</div>
