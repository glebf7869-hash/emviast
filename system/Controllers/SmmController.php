<?php
declare(strict_types=1);

final class SmmController extends Controller
{
    public function tasks(): void
    {
        $this->guardRole(['root', 'admin', 'smm']);
        $user = $this->user();
        $isSmm = $user['role'] === 'smm';

        $sql = 'SELECT t.*, a.first_name AS assignee_first, a.last_name AS assignee_last
                FROM smm_tasks t LEFT JOIN users a ON a.id = t.assignee_id';
        $params = [];
        if ($isSmm) {
            $sql .= ' WHERE t.assignee_id = ?';
            $params[] = (int)$user['id'];
        }
        $sql .= ' ORDER BY FIELD(t.status, \'in_progress\', \'todo\', \'done\'), t.due_on IS NULL, t.due_on';

        $this->view('smm/tasks', [
            'title' => 'SMM-задачи — ' . config('app.name'),
            'items' => Database::fetchAll($sql, $params),
            'team' => Database::fetchAll("SELECT id, first_name, last_name FROM users WHERE role = 'smm' AND status = 'active'"),
            'isSmm' => $isSmm,
            'today' => date('Y-m-d'),
        ]);
    }

    public function storeTask(): void
    {
        $this->guardRole(['root', 'admin', 'smm']);
        $this->guardCsrf();

        $title = $this->input('title');
        if ($title === '') {
            $this->errors(['Укажите задачу.']);
        }

        $assignee = $this->user()['role'] === 'smm'
            ? (int)$this->user()['id']
            : ($this->intInput('assignee_id') ?: null);

        Database::insert('smm_tasks', [
            'title' => mb_substr($title, 0, 160),
            'description' => $this->input('description') ?: null,
            'platform' => $this->input('platform') ?: null,
            'assignee_id' => $assignee,
            'due_on' => $this->input('due_on') ?: null,
            'status' => 'todo',
            'created_by' => (int)$this->user()['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        Activity::log('smm.task_created', 'smm_tasks', 0, $title);
        $this->ok('Задача добавлена.');
        $this->redirect('/app/smm');
    }

    public function updateTask(string $id): void
    {
        $this->guardRole(['root', 'admin', 'smm']);
        $this->guardCsrf();

        $task = Database::fetch('SELECT * FROM smm_tasks WHERE id = ?', [(int)$id]);
        if ($task === null) {
            $this->fail('Задача не найдена.');
        }
        if ($this->user()['role'] === 'smm' && (int)$task['assignee_id'] !== (int)$this->user()['id']) {
            $this->fail('Это не ваша задача.');
        }

        $status = $this->input('status');
        if (!in_array($status, ['todo', 'in_progress', 'done'], true)) {
            $this->fail('Неизвестный статус.');
        }

        Database::update('smm_tasks', [
            'status' => $status,
            'done_at' => $status === 'done' ? date('Y-m-d H:i:s') : null,
        ], 'id = ?', [(int)$id]);

        Activity::log('smm.task_status', 'smm_tasks', (int)$id, $status);
        $this->redirect('/app/smm');
    }

    public function stats(): void
    {
        $this->guardRole(['root', 'admin', 'smm']);

        $rows = Database::fetchAll('SELECT * FROM smm_stats ORDER BY stat_date DESC, platform LIMIT 60');

        $summary = Database::fetchAll(
            'SELECT s.platform, s.followers, s.reach, s.likes, s.comments, s.stat_date
             FROM smm_stats s
             JOIN (SELECT platform, MAX(stat_date) AS d FROM smm_stats GROUP BY platform) m
               ON m.platform = s.platform AND m.d = s.stat_date
             ORDER BY s.platform'
        );

        $this->view('smm/stats', [
            'title' => 'Статистика соцсетей — ' . config('app.name'),
            'rows' => $rows,
            'summary' => $summary,
        ]);
    }

    public function saveStats(): void
    {
        $this->guardRole(['root', 'admin', 'smm']);
        $this->guardCsrf();

        $platform = $this->input('platform');
        $date = $this->input('stat_date');
        if ($platform === '' || $date === '') {
            $this->errors(['Укажите площадку и дату.']);
        }

        Database::q(
            'INSERT INTO smm_stats (platform, stat_date, followers, reach, likes, comments, notes, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE followers = VALUES(followers), reach = VALUES(reach),
                                     likes = VALUES(likes), comments = VALUES(comments), notes = VALUES(notes)',
            [
                mb_substr($platform, 0, 40),
                $date,
                max(0, $this->intInput('followers')),
                max(0, $this->intInput('reach')),
                max(0, $this->intInput('likes')),
                max(0, $this->intInput('comments')),
                $this->input('notes') ?: null,
                (int)$this->user()['id'],
            ]
        );

        Activity::log('smm.stats_saved', 'smm_stats', 0, $platform . ' ' . $date);
        $this->ok('Показатели сохранены.');
        $this->redirect('/app/smm/stats');
    }
}
