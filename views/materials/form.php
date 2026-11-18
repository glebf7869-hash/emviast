<?php
/** @var array|null $item */
$isEdit = !empty($item);
$levels = ['A1', 'A2', 'B1', 'B2', 'C1'];
?>
<form class="card" method="post" action="<?= e(url($isEdit ? '/app/materials/' . $item['id'] : '/app/materials')) ?>" style="max-width:780px">
    <?= csrf_field() ?>
    <h2 class="card-title" style="margin-bottom:16px"><?= $isEdit ? 'Редактирование пособия' : 'Новое пособие' ?></h2>

    <div class="form">
        <label class="field">
            <span>Название</span>
            <input type="text" name="title" value="<?= e($item['title'] ?? '') ?>" placeholder="Например: English File Elementary" required>
        </label>

        <div class="form-row">
            <label class="field">
                <span>Автор или издательство</span>
                <input type="text" name="author" value="<?= e($item['author'] ?? '') ?>" placeholder="Oxford University Press">
            </label>
            <label class="field">
                <span>Уровень</span>
                <select name="level">
                    <option value="">Не указан</option>
                    <?php foreach ($levels as $level): ?>
                        <option value="<?= e($level) ?>" <?= ($item['level'] ?? '') === $level ? 'selected' : '' ?>><?= e($level) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <label class="field">
            <span>Ссылка на материалы</span>
            <input type="url" name="url" value="<?= e($item['url'] ?? '') ?>" placeholder="https://...">
            <span class="hint">Ссылка на диск, PDF или страницу издательства</span>
        </label>

        <label class="field">
            <span>Описание</span>
            <textarea name="description" rows="4" placeholder="Для чего подходит, какие разделы брать"><?= e($item['description'] ?? '') ?></textarea>
        </label>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Сохранить' : 'Добавить в каталог' ?></button>
            <a class="btn btn-ghost" href="<?= e(url('/app/materials')) ?>">К каталогу</a>
        </div>
    </div>
</form>
