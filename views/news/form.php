<?php
/** @var array|null $item */
$item = $item ?: [];
$isEdit = !empty($item);
$canPublish = in_array(user_role(), ['root', 'admin'], true);
?>
<form class="card" method="post" action="<?= e(url($isEdit ? '/app/news/' . $item['id'] : '/app/news')) ?>" enctype="multipart/form-data" style="max-width:820px">
    <?= csrf_field() ?>
    <h2 class="card-title" style="margin-bottom:16px"><?= $isEdit ? 'Редактирование публикации' : 'Новая публикация' ?></h2>

    <div class="form">
        <label class="field">
            <span>Заголовок</span>
            <input type="text" name="title" value="<?= e($item['title'] ?? old('title')) ?>" required>
        </label>

        <label class="field">
            <span>Короткое описание</span>
            <input type="text" name="excerpt" maxlength="255" value="<?= e($item['excerpt'] ?? old('excerpt')) ?>" placeholder="Одно предложение для карточки новости">
        </label>

        <label class="field">
            <span>Обложка новости</span>
            <input type="file" name="cover" accept="image/jpeg,image/png,image/webp">
            <span class="hint">JPG, PNG или WebP до 5 МБ. Показывается в карточке и в самой новости.</span>
        </label>

        <?php if ($isEdit && !empty($item['cover'])): ?>
            <div style="display:flex; align-items:center; gap:12px">
                <img class="thumb" src="<?= e(file_url($item['cover'])) ?>" alt="Обложка">
                <span class="muted small">Текущая обложка</span>
            </div>
        <?php endif; ?>

        <label class="field">
            <span>Текст</span>
            <textarea name="body" rows="14" required placeholder="Абзацы разделяйте пустой строкой"><?= e($item['body'] ?? old('body')) ?></textarea>
        </label>

        <div class="divider"></div>
        <h3 style="font-size:15px">English version</h3>

        <label class="field">
            <span>Title (EN)</span>
            <input type="text" name="title_en" value="<?= e($item['title_en'] ?? '') ?>" placeholder="English title">
        </label>

        <label class="field">
            <span>Short description (EN)</span>
            <input type="text" name="excerpt_en" maxlength="255" value="<?= e($item['excerpt_en'] ?? '') ?>" placeholder="One sentence for the news card">
        </label>

        <label class="field">
            <span>Text (EN)</span>
            <textarea name="body_en" rows="10" placeholder="Separate paragraphs with a blank line"><?= e($item['body_en'] ?? '') ?></textarea>
            <span class="hint">Для публикации нужны заголовок и текст на английском.</span>
        </label>

        <?php if ($canPublish): ?>
            <label class="field">
                <span>Статус</span>
                <select name="status">
                    <option value="draft" <?= ($item['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Черновик</option>
                    <option value="published" <?= ($item['status'] ?? '') === 'published' ? 'selected' : '' ?>>Опубликовано</option>
                </select>
            </label>
        <?php else: ?>
            <div class="alert alert-info">SMM-команда сохраняет черновики — публикует администратор.</div>
        <?php endif; ?>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Сохранить' : 'Создать' ?></button>
            <a class="btn btn-ghost" href="<?= e(url('/app/news')) ?>">К списку</a>
        </div>
    </div>
</form>

<?php if ($isEdit && $canPublish): ?>
    <form method="post" action="<?= e(url('/app/news/' . $item['id'] . '/delete')) ?>" style="margin-top:16px"
          data-confirm="Удалить публикацию без возможности восстановления?">
        <?= csrf_field() ?>
        <button class="btn btn-danger" type="submit">Удалить публикацию</button>
    </form>
<?php endif; ?>
