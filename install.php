<?php
declare(strict_types=1);

require __DIR__ . '/system/bootstrap.php';

$lockFile = STORAGE_PATH . '/installed.lock';
$installed = is_file($lockFile);
$errors = [];
$done = false;
$demoPassword = '';
$rootLogin = '';

function schemaStatements(string $sql): array
{
    $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
    $parts = preg_split('/;\s*(?:\r?\n|$)/', $sql) ?: [];
    return array_values(array_filter(array_map('trim', $parts), static fn (string $s): bool => $s !== ''));
}

if (!$installed && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!Csrf::check($_POST['_token'] ?? null)) {
        $errors[] = 'Сессия истекла, обновите страницу.';
    }

    $firstName = trim((string)($_POST['first_name'] ?? ''));
    $lastName = trim((string)($_POST['last_name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $withDemo = isset($_POST['demo']);

    if ($firstName === '' || $lastName === '') {
        $errors[] = 'Укажите имя и фамилию.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Укажите корректный e-mail.';
    }
    if (mb_strlen($password) < 8) {
        $errors[] = 'Пароль должен быть не короче 8 символов.';
    }

    if (!$errors) {
        try {
            $pdo = Database::pdo();
            $schema = file_get_contents(BASE_PATH . '/database/schema.sql');
            if ($schema === false) {
                throw new RuntimeException('Не найден database/schema.sql');
            }
            foreach (schemaStatements($schema) as $statement) {
                $pdo->exec($statement);
            }

            $settings = [
                'site_title' => 'EMVIA Study',
                'hero_title' => 'Английский, который становится ближе',
                'hero_text' => 'Бесплатная волонтёрская школа: подростки учатся, а волонтёры получают первый опыт преподавания.',
                'contacts_email' => $email,
                'telegram' => 'https://t.me/emvia_study',
            ];
            $stmt = $pdo->prepare('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)');
            foreach ($settings as $k => $v) {
                $stmt->execute([$k, $v]);
            }

            $existing = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
            if ($existing === 0) {
                $login = 'root';
                $rootLogin = $login;
                $stmt = $pdo->prepare(
                    'INSERT INTO users (role, login, email, password_hash, first_name, last_name, must_change_password, status)
                     VALUES (\'root\', ?, ?, ?, ?, ?, 0, \'active\')'
                );
                $stmt->execute([$login, $email, password_hash($password, PASSWORD_DEFAULT), $firstName, $lastName]);
                $rootId = (int)$pdo->lastInsertId();
            } else {
                $rootId = (int)$pdo->query('SELECT id FROM users ORDER BY id LIMIT 1')->fetchColumn();
            }

            if ($withDemo) {
                $demoPassword = 'Emvia' . random_int(1000, 9999);
                $hash = password_hash($demoPassword, PASSWORD_DEFAULT);
                $demoUsers = [
                    ['admin', 'admin@emvia.study', 'Алина', 'Ковалёва', 'admin'],
                    ['curator', 'curator@emvia.study', 'Мария', 'Соколова', 'curator'],
                    ['teacher', 'teacher@emvia.study', 'Илья', 'Ветров', 'teacher'],
                    ['teacher2', 'teacher2@emvia.study', 'Дарья', 'Лисова', 'teacher'],
                    ['smm', 'smm@emvia.study', 'Ника', 'Орлова', 'smm'],
                ];
                $ids = [];
                $ins = $pdo->prepare(
                    'INSERT INTO users (role, login, email, password_hash, first_name, last_name, must_change_password, created_by)
                     VALUES (?, ?, ?, ?, ?, ?, 0, ?)'
                );
                foreach ($demoUsers as [$login, $mail, $first, $last, $role]) {
                    $ins->execute([$role, $login, $mail, $hash, $first, $last, $rootId]);
                    $ids[$login] = (int)$pdo->lastInsertId();
                }

                $students = [
                    ['Аня', 'Морозова'], ['Тимур', 'Хабиров'], ['Соня', 'Лебедева'],
                    ['Марк', 'Иванов'], ['Полина', 'Гусева'], ['Данил', 'Крылов'],
                ];
                $insStudent = $pdo->prepare(
                    'INSERT INTO users (role, login, email, password_hash, first_name, last_name, must_change_password, created_by)
                     VALUES (\'student\', ?, ?, ?, ?, ?, 0, ?)'
                );
                $studentIds = [];
                foreach ($students as $i => [$first, $last]) {
                    $login = generate_login($first, $last);
                    $insStudent->execute([$login, $login . '@example.com', $hash, $first, $last, $rootId]);
                    $studentIds[] = (int)$pdo->lastInsertId();
                }

                $start = strtotime('next monday');
                $ends = strtotime('+6 weeks', $start);

                $pdo->prepare(
                    'INSERT INTO streams (title, language, level, starts_on, ends_on, lessons_total, status, curator_id, description, created_by)
                     VALUES (?, ?, ?, ?, ?, 12, \'active\', ?, ?, ?)'
                )->execute([
                    'Английский A1–A2 · Пилот 1',
                    'Английский',
                    'A1–A2',
                    date('Y-m-d', $start),
                    date('Y-m-d', $ends),
                    $ids['curator'],
                    'Первый поток: две группы, 12 занятий, отчёты и домашние задания.',
                    $rootId,
                ]);
                $streamId = (int)$pdo->lastInsertId();

                $insGroup = $pdo->prepare(
                    'INSERT INTO study_groups (stream_id, name, teacher_id, curator_id, capacity, schedule_note, status)
                     VALUES (?, ?, ?, ?, 5, ?, \'active\')'
                );
                $insGroup->execute([$streamId, 'Группа A · Beginner', $ids['teacher'], $ids['curator'], 'Пн и Ср, 18:00']);
                $groupA = (int)$pdo->lastInsertId();
                $insGroup->execute([$streamId, 'Группа B · Elementary', $ids['teacher2'], $ids['curator'], 'Вт и Чт, 19:00']);
                $groupB = (int)$pdo->lastInsertId();

                $insMember = $pdo->prepare('INSERT INTO group_students (group_id, student_id) VALUES (?, ?)');
                foreach ($studentIds as $i => $sid) {
                    $insMember->execute([$i < 3 ? $groupA : $groupB, $sid]);
                }

                $insLesson = $pdo->prepare(
                    'INSERT INTO lessons (group_id, lesson_no, topic, scheduled_at, duration_min, meeting_url, status)
                     VALUES (?, ?, ?, ?, 60, ?, ?)'
                );
                $topics = ['Знакомство и база', 'О себе', 'Быт и распорядок', 'Вопросы и диалог',
                    'Практика речи', 'Итог и обратная связь'];
                for ($i = 1; $i <= 12; $i++) {
                    $topic = $topics[intdiv($i - 1, 2)] . ' · занятие ' . $i;
                    $dateA = date('Y-m-d H:i:s', strtotime(($i - 1) . ' weeks', $start) + 18 * 3600);
                    $dateB = date('Y-m-d H:i:s', strtotime(($i - 1) . ' weeks', $start) + 86400 + 19 * 3600);
                    $status = $i === 1 ? 'done' : 'planned';
                    $insLesson->execute([$groupA, $i, $topic, $dateA, 'https://meet.google.com/demo-a', $status]);
                    $lessonA = (int)$pdo->lastInsertId();
                    $insLesson->execute([$groupB, $i, $topic, $dateB, 'https://meet.google.com/demo-b', 'planned']);
                    if ($i === 1) {
                        $pdo->prepare(
                            'INSERT INTO lesson_reports (lesson_id, teacher_id, summary, homework_text, attendance_present, attendance_absent,
                                status, submitted_at, created_at)
                             VALUES (?, ?, ?, ?, 3, 0, \'submitted\', NOW(), NOW())'
                        )->execute([
                            $lessonA,
                            $ids['teacher'],
                            'Познакомились, разобрали фразы приветствия и представления. Всем комфортно говорить.',
                            'Выучить 5 фраз знакомства и записать короткое аудио о себе.',
                        ]);
                    }
                }

                $pdo->prepare(
                    'INSERT INTO homework (group_id, title, description, due_at, created_by) VALUES (?, ?, ?, ?, ?)'
                )->execute([
                    $groupA,
                    'Знакомство: расскажи о себе',
                    'Запиши 3–4 предложения о себе по шаблону: Hi! My name is … I am … I like …',
                    date('Y-m-d H:i:s', strtotime('+3 days')),
                    $ids['teacher'],
                ]);
                $hwA = (int)$pdo->lastInsertId();
                $pdo->prepare(
                    'INSERT INTO homework (group_id, title, description, due_at, created_by) VALUES (?, ?, ?, ?, ?)'
                )->execute([
                    $groupB,
                    'Слова о распорядке дня',
                    'Выпиши 6 слов о своём дне и составь с ними предложения.',
                    date('Y-m-d H:i:s', strtotime('+4 days')),
                    $ids['teacher2'],
                ]);

                $pdo->prepare(
                    'INSERT INTO homework_submissions (homework_id, student_id, answer, status, submitted_at)
                     VALUES (?, ?, ?, \'submitted\', NOW())'
                )->execute([$hwA, $studentIds[0], 'Hi! My name is Anya. I am 15. I like books and music.']);
                $pdo->prepare(
                    'INSERT INTO student_progress (student_id, group_id, percent, comment, updated_by, updated_at)
                     VALUES (?, ?, ?, ?, ?, NOW())'
                )->execute([$studentIds[0], $groupA, 35, 'Уверенно представляется, нужно больше практики речи.', $ids['teacher']]);

                $pdo->prepare(
                    'INSERT INTO applications (type, first_name, last_name, email, age, level, goal, status)
                     VALUES (\'student\', "Кирилл", "Ершов", "kirill@example.com", 16, \'A1\', "Хочу перестать бояться говорить", \'new\')'
                )->execute();
                $pdo->prepare(
                    'INSERT INTO applications (type, first_name, last_name, email, level, experience, hours, status)
                     VALUES (\'teacher\', "Анна", "Райт", "anna@example.com", \'B2\', "Репетиторство 1 год", 4, \'in_review\')'
                )->execute();
                $pdo->prepare(
                    'INSERT INTO applications (type, first_name, last_name, email, message, status)
                     VALUES (\'smm\', "Егор", "Смирнов", "egor@example.com", "Веду TikTok, хочу помочь с контентом школы", \'new\')'
                )->execute();

                $taskIns = $pdo->prepare(
                    'INSERT INTO smm_tasks (title, description, platform, assignee_id, due_on, status, created_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                $taskIns->execute(['Снять ролик «5 фраз знакомства»', 'Вертикальный формат, субтитры, 40 секунд.', 'TikTok', $ids['smm'], date('Y-m-d', strtotime('+1 day')), 'in_progress', $rootId]);
                $taskIns->execute(['Опубликовать пост о наборе', 'Текст и карточки в Telegram-канале.', 'Telegram', $ids['smm'], date('Y-m-d', strtotime('+2 days')), 'todo', $rootId]);
                $taskIns->execute(['Собрать статистику недели', 'Подписчики, охват, лайки по платформам.', 'Все площадки', $ids['smm'], date('Y-m-d', strtotime('+3 days')), 'todo', $rootId]);

                $statsIns = $pdo->prepare(
                    'INSERT INTO smm_stats (platform, stat_date, followers, reach, likes, comments, created_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                for ($i = 6; $i >= 0; $i--) {
                    $statsIns->execute(['TikTok', date('Y-m-d', strtotime("-$i days")), 120 + (6 - $i) * 9, 900 + (6 - $i) * 130, 60 + (6 - $i) * 7, 4 + $i, $rootId]);
                }

                $newsIns = $pdo->prepare(
                    'INSERT INTO news (slug, title, title_en, excerpt, excerpt_en, body, body_en, status, published_at, author_id)
                     VALUES (?, ?, ?, ?, ?, ?, ?, \'published\', NOW(), ?)'
                );
                $newsIns->execute([
                    'start-pilota',
                    'Открыт набор в первый поток EMVIA Study',
                    'First stream applications are open',
                    'Английский A1–A2 для подростков 14–17 лет: 6 недель, две встречи в неделю, группы до пяти человек.',
                    'English A1–A2 for teenagers aged 14–17: 6 weeks, two meetings a week, groups of up to five.',
                    "Мы запускаем первый поток волонтёрской школы EMVIA Study.\n\nЗанятия бесплатные, проходят онлайн два раза в неделю. В группе до пяти человек — так у каждого есть время говорить.\n\nЧтобы попасть в поток, заполните заявку на сайте: мы свяжемся с вами и родителями.",
                    "We are launching the first stream of the EMVIA Study volunteer school.\n\nLessons are free and take place online twice a week. Groups are limited to five students, so everyone gets time to speak.\n\nTo join, fill in the application form on the website: we will contact you and your parents.",
                    $rootId,
                ]);
                $newsIns->execute([
                    'komanda',
                    'Ищем преподавателей, кураторов и SMM',
                    'We are looking for teachers, curators and SMM',
                    'Проект растёт: открыты роли для тех, кто хочет попробовать себя в преподавании и организации.',
                    'The project is growing: we have roles for those who want to try teaching and organising.',
                    "EMVIA Study — волонтёрская школа. Мы ищем преподавателей английского от B1, кураторов групп и SMM-специалистов.\n\nЧто даём: опыт, поддержку команды, грамоту по итогам потока и рекомендацию при необходимости.\n\nНапишите нам через форму заявки на сайте — расскажем детали.",
                    "EMVIA Study is a volunteer school. We are looking for English teachers from B1, group curators and SMM specialists.\n\nWhat we offer: experience, team support, a certificate after the stream and a reference if needed.\n\nSend us an application on the website and we will share the details.",
                    $rootId,
                ]);
                $newsIns->execute([
                    'kak-prohodit-zanyatie',
                    'Как проходит занятие в EMVIA Study',
                    'What a lesson at EMVIA Study looks like',
                    'Приветствие, одна тема, практика и короткий итог — без страха ошибки.',
                    'A warm-up, one topic, practice and a short wrap-up — no fear of mistakes.',
                    "Каждое занятие длится час и состоит из четырёх частей: приветствие и разминка, новая тема, практика в парах, итог и домашнее задание.\n\nМы не гоняемся за объёмом: лучше уверенно использовать десять фраз, чем помнить сто и молчать.",
                    "Each lesson lasts an hour and has four parts: a warm-up, a new topic, pair practice, and a wrap-up with homework.\n\nWe do not chase volume: it is better to use ten phrases confidently than to know a hundred and stay silent.",
                    $rootId,
                ]);
            }

            file_put_contents($lockFile, date('c'));
            $done = true;
        } catch (Throwable $e) {
            $errors[] = 'Ошибка установки: ' . $e->getMessage();
        }
    }
}

$token = Csrf::token();
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Установка · EMVIA Study</title>
    <link rel="preload" href="<?= e(base_path() . '/assets/fonts/manrope-cyrillic.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= e(base_path() . '/assets/fonts/manrope-latin.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(base_path() . '/assets/css/app.css') ?>">
</head>
<body class="auth-body">
<div class="auth-card" style="max-width:640px">
    <a class="brand brand-link brand-lg" href="<?= e(url('/')) ?>">
        <img class="logo-full" src="<?= e(base_path() . '/assets/img/logo.svg') ?>" alt="EMVIA Study">
    </a>

    <?php if ($installed && !$done): ?>
        <h1 class="auth-title">Сайт уже установлен</h1>
        <p class="muted">Файл <code>storage/installed.lock</code> существует. Для повторной установки удалите его вручную.</p>
        <a class="btn btn-primary btn-block" href="<?= e(url('/login')) ?>">Перейти ко входу</a>
    <?php elseif ($done): ?>
        <h1 class="auth-title">Готово</h1>
        <p class="muted">База создана, аккаунт руководителя готов. Удалите файл <code>install.php</code> с сервера.</p>
        <div class="card card-soft">
            <p><strong>Вход руководителя:</strong> <code>root</code> / пароль, который вы указали.</p>
            <?php if ($demoPassword !== ''): ?>
                <p><strong>Демо-аккаунты</strong> (пароль у всех один): <code><?= e($demoPassword) ?></code></p>
                <p class="muted small">admin · curator · teacher · teacher2 · smm — логин совпадает с именем аккаунта.</p>
            <?php endif; ?>
        </div>
        <a class="btn btn-primary btn-block" href="<?= e(url('/login')) ?>">Войти</a>
    <?php else: ?>
        <h1 class="auth-title">Установка EMVIA Study</h1>
        <p class="muted">Проверьте данные в <code>config/config.php</code>, затем создайте базу и аккаунт руководителя.</p>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endforeach; ?>

        <form method="post" class="form">
            <input type="hidden" name="_token" value="<?= e($token) ?>">
            <div class="grid-2">
                <label class="field">
                    <span>Имя</span>
                    <input type="text" name="first_name" value="<?= e(old('first_name', 'Глеб')) ?>" required>
                </label>
                <label class="field">
                    <span>Фамилия</span>
                    <input type="text" name="last_name" value="<?= e(old('last_name', '')) ?>" required>
                </label>
            </div>
            <label class="field">
                <span>E-mail руководителя</span>
                <input type="email" name="email" value="<?= e(old('email', '')) ?>" required>
            </label>
            <label class="field">
                <span>Пароль (минимум 8 символов)</span>
                <input type="password" name="password" required minlength="8">
            </label>
            <label class="checkbox">
                <input type="checkbox" name="demo" value="1" checked>
                <span>Добавить демо-данные: поток, группы, занятия, новости и аккаунты команды</span>
            </label>
            <button class="btn btn-primary btn-block" type="submit">Установить</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
