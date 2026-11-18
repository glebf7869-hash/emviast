<?php
/** @var string $type */
/** @var bool $sent */
$tabs = [
    'student' => 'apply.tab_student',
    'teacher' => 'apply.tab_teacher',
    'curator' => 'apply.tab_curator',
    'smm' => 'apply.tab_smm',
];
?>
<section class="page-head">
    <div class="container">
        <span class="eyebrow"><?= e(t('apply.kicker')) ?></span>
        <h1><?= e(t('apply.title')) ?></h1>
        <p><?= e(t('apply.lead')) ?></p>
    </div>
</section>

<section class="section-sm">
    <div class="container split" style="gap:26px">
        <div class="card">
            <?php if ($sent): ?>
                <div class="eyebrow"><?= e(t('apply.sent_title')) ?></div>
                <h2 style="margin-bottom:10px"><?= e(t('apply.sent_title')) ?></h2>
                <p class="muted"><?= e(t('apply.sent_text')) ?></p>
                <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:18px">
                    <a class="btn btn-primary" href="<?= e(url('/')) ?>"><?= e(t('apply.home')) ?></a>
                    <a class="btn btn-ghost" href="<?= e(url('/apply')) ?>"><?= e(t('apply.another')) ?></a>
                </div>
            <?php else: ?>
                <div class="chip-group" style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:22px">
                    <?php foreach ($tabs as $key => $label): ?>
                        <a class="btn btn-sm <?= $type === $key ? 'btn-primary' : 'btn-ghost' ?>"
                           href="<?= e(url('/apply?type=' . $key)) ?>"><?= e(t($label)) ?></a>
                    <?php endforeach; ?>
                </div>

                <form class="form" method="post" action="<?= e(url('/apply')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="type" value="<?= e($type) ?>">

                    <div class="form-row">
                        <label class="field">
                            <span><?= e(t('apply.first')) ?></span>
                            <input type="text" name="first_name" value="<?= e(old('first_name')) ?>" required>
                        </label>
                        <label class="field">
                            <span><?= e(t('apply.last')) ?></span>
                            <input type="text" name="last_name" value="<?= e(old('last_name')) ?>" required>
                        </label>
                    </div>

                    <div class="form-row">
                        <label class="field">
                            <span><?= e(t('apply.email')) ?></span>
                            <input type="email" name="email" value="<?= e(old('email')) ?>" required>
                        </label>
                        <label class="field">
                            <span><?= e(t('apply.phone')) ?></span>
                            <input type="text" name="phone" value="<?= e(old('phone')) ?>" placeholder="<?= e(t('apply.phone_hint')) ?>">
                        </label>
                    </div>

                    <?php if ($type === 'student'): ?>
                        <div class="form-row">
                            <label class="field">
                                <span><?= e(t('apply.age')) ?></span>
                                <input type="number" name="age" min="14" max="17" value="<?= e(old('age', '15')) ?>" required>
                                <span class="hint"><?= e(t('apply.age_hint')) ?></span>
                            </label>
                            <label class="field">
                                <span><?= e(t('apply.level')) ?></span>
                                <select name="level">
                                    <option value="A1"><?= e(t('apply.level_a1')) ?></option>
                                    <option value="A2"><?= e(t('apply.level_a2')) ?></option>
                                </select>
                            </label>
                        </div>

                        <div class="form-row">
                            <label class="field">
                                <span><?= e(t('apply.days')) ?></span>
                                <div class="option-grid">
                                    <?php foreach (student_day_presets() as $value => $label): ?>
                                        <label class="option">
                                            <input type="radio" name="schedule_days" value="<?= e($value) ?>"
                                                <?= old('schedule_days') === $value ? 'checked' : '' ?> required>
                                            <span><?= e($label) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </label>
                            <label class="field">
                                <span><?= e(t('apply.time')) ?></span>
                                <div class="option-grid">
                                    <?php foreach (time_slot_labels() as $value => $label): ?>
                                        <label class="option">
                                            <input type="radio" name="schedule_time" value="<?= e($value) ?>"
                                                <?= old('schedule_time') === $value ? 'checked' : '' ?> required>
                                            <span><?= e($label) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </label>
                        </div>

                        <label class="field">
                            <span><?= e(t('apply.goal')) ?></span>
                            <textarea name="goal" placeholder="<?= e(t('apply.goal_placeholder')) ?>"><?= e(old('goal')) ?></textarea>
                        </label>
                    <?php elseif ($type === 'teacher'): ?>
                        <div class="form-row">
                            <label class="field">
                                <span><?= e(t('apply.level')) ?></span>
                                <select name="level">
                                    <option value="B1"><?= e(t('apply.level_b1')) ?></option>
                                    <option value="B2"><?= e(t('apply.level_b2')) ?></option>
                                    <option value="C1"><?= e(t('apply.level_c1')) ?></option>
                                </select>
                            </label>
                            <label class="field">
                                <span><?= e(t('apply.hours')) ?></span>
                                <input type="number" name="hours" min="1" max="20" value="<?= e(old('hours', '4')) ?>">
                            </label>
                        </div>

                        <label class="field">
                            <span><?= e(t('apply.groups')) ?></span>
                            <input type="number" name="groups_ready" min="1" max="5" value="<?= e(old('groups_ready', '1')) ?>">
                            <span class="hint"><?= e(t('apply.groups_hint')) ?></span>
                        </label>

                        <div class="field">
                            <span><?= e(t('apply.teacher_days')) ?></span>
                            <div class="option-grid">
                                <?php foreach (day_labels() as $value => $label): ?>
                                    <label class="option">
                                        <input type="checkbox" name="schedule_days[]" value="<?= e($value) ?>"
                                            <?= in_array($value, (array)old('schedule_days', []), true) ? 'checked' : '' ?>>
                                        <span><?= e($label) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="field">
                            <span><?= e(t('apply.teacher_time')) ?></span>
                            <div class="option-grid">
                                <?php foreach (time_slot_labels() as $value => $label): ?>
                                    <label class="option">
                                        <input type="checkbox" name="schedule_time[]" value="<?= e($value) ?>"
                                            <?= in_array($value, (array)old('schedule_time', []), true) ? 'checked' : '' ?>>
                                        <span><?= e($label) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <label class="field">
                            <span><?= e(t('apply.experience')) ?></span>
                            <textarea name="experience" placeholder="<?= e(t('apply.experience_placeholder')) ?>"><?= e(old('experience')) ?></textarea>
                        </label>
                    <?php elseif ($type === 'curator'): ?>
                        <div class="form-row">
                            <label class="field">
                                <span><?= e(t('apply.hours')) ?></span>
                                <input type="number" name="hours" min="1" max="20" value="<?= e(old('hours', '3')) ?>">
                            </label>
                            <label class="field">
                                <span><?= e(t('apply.timezone')) ?></span>
                                <input type="text" name="timezone" value="<?= e(old('timezone', 'UTC+3')) ?>" placeholder="<?= e(t('apply.timezone_placeholder')) ?>">
                            </label>
                        </div>
                        <label class="field">
                            <span><?= e(t('apply.experience')) ?></span>
                            <textarea name="experience"><?= e(old('experience')) ?></textarea>
                        </label>
                    <?php else: ?>
                        <div class="form-row">
                            <label class="field">
                                <span><?= e(t('apply.hours')) ?></span>
                                <input type="number" name="hours" min="1" max="40" value="<?= e(old('hours', '5')) ?>">
                            </label>
                            <label class="field">
                                <span><?= e(t('apply.experience')) ?></span>
                                <input type="text" name="experience" value="<?= e(old('experience')) ?>" placeholder="TikTok, Telegram, Reels">
                            </label>
                        </div>
                    <?php endif; ?>

                    <label class="field">
                        <span><?= e(t('apply.message')) ?></span>
                        <textarea name="message" placeholder="<?= e(t('apply.message_placeholder')) ?>"><?= e(old('message')) ?></textarea>
                    </label>

                    <label class="checkbox">
                        <input type="checkbox" required>
                        <span><?= e(t('apply.consent')) ?></span>
                    </label>

                    <button class="btn btn-primary" type="submit"><?= e(t('apply.send')) ?></button>
                </form>
            <?php endif; ?>
        </div>

        <div class="card card-soft" style="padding:30px">
            <h3><?= e(t('apply.side_title')) ?></h3>
            <div class="kv" style="grid-template-columns:1fr; gap:16px; margin-top:16px">
                <div>
                    <strong><?= e(t('apply.side_1')) ?></strong>
                    <div class="muted small"><?= e(t('apply.side_1_text')) ?></div>
                </div>
                <div>
                    <strong><?= e(t('apply.side_2')) ?></strong>
                    <div class="muted small"><?= e(t('apply.side_2_text')) ?></div>
                </div>
                <div>
                    <strong><?= e(t('apply.side_3')) ?></strong>
                    <div class="muted small"><?= e(t('apply.side_3_text')) ?></div>
                </div>
            </div>
            <div class="divider"></div>
            <p class="muted small" style="margin:0"><?= e(t('apply.privacy')) ?></p>
        </div>
    </div>
</section>
