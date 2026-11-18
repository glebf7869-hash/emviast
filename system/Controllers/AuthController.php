<?php
declare(strict_types=1);

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/app');
        }
        $this->view('auth/login', [
            'title' => t('page.login'),
            'sent' => isset($_GET['sent']),
        ], 'public');
    }

    public function login(): void
    {
        $this->guardCsrf();

        $lockUntil = (int)($_SESSION['login_lock_until'] ?? 0);
        if ($lockUntil > time()) {
            $this->fail('Слишком много попыток. Попробуйте через минуту.');
        }

        $login = $this->input('login');
        $password = (string)($_POST['password'] ?? '');

        if ($login === '' || $password === '') {
            $this->fail('Введите логин и пароль.');
        }

        if (!Auth::attempt($login, $password)) {
            $attempts = (int)($_SESSION['login_attempts'] ?? 0) + 1;
            $_SESSION['login_attempts'] = $attempts;
            if ($attempts >= 5) {
                $_SESSION['login_lock_until'] = time() + 60;
                $_SESSION['login_attempts'] = 0;
            }
            $this->fail('Неверный логин или пароль, либо аккаунт заблокирован.');
        }

        unset($_SESSION['login_attempts'], $_SESSION['login_lock_until']);
        $user = $this->user();
        if ((int)$user['must_change_password'] === 1) {
            flash_set('info', 'Вы вошли с временным паролем. Смените его в профиле.');
            $this->redirect('/app/profile');
        }
        $this->redirect('/app');
    }

    public function logout(): void
    {
        $this->guardCsrf();
        Activity::log('auth.logout', 'users', (int)Auth::id());
        Auth::logout();
        $this->redirect('/');
    }
}
