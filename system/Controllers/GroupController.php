<?php
declare(strict_types=1);

final class GroupController extends Controller
{
    public function index(): void
    {
        $this->guardAuth();
        $user = $this->user();
        $role = $user['role'];
        $id = (int)$user['id'];

        $sql = 'SELECT g.*, s.title AS stream_title,
                       u.first_name AS teacher_first, u.last_name AS teacher_last,
                       c.first_name AS curator_first, c.last_name AS curator_last,
                       (SELECT COUNT(*) FROM group_students gs WHERE gs.group_id = g.id AND gs.status = \'active\') AS students_total
                FROM study_groups g
                JOIN streams s ON s.id = g.stream_id
                LEFT JOIN users u ON u.id = g.teacher_id
                LEFT JOIN users c ON c.id = g.curator_id';
        $params = [];
        $where = [];

        if ($role === 'curator') {
            $where[] = '(g.curator_id = ? OR s.curator_id = ?)';
            $params[] = $id;
            $params[] = $id;
        } elseif ($role === 'teacher') {
            $where[] = 'g.teacher_id = ?';
            $params[] = $id;
        } elseif ($role === 'student') {
            $where[] = 'EXISTS (SELECT 1 FROM group_students gs WHERE gs.group_id = g.id AND gs.student_id = ? AND gs.status = \'active\')';
            $params[] = $id;
        } elseif ($role === 'smm') {
            $this->redirect('/app');
        }

        $streamId = (int)($_GET['stream_id'] ?? 0);
        if ($streamId > 0) {
            $where[] = 'g.stream_id = ?';
            $params[] = $streamId;
        }
        $statusFilter = (string)($_GET['status'] ?? '');
        if (in_array($statusFilter, ['forming', 'active', 'finished'], true)) {
            $where[] = 'g.status = ?';
            $params[] = $statusFilter;
        }
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY s.title, g.name';

        $this->view('groups/index', [
            'title' => 'Группы — ' . config('app.name'),
            'items' => Database::fetchAll($sql, $params),
            'streams' => $this->availableStreams(),
            'canCreate' => in_array($role, ['root', 'admin', 'curator'], true),
            'filters' => ['stream_id' => $streamId, 'status' => $statusFilter],
        ]);
    }

    public function create(): void
    {
        $this->guardRole(['root', 'admin', 'curator']);
        $this->view('groups/create', [
            'title' => 'Новая группа — ' . config('app.name'),
            'streams' => $this->availableStreams(),
            'teachers' => $this->staff('teacher'),
            'curators' => $this->staff('curator'),
        ]);
    }

    public function store(): void
    {
        $this->guardRole(['root', 'admin', 'curator']);
        $this->guardCsrf();

        $streamId = $this->intInput('stream_id');
        $stream = $this->findStream($streamId);
        if ($stream === null) {
            $this->fail('Выберите поток.');
        }

        $name = $this->input('name');
        if ($name === '') {
            $this->errors(['Укажите название группы.']);
        }

        $groupId = Database::insert('study_groups', [
            'stream_id' => $streamId,
            'name' => mb_substr($name, 0, 80),
            'teacher_id' => $this->intInput('teacher_id') ?: null,
            'curator_id' => $this->intInput('curator_id') ?: ((int)$stream['curator_id'] ?: null),
            'capacity' => max(1, min(20, $this->intInput('capacity', 5))),
            'schedule_note' => $this->input('schedule_note') ?: null,
            'status' => in_array($this->input('status'), ['forming', 'active', 'finished'], true) ? $this->input('status') : 'forming',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->generateLessons($groupId, $stream);
        Activity::log('group.created', 'study_groups', $groupId);
        $this->ok('Группа создана, занятия добавлены в расписание.');
        $this->redirect('/app/groups/' . $groupId);
    }

    public function show(string $id): void
    {
        $this->guardAuth();
        $group = Database::fetch(
            'SELECT g.*, s.title AS stream_title, s.id AS stream_id, s.language, s.level, s.starts_on, s.ends_on,
                    u.first_name AS teacher_first, u.last_name AS teacher_last,
                    c.first_name AS curator_first, c.last_name AS curator_last
             FROM study_groups g
             JOIN streams s ON s.id = g.stream_id
             LEFT JOIN users u ON u.id = g.teacher_id
             LEFT JOIN users c ON c.id = g.curator_id
             WHERE g.id = ?',
            [(int)$id]
        );
        if ($group === null) {
            (new ErrorController())->notFound();
            return;
        }

        $user = $this->user();
        $role = $user['role'];
        $groupId = (int)$group['id'];

        $isMember = (int)Database::value(
            'SELECT COUNT(*) FROM group_students WHERE group_id = ? AND student_id = ? AND status = \'active\'',
            [$groupId, (int)$user['id']],
            0
        ) > 0;
        $isOwnerCurator = (int)$group['curator_id'] === (int)$user['id']
            || (int)Database::value('SELECT COUNT(*) FROM streams WHERE id = ? AND curator_id = ?', [(int)$group['stream_id'], (int)$user['id']], 0) > 0;
        $isOwnerTeacher = (int)$group['teacher_id'] === (int)$user['id'];

        $allowed = in_array($role, ['root', 'admin'], true) || $isOwnerCurator || $isOwnerTeacher || ($role === 'student' && $isMember);
        if (!$allowed) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Доступ закрыт'], 'app');
            return;
        }

        $students = Database::fetchAll(
            'SELECT u.id, u.first_name, u.last_name, u.login, u.email, p.percent, p.comment, gs.status AS member_status
             FROM group_students gs
             JOIN users u ON u.id = gs.student_id
             LEFT JOIN student_progress p ON p.student_id = u.id AND p.group_id = gs.group_id
             WHERE gs.group_id = ? AND gs.status = \'active\'
             ORDER BY u.last_name, u.first_name',
            [$groupId]
        );

        $availableStudents = in_array($role, ['root', 'admin', 'curator'], true)
            ? Database::fetchAll(
                'SELECT u.id, u.first_name, u.last_name, u.login FROM users u
                 WHERE u.role = \'student\' AND u.status = \'active\'
                   AND NOT EXISTS (
                       SELECT 1 FROM group_students gs JOIN study_groups g ON g.id = gs.group_id
                       WHERE gs.student_id = u.id AND g.stream_id = ?
                   )
                 ORDER BY u.last_name LIMIT 200',
                [(int)$group['stream_id']]
            )
            : [];

        $attached = materials_ready()
            ? Database::fetchAll(
                'SELECT m.*, gm.note, gm.added_at FROM group_materials gm
                 JOIN materials m ON m.id = gm.material_id
                 WHERE gm.group_id = ? ORDER BY m.title',
                [$groupId]
            )
            : [];

        $materials = (materials_ready() && in_array($role, ['root', 'admin', 'curator', 'teacher'], true))
            ? Database::fetchAll('SELECT * FROM materials ORDER BY title LIMIT 300')
            : [];

        $lessons = Database::fetchAll(
            'SELECT * FROM lessons WHERE group_id = ? ORDER BY scheduled_at LIMIT 200',
            [$groupId]
        );

        $this->view('groups/show', [
            'title' => $group['name'] . ' — ' . config('app.name'),
            'group' => $group,
            'students' => $students,
            'availableStudents' => $availableStudents,
            'attached' => $attached,
            'materials' => $materials,
            'lessons' => $lessons,
            'canManage' => in_array($role, ['root', 'admin'], true) || $isOwnerCurator || $isOwnerTeacher,
            'canEdit' => in_array($role, ['root', 'admin'], true) || $isOwnerCurator,
            'canDelete' => in_array($role, ['root', 'admin'], true),
            'teachers' => in_array($role, ['root', 'admin', 'curator'], true) ? $this->staff('teacher') : [],
            'curators' => in_array($role, ['root', 'admin', 'curator'], true) ? $this->staff('curator') : [],
        ]);
    }

    public function update(string $id): void
    {
        $this->guardRole(['root', 'admin', 'curator']);
        $this->guardCsrf();

        $group = $this->findGroup((int)$id);
        if (!$this->canEditGroup($group)) {
            $this->fail('Недостаточно прав для этой группы.');
        }

        Database::update('study_groups', [
            'name' => mb_substr($this->input('name', $group['name']), 0, 80),
            'teacher_id' => $this->intInput('teacher_id') ?: null,
            'curator_id' => $this->intInput('curator_id') ?: null,
            'capacity' => max(1, min(20, $this->intInput('capacity', (int)$group['capacity']))),
            'schedule_note' => $this->input('schedule_note') ?: null,
            'status' => in_array($this->input('status'), ['forming', 'active', 'finished'], true) ? $this->input('status') : $group['status'],
        ], 'id = ?', [(int)$id]);

        Activity::log('group.updated', 'study_groups', (int)$id);
        $this->ok('Группа обновлена.');
        $this->redirect('/app/groups/' . (int)$id);
    }

    public function destroy(string $id): void
    {
        $this->guardRole(['root', 'admin']);
        $this->guardCsrf();

        Database::delete('study_groups', 'id = ?', [(int)$id]);
        Activity::log('group.deleted', 'study_groups', (int)$id);
        $this->ok('Группа удалена вместе с занятиями.');
        $this->redirect('/app/groups');
    }

    public function attachMaterial(string $id): void
    {
        $this->guardRole(['root', 'admin', 'curator', 'teacher']);
        $this->guardCsrf();

        $group = $this->findGroup((int)$id);
        if (!$this->canManageGroup($group)) {
            $this->fail('Недостаточно прав.');
        }
        if (!materials_ready()) {
            $this->fail('Сначала примените обновление базы: upgrade.php.');
        }

        $materialId = $this->intInput('material_id');
        if ($materialId <= 0) {
            $this->fail('Выберите пособие.');
        }

        Database::q(
            'INSERT INTO group_materials (group_id, material_id, note, added_by, added_at)
             VALUES (?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE note = VALUES(note)',
            [(int)$id, $materialId, $this->input('note') ?: null, (int)$this->user()['id']]
        );

        Activity::log('group.material_added', 'study_groups', (int)$id, (string)$materialId);
        $this->ok('Пособие добавлено в группу.');
        $this->redirect('/app/groups/' . (int)$id);
    }

    public function detachMaterial(string $id, string $materialId): void
    {
        $this->guardRole(['root', 'admin', 'curator', 'teacher']);
        $this->guardCsrf();

        $group = $this->findGroup((int)$id);
        if (!$this->canManageGroup($group)) {
            $this->fail('Недостаточно прав.');
        }
        if (!materials_ready()) {
            $this->fail('Сначала примените обновление базы: upgrade.php.');
        }

        Database::q('DELETE FROM group_materials WHERE group_id = ? AND material_id = ?', [(int)$id, (int)$materialId]);
        Activity::log('group.material_removed', 'study_groups', (int)$id, $materialId);
        $this->ok('Пособие убрано из группы.');
        $this->redirect('/app/groups/' . (int)$id);
    }

    /** @return array<int, array> */
    private function availableStreams(): array
    {
        $user = $this->user();
        $sql = 'SELECT id, title, level, lessons_total, starts_on, curator_id FROM streams';
        $params = [];

        if ($user['role'] === 'curator') {
            $sql .= ' WHERE curator_id = ? OR id IN (SELECT stream_id FROM study_groups WHERE curator_id = ?)';
            $params = [(int)$user['id'], (int)$user['id']];
        } elseif (!in_array($user['role'], ['root', 'admin'], true)) {
            return [];
        }

        return Database::fetchAll($sql . ' ORDER BY created_at DESC', $params);
    }

    private function findStream(int $streamId): ?array
    {
        $stream = Database::fetch('SELECT * FROM streams WHERE id = ?', [$streamId]);
        if ($stream === null) {
            return null;
        }
        if ($this->user()['role'] === 'curator') {
            $owns = (int)$stream['curator_id'] === (int)$this->user()['id']
                || (int)Database::value('SELECT COUNT(*) FROM study_groups WHERE stream_id = ? AND curator_id = ?', [$streamId, (int)$this->user()['id']], 0) > 0;
            if (!$owns) {
                return null;
            }
        }
        return $stream;
    }

    private function findGroup(int $id): array
    {
        $group = Database::fetch('SELECT g.*, s.curator_id AS stream_curator FROM study_groups g JOIN streams s ON s.id = g.stream_id WHERE g.id = ?', [$id]);
        if ($group === null) {
            (new ErrorController())->notFound();
            exit;
        }
        return $group;
    }

    private function canEditGroup(array $group): bool
    {
        $user = $this->user();
        if (in_array($user['role'], ['root', 'admin'], true)) {
            return true;
        }
        return $user['role'] === 'curator'
            && ((int)$group['curator_id'] === (int)$user['id'] || (int)$group['stream_curator'] === (int)$user['id']);
    }

    private function canManageGroup(array $group): bool
    {
        $user = $this->user();
        if (in_array($user['role'], ['root', 'admin'], true)) {
            return true;
        }
        if ($user['role'] === 'curator') {
            return (int)$group['curator_id'] === (int)$user['id'] || (int)$group['stream_curator'] === (int)$user['id'];
        }
        if ($user['role'] === 'teacher') {
            return (int)$group['teacher_id'] === (int)$user['id'];
        }
        return false;
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
            Database::insert('lessons', [
                'group_id' => $groupId,
                'lesson_no' => $i,
                'topic' => 'Занятие ' . $i,
                'scheduled_at' => date('Y-m-d H:i:s', strtotime('+' . ($i - 1) . ' weeks', $start) + 18 * 3600),
                'duration_min' => 60,
                'status' => 'planned',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
