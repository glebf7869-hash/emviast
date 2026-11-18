<section class="page-head">
    <div class="container">
        <span class="eyebrow"><?= e(t('about.kicker')) ?></span>
        <h1><?= e(t('about.title')) ?></h1>
        <p><?= e(t('about.lead')) ?></p>
    </div>
</section>

<section class="section-sm">
    <div class="container grid grid-2" style="gap:40px; align-items:start">
        <div class="prose">
            <h2><?= e(t('about.what_title')) ?></h2>
            <p><?= e(t('about.what_p1')) ?></p>
            <p><?= e(t('about.what_p2')) ?></p>

            <h2><?= e(t('about.principles_title')) ?></h2>
            <ul>
                <li><?= e(t('about.p1')) ?></li>
                <li><?= e(t('about.p2')) ?></li>
                <li><?= e(t('about.p3')) ?></li>
                <li><?= e(t('about.p4')) ?></li>
                <li><?= e(t('about.p5')) ?></li>
            </ul>
        </div>

        <div class="card card-soft" style="padding:32px">
            <h3><?= e(t('about.pilot_title')) ?></h3>
            <div class="kv" style="margin-top:16px">
                <dt><?= e(t('about.lang')) ?></dt><dd><?= e(t('about.lang_value')) ?></dd>
                <dt><?= e(t('about.level')) ?></dt><dd>A1–A2</dd>
                <dt><?= e(t('about.age')) ?></dt><dd>14–17</dd>
                <dt><?= e(t('about.flow')) ?></dt><dd><?= e(t('about.flow_value')) ?></dd>
                <dt><?= e(t('about.group')) ?></dt><dd><?= e(t('about.group_value')) ?></dd>
                <dt><?= e(t('about.price')) ?></dt><dd><?= e(t('about.price_value')) ?></dd>
            </div>
            <div class="divider"></div>
            <p class="muted small" style="margin-bottom:16px"><?= e(t('about.note')) ?></p>
            <a class="btn btn-primary btn-block" href="<?= e(url('/apply')) ?>"><?= e(t('nav.apply')) ?></a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow"><?= e(t('about.team_kicker')) ?></span>
            <h2><?= e(t('about.team_title')) ?></h2>
        </div>
        <div class="grid grid-4">
            <div class="card card-hover">
                <h3 style="font-size:17px"><?= e(t('about.team_teachers')) ?></h3>
                <p class="muted small"><?= e(t('about.team_teacher')) ?></p>
            </div>
            <div class="card card-hover">
                <h3 style="font-size:17px"><?= e(t('about.team_curators')) ?></h3>
                <p class="muted small"><?= e(t('about.team_curator')) ?></p>
            </div>
            <div class="card card-hover">
                <h3 style="font-size:17px"><?= e(t('about.team_admins')) ?></h3>
                <p class="muted small"><?= e(t('about.team_admin')) ?></p>
            </div>
            <div class="card card-hover">
                <h3 style="font-size:17px"><?= e(t('about.team_smm_title')) ?></h3>
                <p class="muted small"><?= e(t('about.team_smm')) ?></p>
            </div>
        </div>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="cta-band">
            <div>
                <img class="cta-logo" src="<?= e(asset('img/logo-white.svg')) ?>" alt="EMVIA Study">
                <h2><?= e(t('about.cta_title')) ?></h2>
                <p><?= e(t('about.cta_text')) ?></p>
            </div>
            <a class="btn btn-ghost" href="<?= e(url('/apply?type=teacher')) ?>"><?= e(t('about.cta_btn')) ?></a>
        </div>
    </div>
</section>
