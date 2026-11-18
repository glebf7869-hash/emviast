<?php
declare(strict_types=1);

final class DashboardController extends Controller
{
    public function index(): void
    {
        $this->guardAuth();
        $user = $this->user();
        $role = $user['role'];
        $id = (int)$user['id'];

        $data = ['title' => 'Кабинет — ' . config('app.name')];
        $data['certificate'] = $this->certificateState($user);

        if ($role === 'root' || $role === 'admin') {
            $byRole = Database::fetchAll('SELECT role, COUNT(*) AS total FROM users GROUP BY role');
            $counts = ['root' => 0, 'admin' => 0, 'curator' => 0, 'teacher' => 0, 'smm' => 0, 'student' => 0];
            foreach ($byRole as $row) {
                $counts[$row['role']] = (int)$row['total'];
            }
            $data += [
                'counts' => $counts,
                'applicationsNew' => (int)Database::value("SELECT COUNT(*) FROM applications WHERE status = 'new'", [], 0),
                'reportsPending' => (int)Database::value("SELECT COUNT(*) FROM lesson_reports WHERE status = 'submitted'", [], 0),
                'openTasks' => (int)Database::value("SELECT COUNT(*) FROM smm_tasks WHERE status <> 'done'", [], 0),
                'streams' => Database::fetchAll(
                    "SELECT s.*, (SELECT COUNT(*) FROM study_groups g WHERE g.stream_id = s.id) AS groups_total
                     FROM streams s ORDER BY s.created_at DESC LIMIT 4"
                ),
                'recentApplications' => Database::fetchAll('SELECT * FROM applications ORDER BY created_at DESC LIMIT 5'),
                'upcoming' => Database::fetchAll(
                    "SELECT l.*, g.name AS group_name, u.first_name, u.last_name
                     FROM lessons l
                     JOIN study_groups g ON g.id = l.group_id
                     LEFT JOIN users u ON u.id = g.teacher_id
                     WHERE l.scheduled_at >= NOW() AND l.status = 'planned'
                     ORDER BY l.scheduled_at ASC LIMIT 5"
                ),
                'curators' => Database::fetchAll(
                    'SELECT u.id, u.first_name, u.last_name,
                            (SELECT COUNT(*) FROM streams s WHERE s.curator_id = u.id) AS streams_total,
                            (SELECT COUNT(*) FROM study_groups g WHERE g.curator_id = u.id) AS groups_total,
                            (SELECT COUNT(*) FROM lesson_reports r
                               JOIN lessons l ON l.id = r.lesson_id
                               JOIN study_groups g ON g.id = l.group_id
                               JOIN streams s ON s.id = g.stream_id
                             WHERE r.status = \'submitted\' AND (s.curator_id = u.id OR g.curator_id = u.id)) AS pending_total
                     FROM users u WHERE u.role = \'curator\' AND u.status = \'active\' ORDER BY u.last_name'
                ),
            ];
            $this->view('dashboard/admin', $data, 'app');
            return;
        }

        if ($role === 'curator') {
            $streams = Database::fetchAll(
                'SELECT DISTINCT s.* FROM streams s
                 LEFT JOIN study_groups g ON g.stream_id = s.id
                 WHERE s.curator_id = ? OR g.curator_id = ?
                 ORDER BY s.created_at DESC',
                [$id, $id]
            );
            $streamIds = array_map(static fn (array $s): int => (int)$s['id'], $streams);
            $in = $streamIds ? implode(',', array_fill(0, count($streamIds), '?')) : '0';

            $data += [
                'streams' => $streams,
                'groups' => Database::fetchAll(
                    "SELECT g.*, s.title AS stream_title FROM study_groups g
                     JOIN streams s ON s.id = g.stream_id
                     WHERE g.curator_id = ? OR s.curator_id = ?
                     ORDER BY s.created_at DESC, g.name",
                    [$id, $id]
                ),
                'applicationsNew' => (int)Database::value("SELECT COUNT(*) FROM applications WHERE status = 'new'", [], 0),
                'reportsPending' => $streamIds
                    ? (int)Database::value(
                        "SELECT COUNT(*) FROM lesson_reports r
                         JOIN lessons l ON l.id = r.lesson_id
                         JOIN study_groups g ON g.id = l.group_id
                         WHERE r.status = 'submitted' AND g.stream_id IN ($in)",
                        $streamIds,
                        0
                    )
                    : 0,
                'studentsTotal' => $streamIds
                    ? (int)Database::value(
                        "SELECT COUNT(DISTINCT gs.student_id) FROM group_students gs
                         JOIN study_groups g ON g.id = gs.group_id
                         WHERE gs.status = 'active' AND g.stream_id IN ($in)",
                        $streamIds,
                        0
                    )
                    : 0,
                'upcoming' => $streamIds
                    ? Database::fetchAll(
                        "SELECT l.*, g.name AS group_name, u.first_name, u.last_name
                         FROM lessons l
                         JOIN study_groups g ON g.id = l.group_id
                         LEFT JOIN users u ON u.id = g.teacher_id
                         WHERE l.scheduled_at >= NOW() AND g.stream_id IN ($in)
                         ORDER BY l.scheduled_at ASC LIMIT 5",
                        $streamIds
                    )
                    : [],
            ];
            $this->view('dashboard/curator', $data, 'app');
            return;
        }

        if ($role === 'teacher') {
            $weekParam = (string)($_GET['week'] ?? '');
            $weekStart = $weekParam !== '' ? strtotime($weekParam) : strtotime('monday this week');
            if ($weekStart === false) {
                $weekStart = strtotime('monday this week');
            }
            $weekStart = strtotime('monday', $weekStart);

            $weekLessons = Database::fetchAll(
                'SELECT l.*, g.name AS group_name FROM lessons l
                 JOIN study_groups g ON g.id = l.group_id
                 WHERE g.teacher_id = ? AND l.scheduled_at >= ? AND l.scheduled_at < ?
                 ORDER BY l.scheduled_at',
                [$id, date('Y-m-d 00:00:00', $weekStart), date('Y-m-d 00:00:00', strtotime('+7 days', $weekStart))]
            );
            $lessonsByDay = [];
            foreach ($weekLessons as $lesson) {
                $lessonsByDay[date('Y-m-d', strtotime($lesson['scheduled_at']))][] = $lesson;
            }

            $data += [
                'weekStart' => $weekStart,
                'weekDays' => array_map(static fn (int $i): int => strtotime('+' . $i . ' days', $weekStart), range(0, 6)),
                'lessonsByDay' => $lessonsByDay,
            ];
            $data += [
                'groups' => Database::fetchAll(
                    'SELECT g.*, s.title AS stream_title,
                            (SELECT COUNT(*) FROM group_students gs WHERE gs.group_id = g.id AND gs.status = \'active\') AS students_total
                     FROM study_groups g JOIN streams s ON s.id = g.stream_id
                     WHERE g.teacher_id = ? ORDER BY g.name',
                    [$id]
                ),
                'upcoming' => Database::fetchAll(
                    "SELECT l.*, g.name AS group_name FROM lessons l
                     JOIN study_groups g ON g.id = l.group_id
                     WHERE g.teacher_id = ? AND l.scheduled_at >= NOW() AND l.status = 'planned'
                     ORDER BY l.scheduled_at ASC LIMIT 6",
                    [$id]
                ),
                'toReport' => Database::fetchAll(
                    "SELECT l.*, g.name AS group_name, r.id AS report_id, r.status AS report_status
                     FROM lessons l
                     JOIN study_groups g ON g.id = l.group_id
                     LEFT JOIN lesson_reports r ON r.lesson_id = l.id
                     WHERE g.teacher_id = ? AND l.scheduled_at < NOW()
                       AND (r.id IS NULL OR r.status = 'returned' OR r.status = 'draft')
                     ORDER BY l.scheduled_at DESC LIMIT 5",
                    [$id]
                ),
                'toCheck' => (int)Database::value(
                    "SELECT COUNT(*) FROM homework_submissions hs
                     JOIN homework h ON h.id = hs.homework_id
                     JOIN study_groups g ON g.id = h.group_id
                     WHERE g.teacher_id = ? AND hs.status = 'submitted'",
                    [$id],
                    0
                ),
            ];
            $this->view('dashboard/teacher', $data, 'app');
            return;
        }

        if ($role === 'smm') {
            $data += [
                'myTasks' => Database::fetchAll(
                    'SELECT * FROM smm_tasks WHERE assignee_id = ? ORDER BY status, due_on IS NULL, due_on LIMIT 8',
                    [$id]
                ),
                'overdue' => (int)Database::value(
                    "SELECT COUNT(*) FROM smm_tasks WHERE assignee_id = ? AND status <> 'done' AND due_on IS NOT NULL AND due_on < CURDATE()",
                    [$id],
                    0
                ),
                'doneThisWeek' => (int)Database::value(
                    "SELECT COUNT(*) FROM smm_tasks WHERE assignee_id = ? AND status = 'done' AND done_at >= (NOW() - INTERVAL 7 DAY)",
                    [$id],
                    0
                ),
                'latestStats' => Database::fetchAll(
                    'SELECT s.* FROM smm_stats s
                     JOIN (SELECT platform, MAX(stat_date) AS d FROM smm_stats GROUP BY platform) m
                       ON m.platform = s.platform AND m.d = s.stat_date
                     ORDER BY s.platform'
                ),
                'drafts' => (int)Database::value("SELECT COUNT(*) FROM news WHERE status = 'draft'", [], 0),
            ];
            $this->view('dashboard/smm', $data, 'app');
            return;
        }

        // student
        $data += [
            'upcoming' => Database::fetchAll(
                "SELECT l.*, g.name AS group_name, s.title AS stream_title,
                        u.first_name AS teacher_first, u.last_name AS teacher_last
                 FROM lessons l
                 JOIN study_groups g ON g.id = l.group_id
                 JOIN streams s ON s.id = g.stream_id
                 JOIN group_students gs ON gs.group_id = g.id AND gs.student_id = ? AND gs.status = 'active'
                 LEFT JOIN users u ON u.id = g.teacher_id
                 WHERE l.scheduled_at >= NOW() AND l.status = 'planned'
                 ORDER BY l.scheduled_at ASC LIMIT 4",
                [$id]
            ),
            'homework' => Database::fetchAll(
                "SELECT h.*, g.name AS group_name, hs.status AS submission_status, hs.grade
                 FROM homework h
                 JOIN group_students gs ON gs.group_id = h.group_id AND gs.student_id = ? AND gs.status = 'active'
                 JOIN study_groups g ON g.id = h.group_id
                 LEFT JOIN homework_submissions hs ON hs.homework_id = h.id AND hs.student_id = ?
                 ORDER BY h.due_at IS NULL, h.due_at ASC LIMIT 5",
                [$id, $id]
            ),
            'progress' => Database::fetchAll(
                'SELECT p.*, g.name AS group_name, s.title AS stream_title
                 FROM student_progress p
                 JOIN study_groups g ON g.id = p.group_id
                 JOIN streams s ON s.id = g.stream_id
                 WHERE p.student_id = ?',
                [$id]
            ),
        ];
        $this->view('dashboard/student', $data, 'app');
    }

    /** @return array{state:string, stream:?array, ends_on:?string, certificate:?array} */
    private function certificateState(array $user): array
    {
        $id = (int)$user['id'];

        $stream = Database::fetch(
            'SELECT DISTINCT s.* FROM streams s
             LEFT JOIN study_groups g ON g.stream_id = s.id
             LEFT JOIN group_students gs ON gs.group_id = g.id AND gs.student_id = ? AND gs.status = \'active\'
             WHERE s.curator_id = ? OR g.teacher_id = ? OR g.curator_id = ? OR gs.student_id IS NOT NULL
             ORDER BY s.ends_on DESC, s.id DESC LIMIT 1',
            [$id, $id, $id, $id]
        );

        if ($stream === null) {
            return ['state' => 'none', 'stream' => null, 'ends_on' => null, 'certificate' => null];
        }

        if (!table_exists('certificates')) {
            return ['state' => 'none', 'stream' => null, 'ends_on' => null, 'certificate' => null];
        }

        $certificate = Database::fetch(
            'SELECT * FROM certificates WHERE user_id = ? AND stream_id = ?',
            [$id, (int)$stream['id']]
        );
        if ($certificate !== null) {
            return ['state' => 'issued', 'stream' => $stream, 'ends_on' => $stream['ends_on'], 'certificate' => $certificate];
        }

        $finished = !empty($stream['ends_on']) && strtotime((string)$stream['ends_on']) <= strtotime('today');

        return [
            'state' => $finished ? 'ready' : 'pending',
            'stream' => $stream,
            'ends_on' => $stream['ends_on'],
            'certificate' => null,
        ];
    }
}
