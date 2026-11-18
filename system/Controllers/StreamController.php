<?php
declare(strict_types=1);

final class StreamController extends Controller
{
    public function index(): void
    {
        $this->guardAuth();
        $user = $this->user();
        $role = $user['role'];
        $id = (int)$user['id'];

        $sql = 'SELECT s.*, u.first_name AS curator_first, u.last_name AS curator_last,
                       (SELECT COUNT(*) FROM study_groups g WHERE g.stream_id = s.id) AS groups_total,
                       (SELECT COUNT(DISTINCT gs.student_id) FROM group_students gs
                          JOIN study_groups g ON g.id = gs.group_id
                          WHERE g.stream_id = s.id AND gs.status = \'active\') AS students_total
                FROM streams s
                LEFT JOIN users u ON u.id = s.curator_id';

        $params = [];
        if ($role === 'curator') {
            $sql .= ' WHERE s.curator_id = ? OR EXISTS (SELECT 1 FROM study_groups g WHERE g.stream_id = s.id AND g.curator_id = ?)';
            $params = [$id, $id];
        } elseif ($role === 'teacher') {
            $sql .= ' WHERE EXISTS (SELECT 1 FROM study_groups g WHERE g.stream_id = s.id AND g.teacher_id = ?)';
            $params = [$id];
        } elseif ($role === 'student') {
            $sql .= ' WHERE EXISTS (SELECT 1 FROM group_students gs JOIN study_groups g ON g.id = gs.group_id
                                   WHERE g.stream_id = s.id AND gs.student_id = ? AND gs.status = \'active\')';
            $params = [$id];
        } elseif ($role === 'smm') {
            $this->redirect('/app');
        }

        $sql .= ' ORDER BY s.created_at DESC';

        $this->view('streams/index', [
            'title' => 'Потоки — ' . config('app.name'),
            'items' => Database::fetchAll($sql, $params),
            'canCreate' => in_array($role, ['root', 'admin'], true),
        ]);
    }

    public function create(): void
    {
        $this->guardRole(['root', 'admin']);
        $this->view('streams/create', [
            'title' => 'Новый поток — ' . config('app.name'),
            'curators' => $this->staff('curator'),
        ]);
    }

    public function store(): void
    {
        $this->guardRole(['root', 'admin']);
        $this->guardCsrf();

        $title = $this->input('title');
        if ($title === '') {
            $this->errors(['Укажите название потока.']);
        }

        $streamId = Database::insert('streams', [
            'title' => mb_substr($title, 0, 120),
            'language' => mb_substr($this->input('language', 'Английский'), 0, 40),
            'level' => mb_substr($this->input('level', 'A1–A2'), 0, 16),
            'starts_on' => $this->input('starts_on') ?: null,
            'ends_on' => $this->input('ends_on') ?: null,
            'lessons_total' => max(1, min(60, $this->intInput('lessons_total', 12))),
            'status' => in_array($this->input('status'), ['draft', 'recruiting', 'active', 'finished'], true) ? $this->input('status') : 'draft',
            'curator_id' => $this->intInput('curator_id') ?: null,
            'description' => $this->input('description') ?: null,
            'created_by' => (int)$this->user()['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        Activity::log('stream.created', 'streams', $streamId);
        $this->ok('Поток создан. Добавьте в него группы.');
        $this->redirect('/app/streams/' . $streamId);
    }

    public function show(string $id): void
    {
        $this->guardAuth();
        $streamId = (int)$id;
        $stream = Database::fetch(
            'SELECT s.*, u.first_name AS curator_first, u.last_name AS curator_last
             FROM streams s LEFT JOIN users u ON u.id = s.curator_id WHERE s.id = ?',
            [$streamId]
        );
        if ($stream === null) {
            (new ErrorController())->notFound();
            return;
        }

        $user = $this->user();
        $role = $user['role'];
        $allowed = in_array($role, ['root', 'admin'], true)
            || ($role === 'curator' && (
                (int)$stream['curator_id'] === (int)$user['id']
                || (int)Database::value('SELECT COUNT(*) FROM study_groups WHERE stream_id = ? AND curator_id = ?', [$streamId, (int)$user['id']], 0) > 0
            ))
            || ($role === 'teacher' && (int)Database::value(
                'SELECT COUNT(*) FROM study_groups WHERE stream_id = ? AND teacher_id = ?',
                [$streamId, (int)$user['id']],
                0
            ) > 0)
            || ($role === 'student' && (int)Database::value(
                'SELECT COUNT(*) FROM group_students gs JOIN study_groups g ON g.id = gs.group_id
                 WHERE g.stream_id = ? AND gs.student_id = ? AND gs.status = \'active\'',
                [$streamId, (int)$user['id']],
                0
            ) > 0);

        if (!$allowed) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Доступ закрыт'], 'app');
            return;
        }

        $groups = Database::fetchAll(
            'SELECT g.*, u.first_name AS teacher_first, u.last_name AS teacher_last,
                    c.first_name AS curator_first, c.last_name AS curator_last
             FROM study_groups g
             LEFT JOIN users u ON u.id = g.teacher_id
             LEFT JOIN users c ON c.id = g.curator_id
             WHERE g.stream_id = ? ORDER BY g.name',
            [$streamId]
        );

        $groupIds = array_map(static fn (array $g): int => (int)$g['id'], $groups);
        $in = $groupIds ? implode(',', array_fill(0, count($groupIds), '?')) : '0';

        $members = $groupIds ? Database::fetchAll(
            "SELECT gs.group_id, gs.status AS member_status, u.id, u.first_name, u.last_name, u.login, u.email,
                    p.percent, p.comment
             FROM group_students gs
             JOIN users u ON u.id = gs.student_id
             LEFT JOIN student_progress p ON p.student_id = u.id AND p.group_id = gs.group_id
             WHERE gs.group_id IN ($in) ORDER BY u.last_name, u.first_name",
            $groupIds
        ) : [];

        $membersByGroup = [];
        foreach ($members as $member) {
            $membersByGroup[(int)$member['group_id']][] = $member;
        }

        $availableStudents = Database::fetchAll(
            "SELECT u.id, u.first_name, u.last_name, u.login FROM users u
             WHERE u.role = 'student' AND u.status = 'active'
               AND NOT EXISTS (
                   SELECT 1 FROM group_students gs JOIN study_groups g ON g.id = gs.group_id
                   WHERE gs.student_id = u.id AND g.stream_id = ?
               )
             ORDER BY u.last_name LIMIT 200",
            [$streamId]
        );

        $this->view('streams/show', [
            'title' => $stream['title'] . ' — ' . config('app.name'),
            'stream' => $stream,
            'groups' => $groups,
            'membersByGroup' => $membersByGroup,
            'availableStudents' => $availableStudents,
            'teachers' => $this->staff('teacher'),
            'curators' => $this->staff('curator'),
            'canManage' => in_array($this->user()['role'], ['root', 'admin', 'curator'], true),
            'canEdit' => in_array($this->user()['role'], ['root', 'admin'], true),
        ]);
    }

    public function update(string $id): void
    {
        $this->guardRole(['root', 'admin', 'curator']);
        $this->guardCsrf();

        $status = $this->input('status');
        $data = [
            'status' => in_array($status, ['draft', 'recruiting', 'active', 'finished'], true) ? $status : 'draft',
        ];
        if (in_array($this->user()['role'], ['root', 'admin'], true)) {
            $data['title'] = mb_substr($this->input('title', 'Поток'), 0, 120);
            $data['starts_on'] = $this->input('starts_on') ?: null;
            $data['ends_on'] = $this->input('ends_on') ?: null;
            $data['curator_id'] = $this->intInput('curator_id') ?: null;
            $data['lessons_total'] = max(1, min(60, $this->intInput('lessons_total', 12)));
        }

        Database::update('streams', $data, 'id = ?', [(int)$id]);
        Activity::log('stream.updated', 'streams', (int)$id);
        $this->ok('Поток обновлён.');
        $this->redirect('/app/streams/' . (int)$id);
    }

    public function addGroup(string $id): void
    {
        $this->guardRole(['root', 'admin', 'curator']);
        $this->guardCsrf();

        $stream = Database::fetch('SELECT * FROM streams WHERE id = ?', [(int)$id]);
        if ($stream === null) {
            $this->fail('Поток не найден.');
        }

        $name = $this->input('name');
        if ($name === '') {
            $this->errors(['Укажите название группы.']);
        }

        $groupId = Database::insert('study_groups', [
            'stream_id' => (int)$id,
            'name' => mb_substr($name, 0, 80),
            'teacher_id' => $this->intInput('teacher_id') ?: null,
            'curator_id' => $this->intInput('curator_id') ?: ((int)$stream['curator_id'] ?: null),
            'capacity' => max(1, min(20, $this->intInput('capacity', 5))),
            'schedule_note' => $this->input('schedule_note') ?: null,
            'status' => 'forming',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->generateLessons($groupId, $stream);

        Activity::log('group.created', 'study_groups', $groupId);
        $this->ok('Группа создана, занятия добавлены в расписание.');
        $this->redirect('/app/streams/' . (int)$id);
    }

    public function assignTeacher(string $id): void
    {
        $this->guardRole(['root', 'admin', 'curator']);
        $this->guardCsrf();

        Database::update('study_groups', [
            'teacher_id' => $this->intInput('teacher_id') ?: null,
        ], 'id = ?', [(int)$id]);

        Activity::log('group.teacher', 'study_groups', (int)$id);
        $this->ok('Преподаватель назначен.');
        back();
    }

    public function addStudent(string $id): void
    {
        $this->guardRole(['root', 'admin', 'curator']);
        $this->guardCsrf();

        $groupId = (int)$id;
        $studentId = $this->intInput('student_id');
        $student = Database::fetch("SELECT * FROM users WHERE id = ? AND role = 'student'", [$studentId]);
        if ($student === null) {
            $this->fail('Ученик не найден.');
        }

        $group = Database::fetch('SELECT * FROM study_groups WHERE id = ?', [$groupId]);
        if ($group === null) {
            $this->fail('Группа не найдена.');
        }

        $count = (int)Database::value("SELECT COUNT(*) FROM group_students WHERE group_id = ? AND status = 'active'", [$groupId], 0);
        if ($count >= (int)$group['capacity']) {
            $this->fail('В группе уже максимальное число учеников.');
        }

        Database::q(
            'INSERT INTO group_students (group_id, student_id, status, joined_at) VALUES (?, ?, \'active\', NOW())
             ON DUPLICATE KEY UPDATE status = \'active\'',
            [$groupId, $studentId]
        );

        Activity::log('group.student_added', 'study_groups', $groupId, (string)$studentId);
        $this->ok('Ученик добавлен в группу.');
        back();
    }

    public function removeStudent(string $id, string $studentId): void
    {
        $this->guardRole(['root', 'admin', 'curator']);
        $this->guardCsrf();

        Database::q('DELETE FROM group_students WHERE group_id = ? AND student_id = ?', [(int)$id, (int)$studentId]);
        Activity::log('group.student_removed', 'study_groups', (int)$id, $studentId);
        $this->ok('Ученик убран из группы.');
        back();
    }

    public function setProgress(string $id): void
    {
        $this->guardRole(['root', 'admin', 'curator', 'teacher']);
        $this->guardCsrf();

        $groupId = (int)$id;
        $studentId = $this->intInput('student_id');
        $percent = max(0, min(100, $this->intInput('percent')));
        $comment = $this->input('comment');

        if ($this->user()['role'] === 'teacher') {
            $owns = Database::value('SELECT COUNT(*) FROM study_groups WHERE id = ? AND teacher_id = ?', [$groupId, (int)$this->user()['id']], 0);
            if ((int)$owns === 0) {
                $this->fail('Это не ваша группа.');
            }
        }

        Database::q(
            'INSERT INTO student_progress (student_id, group_id, percent, comment, updated_by, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE percent = VALUES(percent), comment = VALUES(comment),
                                     updated_by = VALUES(updated_by), updated_at = VALUES(updated_at)',
            [$studentId, $groupId, $percent, $comment !== '' ? mb_substr($comment, 0, 255) : null, (int)$this->user()['id']]
        );

        Activity::log('progress.updated', 'student_progress', $studentId, (string)$percent);
        $this->ok('Прогресс обновлён.');
        back();
    }

    /** @return array<int, array> */
    private function staff(string $role): array
    {
        return Database::fetchAll(
            'SELECT id, first_name, last_name FROM users WHERE role = ? AND status = \'active\' ORDER BY last_name',
            [$role]
        );
    }

    private function generateLessons(int $groupId, array $stream): void
    {
        $start = $stream['starts_on'] ? strtotime((string)$stream['starts_on']) : strtotime('next monday');
        $total = max(1, (int)$stream['lessons_total']);

        for ($i = 1; $i <= $total; $i++) {
            $scheduled = date('Y-m-d H:i:s', strtotime('+' . ($i - 1) . ' weeks', $start) + 18 * 3600);
            Database::insert('lessons', [
                'group_id' => $groupId,
                'lesson_no' => $i,
                'topic' => 'Занятие ' . $i,
                'scheduled_at' => $scheduled,
                'duration_min' => 60,
                'status' => 'planned',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
