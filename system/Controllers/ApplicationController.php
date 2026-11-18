<?php
declare(strict_types=1);

final class ApplicationController extends Controller
{
    private const STATUSES = ['new', 'in_review', 'approved', 'rejected', 'waitlist'];

    public function index(): void
    {
        $this->guardRole(['root', 'admin', 'curator']);
        $role = $this->user()['role'];

        $type = (string)($_GET['type'] ?? '');
        $status = (string)($_GET['status'] ?? '');
        $q = trim((string)($_GET['q'] ?? ''));

        $where = [];
        $params = [];

        if ($role === 'curator') {
            $where[] = "type IN ('student','teacher')";
        }
        if (in_array($type, ['student', 'teacher', 'curator', 'smm'], true)) {
            $where[] = 'type = ?';
            $params[] = $type;
        }
        if (in_array($status, self::STATUSES, true)) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        if ($q !== '') {
            $where[] = '(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)';
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like);
        }

        $sql = 'SELECT * FROM applications';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY FIELD(status, \'new\', \'in_review\', \'waitlist\', \'approved\', \'rejected\'), created_at DESC LIMIT 100';

        $items = Database::fetchAll($sql, $params);

        $counts = [];
        foreach (Database::fetchAll('SELECT status, COUNT(*) AS c FROM applications GROUP BY status') as $row) {
            $counts[$row['status']] = (int)$row['c'];
        }

        $this->view('applications/index', [
            'title' => 'Заявки — ' . config('app.name'),
            'items' => $items,
            'counts' => $counts,
            'filters' => ['type' => $type, 'status' => $status, 'q' => $q],
        ]);
    }

    public function show(string $id): void
    {
        $this->guardRole(['root', 'admin', 'curator']);
        $application = $this->find((int)$id);
        $this->view('applications/show', [
            'title' => 'Заявка — ' . config('app.name'),
            'item' => $application,
        ]);
    }

    public function updateStatus(string $id): void
    {
        $this->guardRole(['root', 'admin', 'curator']);
        $this->guardCsrf();

        $application = $this->find((int)$id);
        $status = $this->input('status');
        $comment = $this->input('comment');

        if (!in_array($status, self::STATUSES, true)) {
            $this->fail('Неизвестный статус.');
        }

        if ($this->user()['role'] === 'curator' && !in_array($application['type'], ['student', 'teacher'], true)) {
            $this->fail('Куратор обрабатывает только заявки учеников и преподавателей.');
        }

        Database::update('applications', [
            'status' => $status,
            'reviewed_by' => (int)$this->user()['id'],
            'reviewed_at' => date('Y-m-d H:i:s'),
            'review_comment' => $comment !== '' ? mb_substr($comment, 0, 255) : null,
        ], 'id = ?', [(int)$id]);

        Activity::log('application.status', 'applications', (int)$id, $status);
        $this->ok('Статус заявки обновлён.');
        $this->redirect('/app/applications/' . (int)$id);
    }

    public function createAccount(string $id): void
    {
        $this->guardRole(['root', 'admin', 'curator']);
        $this->guardCsrf();

        $application = $this->find((int)$id);
        if ($this->user()['role'] === 'curator' && $application['type'] !== 'student') {
            $this->fail('Куратор создаёт аккаунты только ученикам.');
        }
        if ($application['user_id']) {
            $this->fail('Для этой заявки аккаунт уже создан.');
        }

        $this->redirect('/app/users/create?application=' . (int)$id);
    }

    private function find(int $id): array
    {
        $application = Database::fetch('SELECT * FROM applications WHERE id = ?', [$id]);
        if ($application === null) {
            (new ErrorController())->notFound();
            exit;
        }
        if ($this->user()['role'] === 'curator' && !in_array($application['type'], ['student', 'teacher'], true)) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Доступ закрыт'], 'app');
            exit;
        }
        return $application;
    }
}
