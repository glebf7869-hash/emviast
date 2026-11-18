<?php
declare(strict_types=1);

final class ResourceController extends Controller
{
    public function index(): void
    {
        $this->guardRole(['root', 'admin', 'curator', 'teacher']);

        if (!table_exists('resources')) {
            flash_set('warning', 'Сначала примените обновление базы: upgrade.php.');
            $this->redirect('/upgrade.php');
        }

        $user = $this->user();
        $isAdmin = in_array($user['role'], ['root', 'admin'], true);

        $sql = 'SELECT r.*, u.first_name, u.last_name FROM resources r LEFT JOIN users u ON u.id = r.created_by';
        $where = [];
        $params = [];

        if (!$isAdmin) {
            $where[] = "r.access = 'staff'";
        }

        $textbook = trim((string)($_GET['textbook'] ?? ''));
        if ($textbook !== '') {
            $where[] = 'r.textbook = ?';
            $params[] = $textbook;
        }

        $topic = trim((string)($_GET['topic'] ?? ''));
        if ($topic !== '') {
            $where[] = 'r.topic = ?';
            $params[] = $topic;
        }

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY r.textbook IS NULL, r.textbook, r.topic IS NULL, r.topic, r.title';

        $items = Database::fetchAll($sql, $params);

        $textbooks = Database::fetchAll('SELECT DISTINCT textbook FROM resources WHERE textbook IS NOT NULL AND textbook <> "" ORDER BY textbook');
        $topics = Database::fetchAll('SELECT DISTINCT topic FROM resources WHERE topic IS NOT NULL AND topic <> "" ORDER BY topic');

        $this->view('resources/index', [
            'title' => 'Презентации — ' . config('app.name'),
            'items' => $items,
            'textbooks' => array_column($textbooks, 'textbook'),
            'topics' => array_column($topics, 'topic'),
            'filters' => ['textbook' => $textbook, 'topic' => $topic],
            'canManage' => $isAdmin,
        ]);
    }

    public function create(): void
    {
        $this->guardRole(['root', 'admin']);
        $this->view('resources/form', [
            'title' => 'Новый ресурс — ' . config('app.name'),
            'item' => null,
        ]);
    }

    public function store(): void
    {
        $this->guardRole(['root', 'admin']);
        $this->guardCsrf();

        $title = $this->input('title');
        $url = $this->input('url');
        if ($title === '' || $url === '') {
            $this->errors(['Укажите название и ссылку на Google Диск.']);
        }

        $kind = str_contains($url, '/folders/') ? 'folder' : 'file';

        $id = Database::insert('resources', [
            'title' => mb_substr($title, 0, 190),
            'kind' => $kind,
            'url' => mb_substr($url, 0, 500),
            'drive_id' => drive_id_from_url($url) ?: null,
            'textbook' => $this->input('textbook') ?: null,
            'topic' => $this->input('topic') ?: null,
            'level' => $this->input('level') ?: null,
            'description' => $this->input('description') ?: null,
            'access' => $this->input('access') === 'admins' ? 'admins' : 'staff',
            'created_by' => (int)$this->user()['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        Activity::log('resource.created', 'resources', $id);
        $this->ok('Ресурс добавлен в каталог.');
        $this->redirect('/app/resources');
    }

    public function edit(string $id): void
    {
        $this->guardRole(['root', 'admin']);
        $this->view('resources/form', [
            'title' => 'Редактирование ресурса — ' . config('app.name'),
            'item' => $this->find((int)$id),
        ]);
    }

    public function update(string $id): void
    {
        $this->guardRole(['root', 'admin']);
        $this->guardCsrf();

        $item = $this->find((int)$id);
        $title = $this->input('title');
        $url = $this->input('url');
        if ($title === '' || $url === '') {
            $this->errors(['Укажите название и ссылку.']);
        }

        Database::update('resources', [
            'title' => mb_substr($title, 0, 190),
            'kind' => str_contains($url, '/folders/') ? 'folder' : 'file',
            'url' => mb_substr($url, 0, 500),
            'drive_id' => drive_id_from_url($url) ?: null,
            'textbook' => $this->input('textbook') ?: null,
            'topic' => $this->input('topic') ?: null,
            'level' => $this->input('level') ?: null,
            'description' => $this->input('description') ?: null,
            'access' => $this->input('access') === 'admins' ? 'admins' : 'staff',
        ], 'id = ?', [(int)$item['id']]);

        Activity::log('resource.updated', 'resources', (int)$item['id']);
        $this->ok('Ресурс обновлён.');
        $this->redirect('/app/resources');
    }

    public function destroy(string $id): void
    {
        $this->guardRole(['root', 'admin']);
        $this->guardCsrf();

        Database::delete('resources', 'id = ?', [(int)$id]);
        Activity::log('resource.deleted', 'resources', (int)$id);
        $this->ok('Ресурс удалён из каталога.');
        $this->redirect('/app/resources');
    }

    private function find(int $id): array
    {
        $item = Database::fetch('SELECT * FROM resources WHERE id = ?', [$id]);
        if ($item === null) {
            (new ErrorController())->notFound();
            exit;
        }
        return $item;
    }
}
