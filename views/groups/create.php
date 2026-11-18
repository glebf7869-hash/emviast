<?php
/** @var array $streams */
/** @var array $teachers */
/** @var array $curators */
?>
<form class="card" method="post" action="<?= e(url('/app/groups')) ?>" style="max-width:780px">
    <?= csrf_field() ?>
    <h2 class="card-title" style="margin-bottom:16px">Новая группа</h2>

    <div class="form">
        <label class="field">
            <span>Поток</span>
            <select name="stream_id" required>
                <option value="">Выберите поток</option>
                <?php foreach ($streams as $stream): ?>
                    <option value="<?= (int)$stream['id'] ?>">
                        <?= e($stream['title'] . ' · ' . $stream['level']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <div class="form-row">
            <label class="field">
                <span>Название группы</span>
                <input type="text" name="name" placeholder="Группа A" required>
            </label>
            <label class="field">
                <span>Мест</span>
                <input type="number" name="capacity" min="1" max="20" value="5">
            </label>
        </div>

        <div class="form-row">
            <label class="field">
                <span>Преподаватель</span>
                <select name="teacher_id">
                    <option value="0">Назначить позже</option>
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?= (int)$teacher['id'] ?>"><?= e($teacher['first_name'] . ' ' . $teacher['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field">
                <span>Куратор</span>
                <select name="curator_id">
                    <option value="0">Как в потоке</option>
                    <?php foreach ($curators as $curator): ?>
                        <option value="<?= (int)$curator['id'] ?>"><?= e($curator['first_name'] . ' ' . $curator['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <div class="form-row">
            <label class="field">
                <span>Расписание</span>
                <input type="text" name="schedule_note" placeholder="Пн и Ср, 18:00">
            </label>
            <label class="field">
                <span>Статус</span>
                <select name="status">
                    <option value="forming">Формируется</option>
                    <option value="active" selected>Активная</option>
                    <option value="finished">Завершена</option>
                </select>
            </label>
        </div>

        <p class="hint muted small" style="margin:0">Занятия появятся в расписании автоматически — по числу занятий в потоке.</p>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Создать группу</button>
            <a class="btn btn-ghost" href="<?= e(url('/app/groups')) ?>">Отмена</a>
        </div>
    </div>
</form>
