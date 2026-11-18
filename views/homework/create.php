<?php
/** @var array $groups */
?>
<form class="card" method="post" action="<?= e(url('/app/homework')) ?>" style="max-width:720px">
    <?= csrf_field() ?>
    <h2 class="card-title" style="margin-bottom:16px">Новое задание</h2>

    <div class="form">
        <label class="field">
            <span>Группа</span>
            <select name="group_id" required>
                <option value="">Выберите группу</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= (int)$group['id'] ?>"><?= e($group['stream_title'] . ' · ' . $group['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="field">
            <span>Название</span>
            <input type="text" name="title" value="<?= e(old('title')) ?>" placeholder="Например: рассказ о себе" required>
        </label>

        <label class="field">
            <span>Описание</span>
            <textarea name="description" placeholder="Что нужно сделать, какие фразы использовать"><?= e(old('description')) ?></textarea>
        </label>

        <label class="field">
            <span>Срок сдачи</span>
            <input type="datetime-local" name="due_at" value="<?= e(old('due_at', date('Y-m-d\T18:00', strtotime('+3 days')))) ?>">
        </label>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Опубликовать</button>
            <a class="btn btn-ghost" href="<?= e(url('/app/homework')) ?>">Отмена</a>
        </div>
    </div>
</form>
