<?php
declare(strict_types=1);

final class ProfileController extends Controller
{
    public function index(): void
    {
        $this->guardAuth();
        $this->view('profile/index', [
            'title' => 'Профиль — ' . config('app.name'),
            'item' => $this->user(),
        ]);
    }

    public function updatePassword(): void
    {
        $this->guardAuth();
        $this->guardCsrf();

        $current = (string)($_POST['current_password'] ?? '');
        $new = (string)($_POST['new_password'] ?? '');
        $repeat = (string)($_POST['repeat_password'] ?? '');

        $row = Database::fetch('SELECT password_hash FROM users WHERE id = ?', [(int)$this->user()['id']]);
        if ($row === null || !password_verify($current, $row['password_hash'])) {
            $this->fail('Текущий пароль неверен.');
        }
        if (mb_strlen($new) < 8) {
            $this->fail('Новый пароль должен быть не короче 8 символов.');
        }
        if ($new !== $repeat) {
            $this->fail('Пароли не совпадают.');
        }

        Database::update('users', [
            'password_hash' => Auth::passwordHash($new),
            'must_change_password' => 0,
        ], 'id = ?', [(int)$this->user()['id']]);

        Activity::log('auth.password_changed', 'users', (int)$this->user()['id']);
        $this->ok('Пароль обновлён.');
        $this->redirect('/app/profile');
    }
}
