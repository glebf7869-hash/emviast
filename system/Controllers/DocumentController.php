<?php
declare(strict_types=1);

final class DocumentController extends Controller
{
    public function index(): void
    {
        $this->guardRole(['root', 'admin']);

        if (!table_exists('documents')) {
            flash_set('warning', 'Сначала примените обновление базы: upgrade.php.');
            $this->redirect('/upgrade.php');
        }

        $items = Database::fetchAll(
            'SELECT d.*, u.first_name, u.last_name FROM documents d
             LEFT JOIN users u ON u.id = d.created_by
             ORDER BY d.sort_order, d.created_at DESC'
        );

        $this->view('documents/index', [
            'title' => 'Документы — ' . config('app.name'),
            'items' => $items,
        ]);
    }

    public function create(): void
    {
        $this->guardRole(['root', 'admin']);
        $this->view('documents/form', [
            'title' => 'Новый документ — ' . config('app.name'),
            'item' => null,
        ]);
    }

    public function store(): void
    {
        $this->guardRole(['root', 'admin']);
        $this->guardCsrf();

        $title = $this->input('title');
        if ($title === '') {
            $this->errors(['Укажите название документа.']);
        }

        $data = [
            'title' => mb_substr($title, 0, 190),
            'url' => $this->input('url') ?: null,
            'description' => $this->input('description') ?: null,
            'sort_order' => max(0, $this->intInput('sort_order')),
            'created_by' => (int)$this->user()['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ];

        try {
            if (!empty($_FILES['file']['name'])) {
                $data['file'] = upload_document($_FILES['file']);
            }
        } catch (RuntimeException $e) {
            $this->fail($e->getMessage());
        }

        if (empty($data['file']) && empty($data['url'])) {
            $this->errors(['Загрузите файл или укажите ссылку.']);
        }

        $id = Database::insert('documents', $data);
        Activity::log('document.created', 'documents', $id);
        $this->ok('Документ добавлен на страницу правил.');
        $this->redirect('/app/documents');
    }

    public function edit(string $id): void
    {
        $this->guardRole(['root', 'admin']);
        $this->view('documents/form', [
            'title' => 'Редактирование документа — ' . config('app.name'),
            'item' => $this->find((int)$id),
        ]);
    }

    public function update(string $id): void
    {
        $this->guardRole(['root', 'admin']);
        $this->guardCsrf();

        $item = $this->find((int)$id);
        $title = $this->input('title');
        if ($title === '') {
            $this->errors(['Укажите название документа.']);
        }

        $data = [
            'title' => mb_substr($title, 0, 190),
            'url' => $this->input('url') ?: null,
            'description' => $this->input('description') ?: null,
            'sort_order' => max(0, $this->intInput('sort_order')),
        ];

        try {
            if (!empty($_FILES['file']['name'])) {
                $data['file'] = upload_document($_FILES['file']);
            }
        } catch (RuntimeException $e) {
            $this->fail($e->getMessage());
        }

        Database::update('documents', $data, 'id = ?', [(int)$item['id']]);
        Activity::log('document.updated', 'documents', (int)$item['id']);
        $this->ok('Документ обновлён.');
        $this->redirect('/app/documents');
    }

    public function destroy(string $id): void
    {
        $this->guardRole(['root', 'admin']);
        $this->guardCsrf();

        Database::delete('documents', 'id = ?', [(int)$id]);
        Activity::log('document.deleted', 'documents', (int)$id);
        $this->ok('Документ удалён.');
        $this->redirect('/app/documents');
    }

    private function find(int $id): array
    {
        $item = Database::fetch('SELECT * FROM documents WHERE id = ?', [$id]);
        if ($item === null) {
            (new ErrorController())->notFound();
            exit;
        }
        return $item;
    }
}
