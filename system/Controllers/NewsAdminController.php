<?php
declare(strict_types=1);

final class NewsAdminController extends Controller
{
    public function index(): void
    {
        $this->guardRole(['root', 'admin', 'smm']);
        $user = $this->user();

        $sql = 'SELECT n.*, u.first_name, u.last_name FROM news n LEFT JOIN users u ON u.id = n.author_id';
        $params = [];
        if ($user['role'] === 'smm') {
            $sql .= ' WHERE n.author_id = ?';
            $params[] = (int)$user['id'];
        }
        $sql .= ' ORDER BY n.created_at DESC LIMIT 100';

        $this->view('news/index', [
            'title' => 'Новости — ' . config('app.name'),
            'items' => Database::fetchAll($sql, $params),
        ]);
    }

    public function create(): void
    {
        $this->guardRole(['root', 'admin', 'smm']);
        $this->view('news/form', [
            'title' => 'Новая публикация — ' . config('app.name'),
            'item' => null,
        ]);
    }

    public function store(): void
    {
        $this->guardRole(['root', 'admin', 'smm']);
        $this->guardCsrf();

        $title = $this->input('title');
        $body = $this->input('body');
        $titleEn = $this->input('title_en');
        $bodyEn = $this->input('body_en');

        if ($title === '' || $body === '') {
            $this->errors(['Заполните заголовок и текст на русском.']);
        }

        $status = $this->user()['role'] === 'smm' ? 'draft' : $this->input('status', 'draft');
        $status = in_array($status, ['draft', 'published'], true) ? $status : 'draft';

        if ($status === 'published' && ($titleEn === '' || $bodyEn === '')) {
            $this->errors(['Для публикации заполните английскую версию: заголовок и текст.']);
        }

        $data = [
            'slug' => $this->uniqueSlug($title),
            'title' => mb_substr($title, 0, 190),
            'title_en' => $titleEn !== '' ? mb_substr($titleEn, 0, 190) : null,
            'excerpt' => mb_substr($this->input('excerpt'), 0, 255) ?: null,
            'excerpt_en' => mb_substr($this->input('excerpt_en'), 0, 255) ?: null,
            'body' => $body,
            'body_en' => $bodyEn !== '' ? $bodyEn : null,
            'status' => $status,
            'published_at' => $status === 'published' ? date('Y-m-d H:i:s') : null,
            'author_id' => (int)$this->user()['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if (!empty($_FILES['cover']['name'])) {
            try {
                $data['cover'] = upload_image($_FILES['cover'], 'news');
            } catch (RuntimeException $e) {
                $this->fail($e->getMessage());
            }
        }

        $newsId = Database::insert('news', $data);

        Activity::log('news.created', 'news', $newsId, $status);
        $this->ok($status === 'published' ? 'Новость опубликована.' : 'Черновик сохранён.');
        $this->redirect('/app/news');
    }

    public function edit(string $id): void
    {
        $this->guardRole(['root', 'admin', 'smm']);
        $item = $this->find((int)$id);

        $this->view('news/form', [
            'title' => 'Редактирование — ' . config('app.name'),
            'item' => $item,
        ]);
    }

    public function update(string $id): void
    {
        $this->guardRole(['root', 'admin', 'smm']);
        $this->guardCsrf();
        $item = $this->find((int)$id);

        $title = $this->input('title');
        $body = $this->input('body');
        $titleEn = $this->input('title_en');
        $bodyEn = $this->input('body_en');

        if ($title === '' || $body === '') {
            $this->errors(['Заполните заголовок и текст на русском.']);
        }

        $status = $this->user()['role'] === 'smm'
            ? 'draft'
            : $this->input('status', $item['status']);
        $status = in_array($status, ['draft', 'published'], true) ? $status : 'draft';

        if ($status === 'published' && ($titleEn === '' || $bodyEn === '')) {
            $this->errors(['Для публикации заполните английскую версию: заголовок и текст.']);
        }

        $data = [
            'title' => mb_substr($title, 0, 190),
            'title_en' => $titleEn !== '' ? mb_substr($titleEn, 0, 190) : null,
            'excerpt' => mb_substr($this->input('excerpt'), 0, 255) ?: null,
            'excerpt_en' => mb_substr($this->input('excerpt_en'), 0, 255) ?: null,
            'body' => $body,
            'body_en' => $bodyEn !== '' ? $bodyEn : null,
            'status' => $status,
            'published_at' => $status === 'published'
                ? ($item['published_at'] ?: date('Y-m-d H:i:s'))
                : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (!empty($_FILES['cover']['name'])) {
            try {
                $data['cover'] = upload_image($_FILES['cover'], 'news');
            } catch (RuntimeException $e) {
                $this->fail($e->getMessage());
            }
        }

        Database::update('news', $data, 'id = ?', [(int)$id]);

        Activity::log('news.updated', 'news', (int)$id, $status);
        $this->ok('Публикация обновлена.');
        $this->redirect('/app/news');
    }

    public function destroy(string $id): void
    {
        $this->guardRole(['root', 'admin']);
        $this->guardCsrf();

        Database::delete('news', 'id = ?', [(int)$id]);
        Activity::log('news.deleted', 'news', (int)$id);
        $this->ok('Публикация удалена.');
        $this->redirect('/app/news');
    }

    private function find(int $id): array
    {
        $item = Database::fetch('SELECT * FROM news WHERE id = ?', [$id]);
        if ($item === null) {
            (new ErrorController())->notFound();
            exit;
        }
        $user = $this->user();
        if ($user['role'] === 'smm' && (int)$item['author_id'] !== (int)$user['id']) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Доступ закрыт'], 'app');
            exit;
        }
        return $item;
    }

    private function uniqueSlug(string $title): string
    {
        $base = translit($title);
        $base = trim(preg_replace('/[^a-z0-9]+/', '-', $base) ?? '', '-');
        if ($base === '') {
            $base = 'news-' . date('Ymd');
        }
        $base = mb_substr($base, 0, 120);
        $slug = $base;
        $i = 2;
        while (Database::fetch('SELECT id FROM news WHERE slug = ?', [$slug])) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }
}
