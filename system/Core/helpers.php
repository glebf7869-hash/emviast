<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Базовый путь сайта. Определяется автоматически: если сайт лежит
 * в /emvia, то все ссылки получат этот префикс.
 */
function current_lang(): string
{
    $lang = (string)($_SESSION['lang'] ?? 'ru');
    return in_array($lang, ['ru', 'en'], true) ? $lang : 'ru';
}

function t(string $key): string
{
    static $dict = [];

    $lang = current_lang();
    if (!array_key_exists($lang, $dict)) {
        $file = BASE_PATH . '/lang/' . $lang . '.php';
        $dict[$lang] = is_file($file) ? (array)require $file : [];
    }
    if (isset($dict[$lang][$key])) {
        return (string)$dict[$lang][$key];
    }

    if (!array_key_exists('ru', $dict)) {
        $file = BASE_PATH . '/lang/ru.php';
        $dict['ru'] = is_file($file) ? (array)require $file : [];
    }
    return (string)($dict['ru'][$key] ?? $key);
}

function base_path(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    $configured = trim((string)config('app.base_url', ''));
    if ($configured !== '') {
        $parsed = parse_url($configured, PHP_URL_PATH);
        $base = is_string($parsed) ? rtrim($parsed, '/') : '';
        return $base === '/' ? '' : $base;
    }

    // 1. Путь исполняемого файла относительно DOCUMENT_ROOT.
    //    Это самый надёжный ориентир: некоторые хостинги «обрезают»
    //    SCRIPT_NAME до /index.php даже в подпапке.
    $docRoot = (string)($_SERVER['DOCUMENT_ROOT'] ?? '');
    $scriptFile = (string)($_SERVER['SCRIPT_FILENAME'] ?? '');

    $realDoc = $docRoot !== '' ? realpath($docRoot) : false;
    $realScript = $scriptFile !== '' ? realpath($scriptFile) : false;

    if ($realDoc !== false && $realScript !== false) {
        $docRootClean = rtrim($realDoc, DIRECTORY_SEPARATOR);
        if ($realScript === $docRootClean || str_starts_with($realScript, $docRootClean . DIRECTORY_SEPARATOR)) {
            $relative = '/' . ltrim(substr($realScript, strlen($docRootClean)), DIRECTORY_SEPARATOR);
            $dir = rtrim(str_replace('\\', '/', dirname($relative)), '/');
            if ($dir !== '.' && $dir !== '/') {
                $base = $dir;
                return $base;
            }
            // DOCUMENT_ROOT уже указывает на папку сайта — значит сайт в корне.
            $base = '';
            return $base;
        }
    }

    // 1б. Тот же расчёт без realpath (на случай open_basedir/симлинков).
    $docRaw = str_replace('\\', '/', $docRoot);
    $scriptRaw = str_replace('\\', '/', $scriptFile);
    $docRawClean = rtrim($docRaw, '/');
    if ($docRawClean !== '' && $scriptRaw !== '' && str_starts_with($scriptRaw, $docRawClean . '/')) {
        $relative = '/' . ltrim(substr($scriptRaw, strlen($docRawClean)), '/');
        $dir = rtrim(dirname($relative), '/');
        if ($dir !== '.' && $dir !== '/') {
            $base = $dir;
            return $base;
        }
        $base = '';
        return $base;
    }

    // 2. Запрос идёт явно через /index.php — каталог виден в REQUEST_URI.
    $uriPath = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '');
    if ($uriPath !== '' && str_ends_with($uriPath, '/index.php')) {
        $dir = rtrim(dirname($uriPath), '/');
        $base = ($dir === '.' || $dir === '/') ? '' : $dir;
        return $base;
    }

    // 3. Запасной вариант — каталог из SCRIPT_NAME.
    $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
    $base = ($dir === '.' || $dir === '/' || $dir === '') ? '' : $dir;
    return $base;
}

/**
 * Как строить ссылки:
 *   pretty — /каталог/about  (нужен mod_rewrite / try_files)
 *   query  — /каталог/index.php?r=/about (работает на любом хостинге)
 * auto — pretty на Apache/LiteSpeed, query на nginx и если нет .htaccess.
 */
/**
 * Текущий маршрут (без каталога и query-строки) — для подсветки меню.
 */
function current_path(): string
{
    if (isset($GLOBALS['current_path']) && is_string($GLOBALS['current_path'])) {
        return $GLOBALS['current_path'];
    }

    $path = (string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    $base = base_path();
    if ($base !== '' && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base));
    }
    return $path === '' ? '/' : $path;
}

function url_style(): string
{
    // «auto» и «query» работают одинаково: ссылки через index.php.
    // Красивые адреса включаются только явно: 'pretty'.
    $configured = strtolower(trim((string)config('app.url_style', 'auto')));
    return $configured === 'pretty' ? 'pretty' : 'query';
}

function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    $query = '';

    if (($pos = strpos($path, '?')) !== false) {
        $query = substr($path, $pos + 1);
        $path = substr($path, 0, $pos);
    }

    if (url_style() === 'query') {
        // Ссылка относительная: работает в любой папке, даже если
        // сервер неправильно сообщает путь к сайту.
        $result = 'index.php';
        if ($path !== '') {
            $result .= '?r=' . rawurlencode('/' . $path);
        }
        if ($query !== '') {
            $result .= ($path !== '' ? '&' : '?') . $query;
        }
        return $result;
    }

    $result = base_path() . '/' . $path;
    if ($query !== '') {
        $result .= '?' . $query;
    }
    return $result;
}

/**
 * Скрытое поле для GET-форм, чтобы в query-режиме не терялся маршрут.
 */
function route_field(): string
{
    if (url_style() !== 'query') {
        return '';
    }
    return '<input type="hidden" name="r" value="' . e((string)($_GET['r'] ?? '')) . '">';
}

function asset(string $path): string
{
    $path = ltrim($path, '/');
    // В query-режиме все страницы живут на index.php, поэтому путь
    // к файлам можно давать относительно страницы.
    return url_style() === 'query'
        ? 'assets/' . $path
        : base_path() . '/assets/' . $path;
}

/**
 * Прямая ссылка на загруженный файл (фотоотчёты и т.п.).
 */
function file_url(string $path): string
{
    $path = ltrim($path, '/');
    return url_style() === 'query' ? $path : base_path() . '/' . $path;
}

/**
 * Абсолютный адрес для писем (относительные ссылки в письме не работают).
 */
function absolute_url(string $path = ''): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
    $base = base_path();
    $clean = '/' . ltrim($path, '/');

    if (url_style() === 'query') {
        $target = $base . '/index.php?r=' . rawurlencode($clean);
    } else {
        $target = $base . $clean;
    }

    return $scheme . '://' . $host . $target;
}

function redirect(string $path): never
{
    $target = preg_match('#^https?://#', $path) ? $path : url($path);
    header('Location: ' . $target);
    exit;
}

function back(): never
{
    $ref = $_SERVER['HTTP_REFERER'] ?? url('/app');
    header('Location: ' . $ref);
    exit;
}

function csrf_field(): string
{
    return '<input type=\'hidden\' name=\'_token\' value="' . e(Csrf::token()) . '">';
}

function current_user(): ?array
{
    return Auth::user();
}

function user_role(): string
{
    return Auth::user()['role'] ?? 'guest';
}

function role_in(array $roles): bool
{
    return in_array(user_role(), $roles, true);
}

function flash_set(string $type, string $message): void
{
    Flash::set($type, $message);
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function fmt_date(?string $value, string $format = 'd.m.Y'): string
{
    if (!$value) {
        return '—';
    }
    $ts = strtotime($value);
    return $ts ? date($format, $ts) : '—';
}

function fmt_dt(?string $value): string
{
    return fmt_date($value, 'd.m.Y H:i');
}

function fmt_time(?string $value): string
{
    return fmt_date($value, 'H:i');
}

function plural_ru(int $n, string $one, string $few, string $many): string
{
    $n = abs($n) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) {
        return $many;
    }
    if ($n1 > 1 && $n1 < 5) {
        return $few;
    }
    if ($n1 === 1) {
        return $one;
    }
    return $many;
}

function initials(string $first, string $last): string
{
    return mb_strtoupper(mb_substr($first, 0, 1) . mb_substr($last, 0, 1));
}

function translit(string $value): string
{
    $map = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '',
        'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    ];
    $value = mb_strtolower(trim($value));
    $value = strtr($value, $map);
    $value = preg_replace('/[^a-z0-9]+/', '.', $value) ?? '';
    return trim($value, '.');
}

/**
 * Логин вида ivan.petrov, ivan.petrov2, ivan.petrov3 и т.д.
 */
function generate_login(string $first, string $last): string
{
    $base = translit($first) . '.' . translit($last);
    $base = trim(preg_replace('/\.+/', '.', $base) ?? '', '.');
    if ($base === '' || $base === '.') {
        $base = 'student';
    }
    $login = $base;
    $i = 2;
    while (Database::fetch('SELECT id FROM users WHERE login = ?', [$login])) {
        $login = $base . $i;
        $i++;
    }
    return $login;
}

function random_password(int $length = 10): string
{
    $alphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $max = strlen($alphabet) - 1;
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $alphabet[random_int(0, $max)];
    }
    return $password;
}

function day_labels(): array
{
    return ['mon' => 'Пн', 'tue' => 'Вт', 'wed' => 'Ср', 'thu' => 'Чт', 'fri' => 'Пт', 'sat' => 'Сб', 'sun' => 'Вс'];
}

function time_slot_labels(): array
{
    return [
        '09-12' => '09:00–12:00',
        '12-15' => '12:00–15:00',
        '15-18' => '15:00–18:00',
        '18-21' => '18:00–21:00',
    ];
}

function student_day_presets(): array
{
    return [
        'tue,thu' => 'Вт и Чт',
        'mon,wed,fri' => 'Пн, Ср, Пт',
    ];
}

function format_schedule_days(?string $csv): string
{
    if (!$csv) {
        return '—';
    }
    $labels = day_labels();
    $result = [];
    foreach (explode(',', $csv) as $code) {
        $code = trim($code);
        if (isset($labels[$code])) {
            $result[] = $labels[$code];
        }
    }
    return $result ? implode(', ', $result) : '—';
}

function format_time_slots(?string $csv): string
{
    if (!$csv) {
        return '—';
    }
    $labels = time_slot_labels();
    $result = [];
    foreach (explode(',', $csv) as $code) {
        $code = trim($code);
        if (isset($labels[$code])) {
            $result[] = $labels[$code];
        }
    }
    return $result ? implode(', ', $result) : '—';
}

function news_title(array $item): string
{
    if (current_lang() === 'en' && !empty($item['title_en'])) {
        return (string)$item['title_en'];
    }
    return (string)($item['title'] ?? '');
}

function news_excerpt(array $item): string
{
    if (current_lang() === 'en' && !empty($item['excerpt_en'])) {
        return (string)$item['excerpt_en'];
    }
    return (string)($item['excerpt'] ?? '');
}

function news_body(array $item): string
{
    if (current_lang() === 'en' && !empty($item['body_en'])) {
        return (string)$item['body_en'];
    }
    return (string)($item['body'] ?? '');
}

function status_meta(?string $status): array
{
    $map = [
        'new' => ['Новая', 'badge-blue'],
        'in_review' => ['На рассмотрении', 'badge-amber'],
        'approved' => ['Одобрена', 'badge-green'],
        'rejected' => ['Отклонена', 'badge-red'],
        'waitlist' => ['Лист ожидания', 'badge-grey'],

        'active' => ['Активна', 'badge-green'],
        'blocked' => ['Заблокирована', 'badge-red'],

        'draft' => ['Черновик', 'badge-grey'],
        'recruiting' => ['Набор', 'badge-blue'],
        'finished' => ['Завершён', 'badge-grey'],
        'forming' => ['Формируется', 'badge-amber'],

        'planned' => ['Запланировано', 'badge-blue'],
        'done' => ['Проведено', 'badge-green'],
        'canceled' => ['Отменено', 'badge-red'],

        'submitted' => ['Отправлен', 'badge-blue'],
        'returned' => ['Возвращён', 'badge-amber'],

        'assigned' => ['Назначено', 'badge-grey'],
        'checked' => ['Проверено', 'badge-green'],

        'todo' => ['К выполнению', 'badge-grey'],
        'in_progress' => ['В работе', 'badge-amber'],

        'published' => ['Опубликовано', 'badge-green'],
        'left' => ['Выбыл', 'badge-grey'],
    ];
    return $map[$status] ?? [$status, 'badge-grey'];
}

function badge(?string $status): string
{
    [$label, $class] = status_meta($status);
    return '<span class="badge ' . $class . '">' . e($label) . '</span>';
}

function role_label(string $role): string
{
    return [
        'root' => 'Руководитель',
        'admin' => 'Администратор',
        'curator' => 'Куратор',
        'teacher' => 'Преподаватель',
        'smm' => 'SMM',
        'student' => 'Ученик',
    ][$role] ?? $role;
}

function role_icon(string $role): string
{
    return [
        'root' => '◆',
        'admin' => '▣',
        'curator' => '◎',
        'teacher' => '✎',
        'smm' => '✷',
        'student' => '●',
    ][$role] ?? '•';
}

/**
 * Сохранение загруженного изображения. Возвращает относительный путь.
 */
function upload_image(array $file, string $folder = 'reports'): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Не удалось загрузить файл.');
    }
    if ($file['size'] > (int)config('uploads.max_size')) {
        throw new RuntimeException('Файл больше 5 МБ.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = (array)config('uploads.types');
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Поддерживаются только JPG, PNG и WebP.');
    }
    $dir = BASE_PATH . '/uploads/' . $folder;
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('Не удалось создать папку для файлов.');
    }
    $name = date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException('Не удалось сохранить файл.');
    }
    return '/uploads/' . $folder . '/' . $name;
}

function applications_schedule_ready(): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }
    try {
        $ready = (bool)Database::value("SHOW COLUMNS FROM applications LIKE 'schedule_days'", [], 0);
    } catch (Throwable) {
        $ready = false;
    }
    return $ready;
}

function drive_id_from_url(string $url): string
{
    if (preg_match('#/folders/([A-Za-z0-9_-]{10,})#', $url, $m)) {
        return $m[1];
    }
    if (preg_match('#/d/([A-Za-z0-9_-]{10,})#', $url, $m)) {
        return $m[1];
    }
    if (preg_match('#[?&]id=([A-Za-z0-9_-]{10,})#', $url, $m)) {
        return $m[1];
    }
    return '';
}

function drive_embed_url(array $resource): string
{
    $id = (string)($resource['drive_id'] ?? '');
    if ($id === '') {
        return '';
    }
    return $resource['kind'] === 'folder'
        ? 'https://drive.google.com/embeddedfolderview?id=' . rawurlencode($id) . '#list'
        : 'https://drive.google.com/file/d/' . rawurlencode($id) . '/preview';
}

function upload_document(array $file): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Не удалось загрузить файл.');
    }
    if ($file['size'] > 15 * 1024 * 1024) {
        throw new RuntimeException('Файл больше 15 МБ.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'text/plain' => 'txt',
    ];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Поддерживаются PDF, DOC, DOCX и TXT.');
    }
    $dir = BASE_PATH . '/uploads/docs';
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('Не удалось создать папку для документов.');
    }
    $name = date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException('Не удалось сохранить файл.');
    }
    return '/uploads/docs/' . $name;
}

function table_exists(string $table): bool
{
    static $cache = [];
    if (array_key_exists($table, $cache)) {
        return $cache[$table];
    }
    try {
        $cache[$table] = (bool)Database::value('SHOW TABLES LIKE ?', [$table], 0);
    } catch (Throwable) {
        $cache[$table] = false;
    }
    return $cache[$table];
}

function materials_ready(): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }
    try {
        $ready = (bool)Database::value("SHOW TABLES LIKE 'materials'", [], 0);
    } catch (Throwable) {
        $ready = false;
    }
    return $ready;
}

function csrf_check_or_fail(): void
{
    if (!Csrf::check($_POST['_token'] ?? null)) {
        http_response_code(419);
        View::render('errors/419', ['title' => 'Сессия истекла'], Auth::check() ? 'app' : 'public');
        exit;
    }
}
