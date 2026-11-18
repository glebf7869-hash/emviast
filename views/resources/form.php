<?php
/** @var array|null $item */
$isEdit = !empty($item);
?>
<form class="card" method="post" action="<?= e(url($isEdit ? '/app/resources/' . $item['id'] : '/app/resources')) ?>" style="max-width:780px">
    <?= csrf_field() ?>
    <h2 class="card-title" style="margin-bottom:16px"><?= $isEdit ? 'Редактирование ресурса' : 'Новый ресурс' ?></h2>

    <div class="form">
        <label class="field">
            <span>Название</span>
            <input type="text" name="title" value="<?= e($item['title'] ?? '') ?>" placeholder="Например: English File Elementary — папка" required>
        </label>

        <label class="field">
            <span>Ссылка на Google Диск</span>
            <input type="url" name="url" value="<?= e($item['url'] ?? '') ?>" placeholder="https://drive.google.com/drive/folders/..." required>
            <span class="hint">
                Вставьте ссылку на папку или файл. Папка должна быть открыта по ссылке
                («Доступ по ссылке — просмотр»), иначе встроенный просмотр не сработает.
            </span>
        </label>

        <div class="form-row">
            <label class="field">
                <span>Учебник</span>
                <input type="text" name="textbook" value="<?= e($item['textbook'] ?? '') ?>" placeholder="English File, Solutions…">
            </label>
            <label class="field">
                <span>Тема</span>
                <input type="text" name="topic" value="<?= e($item['topic'] ?? '') ?>" placeholder="Present Simple, Past Simple…">
            </label>
        </div>

        <div class="form-row">
            <label class="field">
                <span>Уровень</span>
                <input type="text" name="level" value="<?= e($item['level'] ?? '') ?>" placeholder="A1–A2">
            </label>
            <label class="field">
                <span>Кому доступно</span>
                <select name="access">
                    <option value="staff" <?= ($item['access'] ?? 'staff') === 'staff' ? 'selected' : '' ?>>Преподавателям и кураторам</option>
                    <option value="admins" <?= ($item['access'] ?? '') === 'admins' ? 'selected' : '' ?>>Только администраторам</option>
                </select>
            </label>
        </div>

        <label class="field">
            <span>Описание</span>
            <textarea name="description" rows="3" placeholder="Что внутри папки и как использовать"><?= e($item['description'] ?? '') ?></textarea>
        </label>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Сохранить' : 'Добавить ресурс' ?></button>
            <a class="btn btn-ghost" href="<?= e(url('/app/resources')) ?>">К каталогу</a>
        </div>
    </div>
</form>
