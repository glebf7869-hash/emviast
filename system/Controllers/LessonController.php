<?php
declare(strict_types=1);

final class LessonController extends Controller
{
    public function index(): void
    {
        $this->guardAuth();
        $user = $this->user();
        $role = $user['role'];
        $id = (int)$user['id'];

        if ($role === 'smm') {
            $this->redirect('/app');
        }

        $sql = 'SELECT l.*, g.name AS group_name, g.teacher_id, s.title AS stream_title,
                       u.first_name AS teacher_first, u.last_name AS teacher_last,
                       r.id AS report_id, r.status AS report_status
                FROM lessons l
                JOIN study_groups g ON g.id = l.group_id
                JOIN streams s ON s.id = g.stream_id
                LEFT JOIN users u ON u.id = g.teacher_id
                LEFT JOIN lesson_reports r ON r.lesson_id = l.id';
        $params = [];
        $where = [];

        if ($role === 'teacher') {
            $where[] = 'g.teacher_id = ?';
            $params[] = $id;
        } elseif ($role === 'curator') {
            $where[] = '(s.curator_id = ? OR g.curator_id = ?)';
            $params[] = $id;
            $params[] = $id;
        } elseif ($role === 'student') {
            $where[] = 'EXISTS (SELECT 1 FROM group_students gs WHERE gs.group_id = g.id AND gs.student_id = ? AND gs.status = \'active\')';
            $params[] = $id;
        }

        $groupId = (int)($_GET['group_id'] ?? 0);
        if ($groupId > 0) {
            $where[] = 'l.group_id = ?';
            $params[] = $groupId;
        }

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY l.scheduled_at ASC LIMIT 200';

        $this->view('streams/schedule', [
            'title' => 'Расписание — ' . config('app.name'),
            'items' => Database::fetchAll($sql, $params),
            'groups' => $this->filterGroups(),
            'filters' => ['group_id' => $groupId],
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
        $lesson = Database::fetch(
            'SELECT l.*, g.name AS group_name, g.teacher_id, g.curator_id, g.capacity,
                    s.title AS stream_title, s.id AS stream_id,
                    u.first_name AS teacher_first, u.last_name AS teacher_last
             FROM lessons l
             JOIN study_groups g ON g.id = l.group_id
             JOIN streams s ON s.id = g.stream_id
             LEFT JOIN users u ON u.id = g.teacher_id
             WHERE l.id = ?',
            [(int)$id]
        );
        if ($lesson === null) {
            (new ErrorController())->notFound();
            return;
        }

        $user = $this->user();
        $role = $user['role'];
        $allowed = in_array($role, ['root', 'admin'], true)
            || ($role === 'teacher' && (int)$lesson['teacher_id'] === (int)$user['id'])
            || ($role === 'curator' && ((int)$lesson['curator_id'] === (int)$user['id'] || (int)Database::value('SELECT COUNT(*) FROM streams WHERE id = ? AND curator_id = ?', [(int)$lesson['stream_id'], (int)$user['id']], 0) > 0))
            || ($role === 'student' && (int)Database::value('SELECT COUNT(*) FROM group_students WHERE group_id = ? AND student_id = ? AND status = \'active\'', [(int)$lesson['group_id'], (int)$user['id']], 0) > 0);

        if (!$allowed) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Доступ закрыт'], 'app');
            return;
        }

        $report = Database::fetch('SELECT * FROM lesson_reports WHERE lesson_id = ?', [(int)$id]);
        $students = Database::fetchAll(
            'SELECT u.id, u.first_name, u.last_name, u.login FROM group_students gs
             JOIN users u ON u.id = gs.student_id
             WHERE gs.group_id = ? AND gs.status = \'active\' ORDER BY u.last_name',
            [(int)$lesson['group_id']]
        );

        $this->view('streams/lesson', [
            'title' => 'Занятие — ' . config('app.name'),
            'lesson' => $lesson,
            'report' => $report,
            'students' => $students,
            'canManage' => in_array($role, ['root', 'admin'], true) || ($role === 'teacher' && (int)$lesson['teacher_id'] === (int)$user['id']),
        ]);
    }

    public function update(string $id): void
    {
        $this->guardAuth();
        $this->guardCsrf();

        $lesson = Database::fetch(
            'SELECT l.*, g.teacher_id FROM lessons l JOIN study_groups g ON g.id = l.group_id WHERE l.id = ?',
            [(int)$id]
        );
        if ($lesson === null) {
            $this->fail('Занятие не найдено.');
        }

        $user = $this->user();
        $canManage = in_array($user['role'], ['root', 'admin'], true)
            || ($user['role'] === 'teacher' && (int)$lesson['teacher_id'] === (int)$user['id']);
        if (!$canManage) {
            $this->fail('Недостаточно прав.');
        }

        $scheduled = $this->input('scheduled_at');
        if ($scheduled !== '') {
            $scheduled = str_replace('T', ' ', $scheduled);
            if (strlen($scheduled) === 16) {
                $scheduled .= ':00';
            }
        }

        $status = $this->input('status', 'planned');

        Database::update('lessons', [
            'topic' => mb_substr($this->input('topic'), 0, 190),
            'scheduled_at' => $scheduled !== '' ? $scheduled : $lesson['scheduled_at'],
            'duration_min' => max(15, min(240, $this->intInput('duration_min', 60))),
            'meeting_url' => $this->input('meeting_url') ?: null,
            'status' => in_array($status, ['planned', 'done', 'canceled'], true) ? $status : 'planned',
        ], 'id = ?', [(int)$id]);

        Activity::log('lesson.updated', 'lessons', (int)$id);
        $this->ok('Занятие обновлено.');
        $this->redirect('/app/lessons/' . (int)$id);
    }
}
