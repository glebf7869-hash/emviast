<?php
/** @var array|null $item */
$isEdit = !empty($item);
?>
<form class="card" method="post" action="<?= e(url($isEdit ? '/app/documents/' . $item['id'] : '/app/documents')) ?>" enctype="multipart/form-data" style="max-width:720px">
    <?= csrf_field() ?>
    <h2 class="card-title" style="margin-bottom:16px"><?= $isEdit ? 'Редактирование документа' : 'Новый документ' ?></h2>

    <div class="form">
        <label class="field">
            <span>Название</span>
            <input type="text" name="title" value="<?= e($item['title'] ?? '') ?>" placeholder="Правила школы, согласие родителя…" required>
        </label>

        <label class="field">
            <span>Файл</span>
            <input type="file" name="file" accept=".pdf,.doc,.docx,.txt">
            <span class="hint">PDF, DOC, DOCX или TXT до 15 МБ.<?= $isEdit && !empty($item['file']) ? ' Текущий файл уже загружен — можно оставить.' : '' ?></span>
        </label>

        <label class="field">
            <span>или ссылка</span>
            <input type="url" name="url" value="<?= e($item['url'] ?? '') ?>" placeholder="https://...">
        </label>

        <div class="form-row">
            <label class="field">
                <span>Описание</span>
                <input type="text" name="description" value="<?= e($item['description'] ?? '') ?>" placeholder="Коротко о документе">
            </label>
            <label class="field">
                <span>Порядок</span>
                <input type="number" name="sort_order" min="0" max="999" value="<?= e((string)($item['sort_order'] ?? 0)) ?>">
                <span class="hint">Меньше — выше</span>
            </label>
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Сохранить' : 'Добавить документ' ?></button>
            <a class="btn btn-ghost" href="<?= e(url('/app/documents')) ?>">К списку</a>
        </div>
    </div>
</form>
