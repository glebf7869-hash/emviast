<?php
declare(strict_types=1);

final class PublicController extends Controller
{
    private const TYPES = ['student', 'teacher', 'curator', 'smm'];

    public function home(): void
    {
        $news = Database::fetchAll(
            "SELECT slug, title, title_en, excerpt, excerpt_en, cover, published_at FROM news
             WHERE status = 'published' ORDER BY published_at DESC LIMIT 3"
        );

        $stats = [
            'students' => (int)Database::value("SELECT COUNT(*) FROM users WHERE role = 'student' AND status = 'active'", [], 0),
            'teachers' => (int)Database::value("SELECT COUNT(*) FROM users WHERE role = 'teacher' AND status = 'active'", [], 0),
            'groups' => (int)Database::value("SELECT COUNT(*) FROM study_groups WHERE status IN ('active','forming')", [], 0),
            'streams' => (int)Database::value("SELECT COUNT(*) FROM streams WHERE status IN ('active','recruiting')", [], 0),
        ];

        $this->view('public/home', [
            'title' => t('page.home'),
            'news' => $news,
            'stats' => $stats,
        ], 'public');
    }

    public function about(): void
    {
        $this->view('public/about', ['title' => t('page.about')], 'public');
    }

    public function rules(): void
    {
        $documents = table_exists('documents')
            ? Database::fetchAll('SELECT * FROM documents ORDER BY sort_order, id')
            : [];
        $this->view('public/rules', [
            'title' => t('page.rules'),
            'documents' => $documents,
        ], 'public');
    }

    public function newsIndex(): void
    {
        $news = Database::fetchAll(
            "SELECT slug, title, title_en, excerpt, excerpt_en, cover, published_at FROM news
             WHERE status = 'published' ORDER BY published_at DESC LIMIT 30"
        );
        $this->view('public/news', ['title' => t('page.news'), 'news' => $news], 'public');
    }

    public function newsShow(string $slug): void
    {
        $item = Database::fetch(
            "SELECT n.*, u.first_name, u.last_name FROM news n
             LEFT JOIN users u ON u.id = n.author_id
             WHERE n.slug = ? AND n.status = 'published' LIMIT 1",
            [$slug]
        );
        if ($item === null) {
            (new ErrorController())->notFound();
            return;
        }
        $this->view('public/news-show', ['title' => news_title($item) . ' — EMVIA Study', 'item' => $item], 'public');
    }

    public function applyForm(): void
    {
        $type = (string)($_GET['type'] ?? 'student');
        if (!in_array($type, self::TYPES, true)) {
            $type = 'student';
        }
        $this->view('public/apply', [
            'title' => t('page.apply'),
            'type' => $type,
            'sent' => isset($_GET['ok']),
        ], 'public');
    }

    public function apply(): void
    {
        $this->guardCsrf();

        $type = $this->input('type');
        $firstName = $this->input('first_name');
        $lastName = $this->input('last_name');
        $email = $this->input('email');
        $phone = $this->input('phone');
        $timezone = $this->input('timezone');
        $goal = $this->input('goal');
        $experience = $this->input('experience');
        $message = $this->input('message');
        $level = $this->input('level');
        $age = (int)($_POST['age'] ?? 0);
        $hours = (int)($_POST['hours'] ?? 0);
        $groupsReady = (int)($_POST['groups_ready'] ?? 0);

        $daysRaw = $_POST['schedule_days'] ?? '';
        $timeRaw = $_POST['schedule_time'] ?? '';

        $scheduleDays = null;
        $scheduleTime = null;

        if ($type === 'student') {
            $dayPresets = student_day_presets();
            $slotLabels = time_slot_labels();
            $dayValue = is_string($daysRaw) ? $daysRaw : '';
            $timeValue = is_string($timeRaw) ? $timeRaw : '';
            $scheduleDays = isset($dayPresets[$dayValue]) ? $dayValue : null;
            $scheduleTime = isset($slotLabels[$timeValue]) ? $timeValue : null;
        } elseif ($type === 'teacher') {
            $validDays = array_keys(day_labels());
            $validSlots = array_keys(time_slot_labels());
            $daysList = array_values(array_unique(array_intersect((array)$daysRaw, $validDays)));
            $slotsList = array_values(array_unique(array_intersect((array)$timeRaw, $validSlots)));
            $scheduleDays = $daysList ? implode(',', $daysList) : null;
            $scheduleTime = $slotsList ? implode(',', $slotsList) : null;
        }

        $errors = [];
        if (!in_array($type, self::TYPES, true)) {
            $errors[] = 'Выберите тип заявки.';
        }
        if ($firstName === '' || $lastName === '') {
            $errors[] = 'Укажите имя и фамилию.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Укажите корректный e-mail.';
        }
        if ($type === 'student' && ($age < 14 || $age > 17)) {
            $errors[] = 'Занятия рассчитаны на подростков 14–17 лет.';
        }
        if ($type === 'student' && ($scheduleDays === null || $scheduleTime === null)) {
            $errors[] = 'Выберите удобные дни и время занятий.';
        }
        if ($type === 'teacher') {
            if ($scheduleDays === null || $scheduleTime === null) {
                $errors[] = 'Отметьте дни и время, когда вам удобно вести занятия.';
            }
            if ($groupsReady < 1 || $groupsReady > 5) {
                $errors[] = 'Укажите, сколько групп вы готовы взять (от 1 до 5).';
            }
        }
        if ($errors) {
            $this->errors($errors);
        }

        $data = [
            'type' => $type,
            'first_name' => mb_substr($firstName, 0, 80),
            'last_name' => mb_substr($lastName, 0, 80),
            'email' => mb_substr($email, 0, 190),
            'phone' => $phone !== '' ? mb_substr($phone, 0, 32) : null,
            'age' => $type === 'student' ? $age : null,
            'timezone' => $timezone !== '' ? mb_substr($timezone, 0, 64) : null,
            'level' => in_array($level, ['A1', 'A2', 'B1', 'B2', 'C1'], true) ? $level : null,
            'goal' => $goal !== '' ? $goal : null,
            'experience' => $experience !== '' ? $experience : null,
            'hours' => $hours > 0 ? min($hours, 60) : null,
            'groups_ready' => $type === 'teacher' ? max(1, min(5, $groupsReady)) : null,
            'schedule_days' => $scheduleDays,
            'schedule_time' => $scheduleTime,
            'message' => $message !== '' ? $message : null,
            'source' => 'site',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if (!applications_schedule_ready()) {
            unset($data['groups_ready'], $data['schedule_days'], $data['schedule_time']);
        }

        Database::insert('applications', $data);

        Activity::log('application.created', 'applications', 0, $type . ': ' . $email);
        $this->redirect('/apply?ok=1&type=' . urlencode($type));
    }
}
