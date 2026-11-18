<?php
declare(strict_types=1);

final class MaterialController extends Controller
{
    public function index(): void
    {
        $this->guardRole(['root', 'admin', 'curator', 'teacher']);

        if (!materials_ready()) {
            flash_set('warning', 'Сначала примените обновление базы: upgrade.php.');
            $this->redirect('/upgrade.php');
        }

        $level = (string)($_GET['level'] ?? '');
        $q = trim((string)($_GET['q'] ?? ''));

        $where = [];
        $params = [];
        if ($level !== '') {
            $where[] = 'level = ?';
            $params[] = $level;
        }
        if ($q !== '') {
            $where[] = '(title LIKE ? OR author LIKE ? OR description LIKE ?)';
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like);
        }

        $sql = 'SELECT m.*, u.first_name, u.last_name,
                       (SELECT COUNT(*) FROM group_materials gm WHERE gm.material_id = m.id) AS groups_total
                FROM materials m LEFT JOIN users u ON u.id = m.created_by';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY m.title LIMIT 300';

        $this->view('materials/index', [
            'title' => 'Пособия — ' . config('app.name'),
            'items' => Database::fetchAll($sql, $params),
            'filters' => ['level' => $level, 'q' => $q],
            'canCreate' => in_array($this->user()['role'], ['root', 'admin'], true),
        ]);
    }

    public function create(): void
    {
        $this->guardRole(['root', 'admin']);
        $this->view('materials/form', [
            'title' => 'Новое пособие — ' . config('app.name'),
            'item' => null,
        ]);
    }

    public function store(): void
    {
        $this->guardRole(['root', 'admin']);
        $this->guardCsrf();
        $this->requireMaterials();

        $title = $this->input('title');
        if ($title === '') {
            $this->errors(['Укажите название пособия.']);
        }

        $id = Database::insert('materials', [
            'title' => mb_substr($title, 0, 160),
            'author' => $this->input('author') ?: null,
            'level' => $this->input('level') ?: null,
            'url' => $this->input('url') ?: null,
            'description' => $this->input('description') ?: null,
            'created_by' => (int)$this->user()['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        Activity::log('material.created', 'materials', $id);
        $this->ok('Пособие добавлено в каталог.');
        $this->redirect('/app/materials');
    }

    public function edit(string $id): void
    {
        $this->guardRole(['root', 'admin']);
        $this->view('materials/form', [
            'title' => 'Редактирование пособия — ' . config('app.name'),
            'item' => $this->find((int)$id),
        ]);
    }

    public function update(string $id): void
    {
        $this->guardRole(['root', 'admin']);
        $this->guardCsrf();
        $this->requireMaterials();

        $item = $this->find((int)$id);
        $title = $this->input('title');
        if ($title === '') {
            $this->errors(['Укажите название пособия.']);
        }

        Database::update('materials', [
            'title' => mb_substr($title, 0, 160),
            'author' => $this->input('author') ?: null,
            'level' => $this->input('level') ?: null,
            'url' => $this->input('url') ?: null,
            'description' => $this->input('description') ?: null,
        ], 'id = ?', [(int)$item['id']]);

        Activity::log('material.updated', 'materials', (int)$item['id']);
        $this->ok('Пособие обновлено.');
        $this->redirect('/app/materials');
    }

    public function destroy(string $id): void
    {
        $this->guardRole(['root', 'admin']);
        $this->guardCsrf();
        $this->requireMaterials();

        Database::delete('materials', 'id = ?', [(int)$id]);
        Activity::log('material.deleted', 'materials', (int)$id);
        $this->ok('Пособие удалено.');
        $this->redirect('/app/materials');
    }

    private function requireMaterials(): void
    {
        if (!materials_ready()) {
            $this->fail('Сначала примените обновление базы: upgrade.php.');
        }
    }

    private function find(int $id): array
    {
        $item = Database::fetch('SELECT * FROM materials WHERE id = ?', [$id]);
        if ($item === null) {
            (new ErrorController())->notFound();
            exit;
        }
        return $item;
    }
}
