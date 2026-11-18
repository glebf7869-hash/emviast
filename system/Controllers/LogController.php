<?php
declare(strict_types=1);

final class LogController extends Controller
{
    public function index(): void
    {
        $this->guardRole(['root']);

        $items = Database::fetchAll(
            'SELECT l.*, u.first_name, u.last_name, u.role
             FROM activity_log l LEFT JOIN users u ON u.id = l.user_id
             ORDER BY l.created_at DESC LIMIT 200'
        );

        $this->view('profile/logs', [
            'title' => 'Журнал действий — ' . config('app.name'),
            'items' => $items,
        ]);
    }
}
