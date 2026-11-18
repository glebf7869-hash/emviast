<?php
declare(strict_types=1);

final class ReportController extends Controller
{
    public function index(): void
    {
        $this->guardAuth();
        $user = $this->user();
        $role = $user['role'];
        $id = (int)$user['id'];

        $sql = 'SELECT r.*, l.topic, l.scheduled_at, l.lesson_no, g.name AS group_name, s.title AS stream_title,
                       u.first_name AS teacher_first, u.last_name AS teacher_last
                FROM lesson_reports r
                JOIN lessons l ON l.id = r.lesson_id
                JOIN study_groups g ON g.id = l.group_id
                JOIN streams s ON s.id = g.stream_id
                LEFT JOIN users u ON u.id = r.teacher_id';
        $params = [];
        $where = [];

        if ($role === 'teacher') {
            $where[] = 'r.teacher_id = ?';
            $params[] = $id;
        } elseif ($role === 'curator') {
            $where[] = '(s.curator_id = ? OR g.curator_id = ?)';
            $params[] = $id;
            $params[] = $id;
        } elseif ($role === 'student') {
            $this->redirect('/app');
        }

        $groupId = (int)($_GET['group_id'] ?? 0);
        if ($groupId > 0) {
            $where[] = 'g.id = ?';
            $params[] = $groupId;
        }

        $filterStatus = (string)($_GET['status'] ?? '');
        if (in_array($filterStatus, ['draft', 'submitted', 'approved', 'returned'], true)) {
            $where[] = 'r.status = ?';
            $params[] = $filterStatus;
        }

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY l.scheduled_at DESC LIMIT 200';

        $this->view('reports/index', [
            'title' => 'Отчёты — ' . config('app.name'),
            'items' => Database::fetchAll($sql, $params),
            'canReview' => in_array($role, ['root', 'admin', 'curator'], true),
            'groups' => $this->filterGroups(),
            'filters' => ['status' => $filterStatus, 'group_id' => $groupId],
        ]);
    }

    /** @return array<int, array> */
    private function filterGroups(): array
    {
        $user = $this->user();
        $role = $user['role'];
        $id = (int)$user['id'];

        $sql = 'SELECT g.id, g.name, s.title AS stream_title FROM study_groups g JOIN streams s ON s.id = g.stream_id';
        $params = [];

        if (in_array($role, ['root', 'admin'], true)) {
            // все группы
        } elseif ($role === 'curator') {
            $sql .= ' WHERE g.curator_id = ? OR s.curator_id = ?';
            $params = [$id, $id];
        } elseif ($role === 'teacher') {
            $sql .= ' WHERE g.teacher_id = ?';
            $params = [$id];
        } else {
            return [];
        }

        return Database::fetchAll($sql . ' ORDER BY s.title, g.name', $params);
    }

    public function show(string $id): void
    {
        $this->guardAuth();
        $report = Database::fetch(
            'SELECT r.*, l.topic, l.scheduled_at, l.lesson_no, l.meeting_url, g.name AS group_name, g.teacher_id,
                    s.title AS stream_title, u.first_name AS teacher_first, u.last_name AS teacher_last,
                    rv.first_name AS reviewer_first, rv.last_name AS reviewer_last
             FROM lesson_reports r
             JOIN lessons l ON l.id = r.lesson_id
             JOIN study_groups g ON g.id = l.group_id
             JOIN streams s ON s.id = g.stream_id
             LEFT JOIN users u ON u.id = r.teacher_id
             LEFT JOIN users rv ON rv.id = r.reviewed_by
             WHERE r.id = ?',
            [(int)$id]
        );
        if ($report === null) {
            (new ErrorController())->notFound();
            return;
        }

        $user = $this->user();
        $allowed = in_array($user['role'], ['root', 'admin', 'curator'], true)
            || ($user['role'] === 'teacher' && (int)$report['teacher_id'] === (int)$user['id']);
        if (!$allowed) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Доступ закрыт'], 'app');
            return;
        }

        $this->view('reports/show', [
            'title' => 'Отчёт — ' . config('app.name'),
            'report' => $report,
            'canReview' => in_array($user['role'], ['root', 'admin', 'curator'], true),
        ]);
    }

    public function save(string $lessonId): void
    {
        $this->guardAuth();
        $this->guardCsrf();

        $lesson = Database::fetch(
            'SELECT l.*, g.teacher_id FROM lessons l JOIN study_groups g ON g.id = l.group_id WHERE l.id = ?',
            [(int)$lessonId]
        );
        if ($lesson === null) {
            $this->fail('Занятие не найдено.');
        }

        $user = $this->user();
        $canManage = in_array($user['role'], ['root', 'admin'], true)
            || ($user['role'] === 'teacher' && (int)$lesson['teacher_id'] === (int)$user['id']);
        if (!$canManage) {
            $this->fail('Отчёт может отправить только преподаватель группы.');
        }

        $action = $this->input('action', 'draft');
        $status = $action === 'submit' ? 'submitted' : 'draft';

        $existing = Database::fetch('SELECT * FROM lesson_reports WHERE lesson_id = ?', [(int)$lessonId]);

        $data = [
            'summary' => $this->input('summary') ?: null,
            'homework_text' => $this->input('homework_text') ?: null,
            'attendance_present' => max(0, min(50, $this->intInput('attendance_present'))),
            'attendance_absent' => max(0, min(50, $this->intInput('attendance_absent'))),
            'issues' => $this->input('issues') ?: null,
            'status' => $status,
            'teacher_id' => (int)($lesson['teacher_id'] ?: $user['id']),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        try {
            if (!empty($_FILES['photo_start']['name'])) {
                $data['photo_start'] = upload_image($_FILES['photo_start'], 'reports');
                $data['photo_start_at'] = date('Y-m-d H:i:s');
            }
            if (!empty($_FILES['photo_end']['name'])) {
                $data['photo_end'] = upload_image($_FILES['photo_end'], 'reports');
                $data['photo_end_at'] = date('Y-m-d H:i:s');
            }
        } catch (RuntimeException $e) {
            $this->fail($e->getMessage());
        }

        $hasStartPhoto = !empty($data['photo_start']) || !empty($existing['photo_start']);
        $hasEndPhoto = !empty($data['photo_end']) || !empty($existing['photo_end']);

        if ($status === 'submitted') {
            if (!$hasStartPhoto || !$hasEndPhoto) {
                $this->fail('Чтобы отправить отчёт, прикрепите оба фото: начало и конец урока.');
            }
            $data['submitted_at'] = date('Y-m-d H:i:s');
        }

        if ($existing) {
            Database::update('lesson_reports', $data, 'id = ?', [(int)$existing['id']]);
            $reportId = (int)$existing['id'];
        } else {
            $data['lesson_id'] = (int)$lessonId;
            $data['created_at'] = date('Y-m-d H:i:s');
            $reportId = Database::insert('lesson_reports', $data);
        }

        if ($status === 'submitted') {
            Database::update('lessons', ['status' => 'done'], 'id = ?', [(int)$lessonId]);
        }

        Activity::log('report.saved', 'lesson_reports', $reportId, $status);
        $this->ok($status === 'submitted' ? 'Отчёт отправлен куратору.' : 'Черновик сохранён.');
        $this->redirect('/app/lessons/' . (int)$lessonId);
    }

    public function review(string $id): void
    {
        $this->guardRole(['root', 'admin', 'curator']);
        $this->guardCsrf();

        $report = Database::fetch('SELECT * FROM lesson_reports WHERE id = ?', [(int)$id]);
        if ($report === null) {
            $this->fail('Отчёт не найден.');
        }

        $decision = $this->input('decision');
        if (!in_array($decision, ['approved', 'returned'], true)) {
            $this->fail('Выберите решение.');
        }

        Database::update('lesson_reports', [
            'status' => $decision,
            'reviewed_by' => (int)$this->user()['id'],
            'reviewed_at' => date('Y-m-d H:i:s'),
            'review_comment' => $this->input('comment') ?: null,
        ], 'id = ?', [(int)$id]);

        Activity::log('report.reviewed', 'lesson_reports', (int)$id, $decision);
        $this->ok($decision === 'approved' ? 'Отчёт принят.' : 'Отчёт возвращён на доработку.');
        $this->redirect('/app/reports/' . (int)$id);
    }
}
