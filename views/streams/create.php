<?php
/** @var array $curators */
?>
<form class="card" method="post" action="<?= e(url('/app/streams')) ?>" style="max-width:760px">
    <?= csrf_field() ?>
    <h2 class="card-title" style="margin-bottom:16px">Новый поток</h2>

    <div class="form">
        <label class="field">
            <span>Название</span>
            <input type="text" name="title" value="<?= e(old('title')) ?>" placeholder="Английский A1–A2 · Пилот 2" required>
        </label>

        <div class="form-row">
            <label class="field">
                <span>Язык</span>
                <input type="text" name="language" value="<?= e(old('language', 'Английский')) ?>">
            </label>
            <label class="field">
                <span>Уровень</span>
                <input type="text" name="level" value="<?= e(old('level', 'A1–A2')) ?>">
            </label>
        </div>

        <div class="form-row">
            <label class="field">
                <span>Начало</span>
                <input type="date" name="starts_on" value="<?= e(old('starts_on', date('Y-m-d'))) ?>">
            </label>
            <label class="field">
                <span>Окончание</span>
                <input type="date" name="ends_on" value="<?= e(old('ends_on', date('Y-m-d', strtotime('+6 weeks')))) ?>">
            </label>
        </div>

        <div class="form-row">
            <label class="field">
                <span>Занятий в потоке</span>
                <input type="number" name="lessons_total" min="1" max="60" value="<?= e(old('lessons_total', '12')) ?>">
            </label>
            <label class="field">
                <span>Статус</span>
                <select name="status">
                    <option value="draft">Черновик</option>
                    <option value="recruiting">Набор</option>
                    <option value="active" selected>Активный</option>
                    <option value="finished">Завершён</option>
                </select>
            </label>
        </div>

        <label class="field">
            <span>Куратор потока</span>
            <select name="curator_id">
                <option value="0">Назначить позже</option>
                <?php foreach ($curators as $curator): ?>
                    <option value="<?= (int)$curator['id'] ?>"><?= e($curator['first_name'] . ' ' . $curator['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="field">
            <span>Описание</span>
            <textarea name="description" placeholder="Коротко: для кого поток и что внутри"><?= e(old('description')) ?></textarea>
        </label>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Создать поток</button>
            <a class="btn btn-ghost" href="<?= e(url('/app/streams')) ?>">Отмена</a>
        </div>
    </div>
</form>
