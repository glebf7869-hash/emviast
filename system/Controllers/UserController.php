<?php
declare(strict_types=1);

final class UserController extends Controller
{
    private const ROLES = ['admin', 'curator', 'teacher', 'smm', 'student'];

    public function index(): void
    {
        $this->guardRole(['root', 'admin', 'curator']);
        $role = $this->user()['role'];

        $filterRole = (string)($_GET['role'] ?? '');
        $q = trim((string)($_GET['q'] ?? ''));

        $where = [];
        $params = [];

        if ($role === 'curator') {
            $where[] = "role = 'student'";
        } elseif (in_array($filterRole, self::ROLES, true)) {
            $where[] = 'role = ?';
            $params[] = $filterRole;
        }
        if ($q !== '') {
            $where[] = '(first_name LIKE ? OR last_name LIKE ? OR login LIKE ? OR email LIKE ?)';
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like, $like);
        }

        $sql = 'SELECT * FROM users';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY FIELD(role, \'root\', \'admin\', \'curator\', \'teacher\', \'smm\', \'student\'), created_at DESC LIMIT 200';

        $items = Database::fetchAll($sql, $params);

        $credentials = $_SESSION['_credentials'] ?? null;
        unset($_SESSION['_credentials']);

        $this->view('users/index', [
            'title' => 'Аккаунты — ' . config('app.name'),
            'items' => $items,
            'filterRole' => $filterRole,
            'q' => $q,
            'credentials' => $credentials,
        ]);
    }

    public function create(): void
    {
        $this->guardRole(['root', 'admin', 'curator']);
        $application = null;
        $applicationId = (int)($_GET['application'] ?? 0);
        if ($applicationId > 0) {
            $application = Database::fetch('SELECT * FROM applications WHERE id = ? AND user_id IS NULL', [$applicationId]);
        }

        $this->view('users/create', [
            'title' => 'Новый аккаунт — ' . config('app.name'),
            'allowedRoles' => $this->allowedRoles(),
            'application' => $application,
            'prefillRole' => $application ? $this->roleForType($application['type']) : 'student',
        ]);
    }

    public function store(): void
    {
        $this->guardRole(['root', 'admin', 'curator']);
        $this->guardCsrf();

        $role = $this->input('role');
        if (!in_array($role, $this->allowedRoles(), true)) {
            $this->fail('Недостаточно прав для создания такой роли.');
        }

        $firstName = $this->input('first_name');
        $lastName = $this->input('last_name');
        $email = $this->input('email');
        $phone = $this->input('phone');
        $timezone = $this->input('timezone', 'Europe/Moscow');
        $applicationId = $this->intInput('application_id');

        if ($firstName === '' || $lastName === '') {
            $this->errors(['Укажите имя и фамилию.']);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors(['Укажите корректный e-mail.']);
        }
        if (Database::fetch('SELECT id FROM users WHERE email = ?', [$email])) {
            $this->errors(['Пользователь с таким e-mail уже существует.']);
        }

        $login = $this->input('login');
        if ($login === '') {
            $login = generate_login($firstName, $lastName);
        }
        if (!preg_match('/^[a-zA-Z0-9._-]{3,64}$/', $login)) {
            $this->errors(['Логин может содержать латинские буквы, цифры, точку, дефис и подчёркивание (3–64 символа).']);
        }
        if (Database::fetch('SELECT id FROM users WHERE login = ?', [$login])) {
            $this->errors(['Такой логин уже занят.']);
        }

        $password = (string)($_POST['password'] ?? '');
        $generated = isset($_POST['generate_password']);
        if ($generated) {
            $password = random_password(10);
        }
        if (mb_strlen($password) < 8) {
            $this->errors(['Пароль должен быть не короче 8 символов.']);
        }

        $userId = Database::insert('users', [
            'role' => $role,
            'login' => $login,
            'email' => mb_substr($email, 0, 190),
            'password_hash' => Auth::passwordHash($password),
            'first_name' => mb_substr($firstName, 0, 80),
            'last_name' => mb_substr($lastName, 0, 80),
            'phone' => $phone !== '' ? mb_substr($phone, 0, 32) : null,
            'timezone' => $timezone !== '' ? mb_substr($timezone, 0, 64) : 'Europe/Moscow',
            'status' => 'active',
            'must_change_password' => 1,
            'created_by' => (int)$this->user()['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if ($applicationId > 0) {
            Database::update('applications', [
                'status' => 'approved',
                'reviewed_by' => (int)$this->user()['id'],
                'reviewed_at' => date('Y-m-d H:i:s'),
                'user_id' => $userId,
            ], 'id = ?', [$applicationId]);
        }

        Mailer::credentials($email, $firstName . ' ' . $lastName, $login, $password);
        Activity::log('user.created', 'users', $userId, $role . ' ' . $login);

        $_SESSION['_credentials'] = [
            'name' => $firstName . ' ' . $lastName,
            'login' => $login,
            'password' => $password,
            'email' => $email,
            'role' => $role,
        ];

        $this->ok('Аккаунт создан. Данные для входа показаны ниже и отправлены на почту.');
        $this->redirect('/app/users');
    }

    public function edit(string $id): void
    {
        $this->guardRole(['root', 'admin', 'curator']);
        $user = $this->find((int)$id);

        $this->view('users/edit', [
            'title' => 'Аккаунт — ' . config('app.name'),
            'item' => $user,
            'allowedRoles' => $this->allowedRoles(true),
        ]);
    }

    public function update(string $id): void
    {
        $this->guardRole(['root', 'admin', 'curator']);
        $this->guardCsrf();
        $user = $this->find((int)$id);

        $firstName = $this->input('first_name');
        $lastName = $this->input('last_name');
        $email = $this->input('email');
        $role = $this->input('role');
        $status = $this->input('status', 'active');

        if ($firstName === '' || $lastName === '') {
            $this->errors(['Имя и фамилия обязательны.']);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors(['Некорректный e-mail.']);
        }
        $duplicate = Database::fetch('SELECT id FROM users WHERE email = ? AND id <> ?', [$email, (int)$id]);
        if ($duplicate) {
            $this->errors(['Этот e-mail уже используется.']);
        }

        $data = [
            'first_name' => mb_substr($firstName, 0, 80),
            'last_name' => mb_substr($lastName, 0, 80),
            'email' => mb_substr($email, 0, 190),
            'status' => $status === 'blocked' ? 'blocked' : 'active',
        ];

        if ($this->user()['role'] === 'root' && in_array($role, array_merge(['root'], self::ROLES), true) && $user['id'] !== $this->user()['id']) {
            $data['role'] = $role;
        }

        Database::update('users', $data, 'id = ?', [(int)$id]);
        Activity::log('user.updated', 'users', (int)$id);
        $this->ok('Данные аккаунта обновлены.');
        $this->redirect('/app/users/' . (int)$id . '/edit');
    }

    public function resetPassword(string $id): void
    {
        $this->guardRole(['root', 'admin', 'curator']);
        $this->guardCsrf();
        $user = $this->find((int)$id);

        $password = random_password(10);
        Database::update('users', [
            'password_hash' => Auth::passwordHash($password),
            'must_change_password' => 1,
        ], 'id = ?', [(int)$id]);

        Mailer::credentials($user['email'], $user['first_name'] . ' ' . $user['last_name'], $user['login'], $password);
        Activity::log('user.password_reset', 'users', (int)$id);

        $_SESSION['_credentials'] = [
            'name' => $user['first_name'] . ' ' . $user['last_name'],
            'login' => $user['login'],
            'password' => $password,
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        $this->ok('Пароль сброшен и отправлен на почту.');
        $this->redirect('/app/users');
    }

    /** @return array<int, string> */
    private function allowedRoles(bool $forEdit = false): array
    {
        $role = $this->user()['role'];
        if ($role === 'root') {
            return array_merge(['root'], self::ROLES);
        }
        if ($role === 'admin') {
            return $forEdit ? self::ROLES : ['admin', 'curator', 'teacher', 'smm', 'student'];
        }
        return ['student'];
    }

    private function roleForType(string $type): string
    {
        return [
            'student' => 'student',
            'teacher' => 'teacher',
            'curator' => 'curator',
            'smm' => 'smm',
        ][$type] ?? 'student';
    }

    private function find(int $id): array
    {
        $user = Database::fetch('SELECT * FROM users WHERE id = ?', [$id]);
        if ($user === null) {
            (new ErrorController())->notFound();
            exit;
        }

        $actor = $this->user();
        if ($actor['role'] === 'curator' && $user['role'] !== 'student') {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Доступ закрыт'], 'app');
            exit;
        }
        if ($actor['role'] === 'admin' && $user['role'] === 'root') {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Доступ закрыт'], 'app');
            exit;
        }
        return $user;
    }
}
