<?php
declare(strict_types=1);

final class HomeworkController extends Controller
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

        $sql = 'SELECT h.*, g.name AS group_name, g.teacher_id, s.title AS stream_title,
                       (SELECT COUNT(*) FROM homework_submissions hs WHERE hs.homework_id = h.id AND hs.status = \'submitted\') AS submitted_total,
                       (SELECT COUNT(*) FROM group_students gs WHERE gs.group_id = h.group_id AND gs.status = \'active\') AS students_total,
                       (SELECT hs.status FROM homework_submissions hs WHERE hs.homework_id = h.id AND hs.student_id = ?) AS my_status
                FROM homework h
                JOIN study_groups g ON g.id = h.group_id
                JOIN streams s ON s.id = g.stream_id';
        $params = [$id];
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

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY h.created_at DESC LIMIT 100';

        $this->view('homework/index', [
            'title' => 'Домашние задания — ' . config('app.name'),
            'items' => Database::fetchAll($sql, $params),
            'canCreate' => in_array($role, ['root', 'admin', 'curator', 'teacher'], true),
        ]);
    }

    public function create(): void
    {
        $this->guardRole(['root', 'admin', 'curator', 'teacher']);
        $this->view('homework/create', [
            'title' => 'Новое задание — ' . config('app.name'),
            'groups' => $this->groupsForUser(),
        ]);
    }

    public function store(): void
    {
        $this->guardRole(['root', 'admin', 'curator', 'teacher']);
        $this->guardCsrf();

        $groupId = $this->intInput('group_id');
        if (!$this->canUseGroup($groupId)) {
            $this->fail('Выберите свою группу.');
        }

        $title = $this->input('title');
        $description = $this->input('description');
        if ($title === '') {
            $this->errors(['Укажите название задания.']);
        }

        $due = $this->input('due_at');
        if ($due !== '') {
            $due = str_replace('T', ' ', $due);
            if (strlen($due) === 16) {
                $due .= ':00';
            }
        }

        $homeworkId = Database::insert('homework', [
            'group_id' => $groupId,
            'title' => mb_substr($title, 0, 190),
            'description' => $description ?: null,
            'due_at' => $due !== '' ? $due : null,
            'created_by' => (int)$this->user()['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        Activity::log('homework.created', 'homework', $homeworkId);
        $this->ok('Задание опубликовано для группы.');
        $this->redirect('/app/homework/' . $homeworkId);
    }

    public function show(string $id): void
    {
        $this->guardAuth();
        $homework = Database::fetch(
            'SELECT h.*, g.name AS group_name, g.teacher_id, g.capacity, s.title AS stream_title
             FROM homework h
             JOIN study_groups g ON g.id = h.group_id
             JOIN streams s ON s.id = g.stream_id
             WHERE h.id = ?',
            [(int)$id]
        );
        if ($homework === null) {
            (new ErrorController())->notFound();
            return;
        }

        $user = $this->user();
        $role = $user['role'];
        $groupId = (int)$homework['group_id'];

        $isMember = (int)Database::value(
            'SELECT COUNT(*) FROM group_students WHERE group_id = ? AND student_id = ? AND status = \'active\'',
            [$groupId, (int)$user['id']],
            0
        ) > 0;

        $allowed = in_array($role, ['root', 'admin', 'curator'], true)
            || ($role === 'teacher' && (int)$homework['teacher_id'] === (int)$user['id'])
            || ($role === 'student' && $isMember);

        if (!$allowed) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Доступ закрыт'], 'app');
            return;
        }

        $submissions = [];
        if ($role !== 'student') {
            $submissions = Database::fetchAll(
                'SELECT u.id AS student_id, u.first_name, u.last_name, u.login,
                        hs.id AS submission_id, hs.status, hs.grade, hs.comment, hs.answer, hs.submitted_at
                 FROM group_students gs
                 JOIN users u ON u.id = gs.student_id
                 LEFT JOIN homework_submissions hs ON hs.homework_id = ? AND hs.student_id = u.id
                 WHERE gs.group_id = ? AND gs.status = \'active\'
                 ORDER BY u.last_name',
                [(int)$id, $groupId]
            );
        }

        $mySubmission = $role === 'student'
            ? Database::fetch('SELECT * FROM homework_submissions WHERE homework_id = ? AND student_id = ?', [(int)$id, (int)$user['id']])
            : null;

        $this->view('homework/show', [
            'title' => $homework['title'] . ' — ' . config('app.name'),
            'homework' => $homework,
            'submissions' => $submissions,
            'mySubmission' => $mySubmission,
            'isStudent' => $role === 'student',
        ]);
    }

    public function submit(string $id): void
    {
        $this->guardRole(['student']);
        $this->guardCsrf();

        $homework = Database::fetch('SELECT * FROM homework WHERE id = ?', [(int)$id]);
        if ($homework === null) {
            $this->fail('Задание не найдено.');
        }

        $isMember = (int)Database::value(
            'SELECT COUNT(*) FROM group_students WHERE group_id = ? AND student_id = ? AND status = \'active\'',
            [(int)$homework['group_id'], (int)$this->user()['id']],
            0
        );
        if ($isMember === 0) {
            $this->fail('Вы не в этой группе.');
        }

        $answer = $this->input('answer');
        if ($answer === '') {
            $this->errors(['Напишите ответ или вставьте ссылку.']);
        }

        Database::q(
            'INSERT INTO homework_submissions (homework_id, student_id, answer, status, submitted_at)
             VALUES (?, ?, ?, \'submitted\', NOW())
             ON DUPLICATE KEY UPDATE answer = VALUES(answer), status = \'submitted\', submitted_at = NOW()',
            [(int)$id, (int)$this->user()['id'], $answer]
        );

        Activity::log('homework.submitted', 'homework', (int)$id);
        $this->ok('Работа отправлена преподавателю.');
        $this->redirect('/app/homework/' . (int)$id);
    }

    public function check(string $id): void
    {
        $this->guardRole(['root', 'admin', 'teacher', 'curator']);
        $this->guardCsrf();

        $homework = Database::fetch(
            'SELECT h.*, g.teacher_id FROM homework h JOIN study_groups g ON g.id = h.group_id WHERE h.id = ?',
            [(int)$id]
        );
        if ($homework === null) {
            $this->fail('Задание не найдено.');
        }

        $studentId = $this->intInput('student_id');
        $grade = max(0, min(100, $this->intInput('grade')));
        $comment = $this->input('comment');

        Database::q(
            'INSERT INTO homework_submissions (homework_id, student_id, status, grade, comment, checked_by, checked_at)
             VALUES (?, ?, \'checked\', ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE status = \'checked\', grade = VALUES(grade), comment = VALUES(comment),
                                     checked_by = VALUES(checked_by), checked_at = NOW()',
            [(int)$id, $studentId, $grade, $comment !== '' ? mb_substr($comment, 0, 255) : null, (int)$this->user()['id']]
        );

        Activity::log('homework.checked', 'homework', (int)$id, (string)$studentId);
        $this->ok('Оценка выставлена.');
        $this->redirect('/app/homework/' . (int)$id);
    }

    /** @return array<int, array> */
    private function groupsForUser(): array
    {
        $user = $this->user();
        $sql = 'SELECT g.id, g.name, s.title AS stream_title FROM study_groups g
                JOIN streams s ON s.id = g.stream_id';
        $params = [];

        if ($user['role'] === 'teacher') {
            $sql .= ' WHERE g.teacher_id = ?';
            $params[] = (int)$user['id'];
        } elseif ($user['role'] === 'curator') {
            $sql .= ' WHERE g.curator_id = ? OR s.curator_id = ?';
            $params[] = (int)$user['id'];
            $params[] = (int)$user['id'];
        }

        return Database::fetchAll($sql . ' ORDER BY s.title, g.name', $params);
    }

    private function canUseGroup(int $groupId): bool
    {
        $user = $this->user();
        if (in_array($user['role'], ['root', 'admin'], true)) {
            return true;
        }
        if ($user['role'] === 'teacher') {
            return (int)Database::value('SELECT COUNT(*) FROM study_groups WHERE id = ? AND teacher_id = ?', [$groupId, (int)$user['id']], 0) > 0;
        }
        if ($user['role'] === 'curator') {
            return (int)Database::value(
                'SELECT COUNT(*) FROM study_groups g JOIN streams s ON s.id = g.stream_id
                 WHERE g.id = ? AND (g.curator_id = ? OR s.curator_id = ?)',
                [$groupId, (int)$user['id'], (int)$user['id']],
                0
            ) > 0;
        }
        return false;
    }
}
