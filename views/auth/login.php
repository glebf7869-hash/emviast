<section class="section" style="padding-top:56px">
    <div class="container" style="max-width:520px; margin:0 auto">
        <div class="card" style="padding:36px">
            <span class="eyebrow"><?= e(t('login.kicker')) ?></span>
            <h1 style="font-size:30px; margin-bottom:8px"><?= e(t('login.title')) ?></h1>
            <p class="muted" style="margin-bottom:22px"><?= e(t('login.lead')) ?></p>

            <form class="form" method="post" action="<?= e(url('/login')) ?>">
                <?= csrf_field() ?>
                <label class="field">
                    <span><?= e(t('login.login')) ?></span>
                    <input type="text" name="login" autocomplete="username" required autofocus>
                </label>
                <label class="field">
                    <span><?= e(t('login.password')) ?></span>
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>
                <button class="btn btn-primary btn-block" type="submit"><?= e(t('login.btn')) ?></button>
            </form>

            <div class="auth-links">
                <span><?= e(t('login.no_account')) ?> <a href="<?= e(url('/apply')) ?>"><?= e(t('login.leave')) ?></a></span>
                <a href="<?= e(url('/rules')) ?>"><?= e(t('login.rules')) ?></a>
            </div>
        </div>

        <p class="muted small center" style="margin-top:20px"><?= e(t('login.hint')) ?></p>
    </div>
</section>
