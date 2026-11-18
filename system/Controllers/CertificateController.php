<?php
declare(strict_types=1);

final class CertificateController extends Controller
{
    public function issue(string $streamId): void
    {
        $this->guardAuth();
        $this->guardCsrf();

        $user = $this->user();
        $stream = Database::fetch('SELECT * FROM streams WHERE id = ?', [(int)$streamId]);
        if ($stream === null) {
            $this->fail('Поток не найден.');
        }

        $role = $this->roleInStream((int)$user['id'], $stream);
        if ($role === null) {
            $this->fail('Вы не участвовали в этом потоке.');
        }

        if (empty($stream['ends_on']) || strtotime((string)$stream['ends_on']) > strtotime('today')) {
            $this->fail('Сертификат станет доступен после окончания потока.');
        }

        $existing = Database::fetch('SELECT * FROM certificates WHERE user_id = ? AND stream_id = ?', [(int)$user['id'], (int)$stream['id']]);
        if ($existing === null) {
            $id = Database::insert('certificates', [
                'user_id' => (int)$user['id'],
                'stream_id' => (int)$stream['id'],
                'role' => $role,
                'number' => 'TMP-' . bin2hex(random_bytes(4)),
                'issued_at' => date('Y-m-d H:i:s'),
            ]);
            Database::update('certificates', [
                'number' => 'EMVIA-' . date('Y') . '-' . str_pad((string)$id, 5, '0', STR_PAD_LEFT),
            ], 'id = ?', [$id]);
            Activity::log('certificate.issued', 'certificates', $id, $role);
        }

        $this->redirect('/app/certificate/' . (int)$stream['id']);
    }

    public function show(string $streamId): void
    {
        $this->guardAuth();

        $user = $this->user();
        $stream = Database::fetch('SELECT * FROM streams WHERE id = ?', [(int)$streamId]);
        if ($stream === null) {
            (new ErrorController())->notFound();
            return;
        }

        $certificate = Database::fetch(
            'SELECT * FROM certificates WHERE user_id = ? AND stream_id = ?',
            [(int)$user['id'], (int)$stream['id']]
        );
        if ($certificate === null) {
            flash_set('warning', 'Сертификат ещё не получен.');
            $this->redirect('/app');
        }

        $role = (string)$certificate['role'];

        View::render('certificate/show', [
            'title' => 'Сертификат — ' . config('app.name'),
            'user' => $user,
            'stream' => $stream,
            'certificate' => $certificate,
            'role' => $role,
            'roleLabel' => ['student' => 'ученика', 'teacher' => 'преподавателя', 'curator' => 'куратора'][$role] ?? 'участника',
            'activity' => $this->activity($user, $stream, $role),
            'issuedAt' => fmt_date($certificate['issued_at']),
        ], 'certificate');
    }

    private function roleInStream(int $userId, array $stream): ?string
    {
        $isStudent = (int)Database::value(
            'SELECT COUNT(*) FROM group_students gs JOIN study_groups g ON g.id = gs.group_id
             WHERE g.stream_id = ? AND gs.student_id = ? AND gs.status = \'active\'',
            [(int)$stream['id'], $userId],
            0
        ) > 0;
        if ($isStudent) {
            return 'student';
        }

        $isTeacher = (int)Database::value(
            'SELECT COUNT(*) FROM study_groups WHERE stream_id = ? AND teacher_id = ?',
            [(int)$stream['id'], $userId],
            0
        ) > 0;
        if ($isTeacher) {
            return 'teacher';
        }

        $isCurator = (int)$stream['curator_id'] === $userId
            || (int)Database::value(
                'SELECT COUNT(*) FROM study_groups WHERE stream_id = ? AND curator_id = ?',
                [(int)$stream['id'], $userId],
                0
            ) > 0;

        return $isCurator ? 'curator' : null;
    }

    /** @return array<int, array{label:string, value:string}> */
    private function activity(array $user, array $stream, string $role): array
    {
        $userId = (int)$user['id'];
        $streamId = (int)$stream['id'];

        if ($role === 'student') {
            $lessons = (int)Database::value(
                'SELECT COUNT(DISTINCT l.id) FROM lessons l
                 JOIN group_students gs ON gs.group_id = l.group_id AND gs.student_id = ? AND gs.status = \'active\'
                 JOIN study_groups g ON g.id = l.group_id
                 WHERE g.stream_id = ? AND l.status = \'done\'',
                [$userId, $streamId],
                0
            );
            $homework = (int)Database::value(
                'SELECT COUNT(*) FROM homework_submissions hs
                 JOIN homework h ON h.id = hs.homework_id
                 JOIN study_groups g ON g.id = h.group_id
                 WHERE g.stream_id = ? AND hs.student_id = ? AND hs.status = \'checked\'',
                [$streamId, $userId],
                0
            );
            return [
                ['label' => 'посещённых занятий', 'value' => (string)$lessons],
                ['label' => 'проверенных домашних заданий', 'value' => (string)$homework],
            ];
        }

        if ($role === 'teacher') {
            $groups = (int)Database::value('SELECT COUNT(*) FROM study_groups WHERE stream_id = ? AND teacher_id = ?', [$streamId, $userId], 0);
            $reports = (int)Database::value(
                'SELECT COUNT(*) FROM lesson_reports r JOIN lessons l ON l.id = r.lesson_id
                 JOIN study_groups g ON g.id = l.group_id
                 WHERE g.stream_id = ? AND g.teacher_id = ? AND r.status IN (\'submitted\',\'approved\')',
                [$streamId, $userId],
                0
            );
            $students = (int)Database::value(
                'SELECT COUNT(DISTINCT gs.student_id) FROM group_students gs
                 JOIN study_groups g ON g.id = gs.group_id
                 WHERE g.stream_id = ? AND g.teacher_id = ? AND gs.status = \'active\'',
                [$streamId, $userId],
                0
            );
            return [
                ['label' => 'групп', 'value' => (string)$groups],
                ['label' => 'проведённых занятий', 'value' => (string)$reports],
                ['label' => 'учеников', 'value' => (string)$students],
            ];
        }

        $groups = (int)Database::value(
            'SELECT COUNT(*) FROM study_groups g JOIN streams s ON s.id = g.stream_id
             WHERE g.stream_id = ? AND (g.curator_id = ? OR s.curator_id = ?)',
            [$streamId, $userId, $userId],
            0
        );
        $students = (int)Database::value(
            'SELECT COUNT(DISTINCT gs.student_id) FROM group_students gs
             JOIN study_groups g ON g.id = gs.group_id
             JOIN streams s ON s.id = g.stream_id
             WHERE g.stream_id = ? AND (g.curator_id = ? OR s.curator_id = ?) AND gs.status = \'active\'',
            [$streamId, $userId, $userId],
            0
        );

        return [
            ['label' => 'групп под кураторством', 'value' => (string)$groups],
            ['label' => 'учеников', 'value' => (string)$students],
        ];
    }
}
