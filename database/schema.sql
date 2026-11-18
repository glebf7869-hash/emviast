-- EMVIA Study — схема базы данных (MySQL 8.0, utf8mb4)
-- Импорт: mysql -u USER -p DBNAME < database/schema.sql

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    role ENUM('root','admin','curator','teacher','smm','student') NOT NULL,
    login VARCHAR(64) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    phone VARCHAR(32) DEFAULT NULL,
    timezone VARCHAR(64) NOT NULL DEFAULT 'Europe/Moscow',
    status ENUM('active','blocked') NOT NULL DEFAULT 'active',
    must_change_password TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT UNSIGNED DEFAULT NULL,
    last_login_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_login (login),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role (role),
    CONSTRAINT fk_users_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS applications (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    type ENUM('student','teacher','curator','smm') NOT NULL,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(32) DEFAULT NULL,
    age TINYINT UNSIGNED DEFAULT NULL,
    timezone VARCHAR(64) DEFAULT NULL,
    level ENUM('A1','A2','B1','B2','C1') DEFAULT NULL,
    goal TEXT,
    experience TEXT,
    hours TINYINT UNSIGNED DEFAULT NULL,
    groups_ready TINYINT UNSIGNED DEFAULT NULL,
    schedule_days VARCHAR(60) DEFAULT NULL,
    schedule_time VARCHAR(120) DEFAULT NULL,
    message TEXT,
    status ENUM('new','in_review','approved','rejected','waitlist') NOT NULL DEFAULT 'new',
    source VARCHAR(64) DEFAULT NULL,
    reviewed_by INT UNSIGNED DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    review_comment VARCHAR(255) DEFAULT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_app_type_status (type, status),
    KEY idx_app_created (created_at),
    CONSTRAINT fk_app_reviewed FOREIGN KEY (reviewed_by) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT fk_app_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS streams (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(120) NOT NULL,
    language VARCHAR(40) NOT NULL DEFAULT 'Английский',
    level VARCHAR(16) NOT NULL DEFAULT 'A1–A2',
    starts_on DATE DEFAULT NULL,
    ends_on DATE DEFAULT NULL,
    lessons_total TINYINT UNSIGNED NOT NULL DEFAULT 12,
    status ENUM('draft','recruiting','active','finished') NOT NULL DEFAULT 'draft',
    curator_id INT UNSIGNED DEFAULT NULL,
    description TEXT,
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_streams_status (status),
    CONSTRAINT fk_streams_curator FOREIGN KEY (curator_id) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT fk_streams_author FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS study_groups (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    stream_id INT UNSIGNED NOT NULL,
    name VARCHAR(80) NOT NULL,
    teacher_id INT UNSIGNED DEFAULT NULL,
    curator_id INT UNSIGNED DEFAULT NULL,
    capacity TINYINT UNSIGNED NOT NULL DEFAULT 5,
    schedule_note VARCHAR(190) DEFAULT NULL,
    status ENUM('forming','active','finished') NOT NULL DEFAULT 'forming',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_groups_stream (stream_id),
    KEY idx_groups_teacher (teacher_id),
    CONSTRAINT fk_groups_stream FOREIGN KEY (stream_id) REFERENCES streams (id) ON DELETE CASCADE,
    CONSTRAINT fk_groups_teacher FOREIGN KEY (teacher_id) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT fk_groups_curator FOREIGN KEY (curator_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS group_students (
    group_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    status ENUM('active','left','waitlist') NOT NULL DEFAULT 'active',
    joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (group_id, student_id),
    KEY idx_group_students_student (student_id),
    CONSTRAINT fk_gs_group FOREIGN KEY (group_id) REFERENCES study_groups (id) ON DELETE CASCADE,
    CONSTRAINT fk_gs_student FOREIGN KEY (student_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lessons (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    group_id INT UNSIGNED NOT NULL,
    lesson_no SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    topic VARCHAR(190) DEFAULT NULL,
    scheduled_at DATETIME NOT NULL,
    duration_min SMALLINT UNSIGNED NOT NULL DEFAULT 60,
    meeting_url VARCHAR(255) DEFAULT NULL,
    status ENUM('planned','done','canceled') NOT NULL DEFAULT 'planned',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_lessons_group_date (group_id, scheduled_at),
    CONSTRAINT fk_lessons_group FOREIGN KEY (group_id) REFERENCES study_groups (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lesson_reports (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    lesson_id INT UNSIGNED NOT NULL,
    teacher_id INT UNSIGNED DEFAULT NULL,
    summary TEXT,
    homework_text TEXT,
    attendance_present TINYINT UNSIGNED NOT NULL DEFAULT 0,
    attendance_absent TINYINT UNSIGNED NOT NULL DEFAULT 0,
    photo_start VARCHAR(255) DEFAULT NULL,
    photo_start_at DATETIME DEFAULT NULL,
    photo_end VARCHAR(255) DEFAULT NULL,
    photo_end_at DATETIME DEFAULT NULL,
    issues TEXT,
    status ENUM('draft','submitted','approved','returned') NOT NULL DEFAULT 'draft',
    reviewed_by INT UNSIGNED DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    review_comment VARCHAR(255) DEFAULT NULL,
    submitted_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_report_lesson (lesson_id),
    KEY idx_reports_status (status),
    CONSTRAINT fk_reports_lesson FOREIGN KEY (lesson_id) REFERENCES lessons (id) ON DELETE CASCADE,
    CONSTRAINT fk_reports_teacher FOREIGN KEY (teacher_id) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT fk_reports_reviewer FOREIGN KEY (reviewed_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homework (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    group_id INT UNSIGNED NOT NULL,
    lesson_id INT UNSIGNED DEFAULT NULL,
    title VARCHAR(190) NOT NULL,
    description TEXT,
    due_at DATETIME DEFAULT NULL,
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_homework_group (group_id),
    CONSTRAINT fk_homework_group FOREIGN KEY (group_id) REFERENCES study_groups (id) ON DELETE CASCADE,
    CONSTRAINT fk_homework_lesson FOREIGN KEY (lesson_id) REFERENCES lessons (id) ON DELETE SET NULL,
    CONSTRAINT fk_homework_author FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homework_submissions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    homework_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    answer TEXT,
    status ENUM('assigned','submitted','checked') NOT NULL DEFAULT 'assigned',
    grade TINYINT UNSIGNED DEFAULT NULL,
    comment VARCHAR(255) DEFAULT NULL,
    submitted_at DATETIME DEFAULT NULL,
    checked_by INT UNSIGNED DEFAULT NULL,
    checked_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_submission (homework_id, student_id),
    CONSTRAINT fk_sub_homework FOREIGN KEY (homework_id) REFERENCES homework (id) ON DELETE CASCADE,
    CONSTRAINT fk_sub_student FOREIGN KEY (student_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_sub_checker FOREIGN KEY (checked_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS student_progress (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id INT UNSIGNED NOT NULL,
    group_id INT UNSIGNED NOT NULL,
    percent TINYINT UNSIGNED NOT NULL DEFAULT 0,
    comment VARCHAR(255) DEFAULT NULL,
    updated_by INT UNSIGNED DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_progress (student_id, group_id),
    CONSTRAINT fk_progress_student FOREIGN KEY (student_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_progress_group FOREIGN KEY (group_id) REFERENCES study_groups (id) ON DELETE CASCADE,
    CONSTRAINT fk_progress_author FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS smm_tasks (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(160) NOT NULL,
    description TEXT,
    platform VARCHAR(40) DEFAULT NULL,
    assignee_id INT UNSIGNED DEFAULT NULL,
    due_on DATE DEFAULT NULL,
    status ENUM('todo','in_progress','done') NOT NULL DEFAULT 'todo',
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    done_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_tasks_status (status),
    KEY idx_tasks_assignee (assignee_id),
    CONSTRAINT fk_tasks_assignee FOREIGN KEY (assignee_id) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT fk_tasks_author FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS smm_stats (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    platform VARCHAR(40) NOT NULL,
    stat_date DATE NOT NULL,
    followers INT UNSIGNED NOT NULL DEFAULT 0,
    reach INT UNSIGNED NOT NULL DEFAULT 0,
    likes INT UNSIGNED NOT NULL DEFAULT 0,
    comments INT UNSIGNED NOT NULL DEFAULT 0,
    notes VARCHAR(255) DEFAULT NULL,
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_stats (platform, stat_date),
    CONSTRAINT fk_stats_author FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS news (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug VARCHAR(160) NOT NULL,
    title VARCHAR(190) NOT NULL,
    title_en VARCHAR(190) DEFAULT NULL,
    excerpt VARCHAR(255) DEFAULT NULL,
    excerpt_en VARCHAR(255) DEFAULT NULL,
    body MEDIUMTEXT,
    body_en MEDIUMTEXT,
    cover VARCHAR(255) DEFAULT NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    published_at DATETIME DEFAULT NULL,
    author_id INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_news_slug (slug),
    KEY idx_news_status (status, published_at),
    CONSTRAINT fk_news_author FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_log (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED DEFAULT NULL,
    action VARCHAR(80) NOT NULL,
    entity VARCHAR(40) DEFAULT NULL,
    entity_id INT UNSIGNED NOT NULL DEFAULT 0,
    meta VARCHAR(255) DEFAULT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_log_created (created_at),
    KEY idx_log_user (user_id),
    CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    k VARCHAR(64) NOT NULL,
    v TEXT,
    PRIMARY KEY (k)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Добавляет каталог пособий и привязку пособий к группам.
-- Выполняется один раз (можно через upgrade.php или phpMyAdmin).

CREATE TABLE IF NOT EXISTS materials (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(160) NOT NULL,
    author VARCHAR(120) DEFAULT NULL,
    level VARCHAR(16) DEFAULT NULL,
    url VARCHAR(255) DEFAULT NULL,
    description TEXT,
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_materials_level (level),
    CONSTRAINT fk_materials_author FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS group_materials (
    group_id INT UNSIGNED NOT NULL,
    material_id INT UNSIGNED NOT NULL,
    note VARCHAR(190) DEFAULT NULL,
    added_by INT UNSIGNED DEFAULT NULL,
    added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (group_id, material_id),
    CONSTRAINT fk_gm_group FOREIGN KEY (group_id) REFERENCES study_groups (id) ON DELETE CASCADE,
    CONSTRAINT fk_gm_material FOREIGN KEY (material_id) REFERENCES materials (id) ON DELETE CASCADE,
    CONSTRAINT fk_gm_user FOREIGN KEY (added_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Каталог презентаций (Google Drive), документы правил и сертификаты.

CREATE TABLE IF NOT EXISTS resources (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(190) NOT NULL,
    kind ENUM('folder','file') NOT NULL DEFAULT 'folder',
    url VARCHAR(500) NOT NULL,
    drive_id VARCHAR(120) DEFAULT NULL,
    textbook VARCHAR(120) DEFAULT NULL,
    topic VARCHAR(120) DEFAULT NULL,
    level VARCHAR(16) DEFAULT NULL,
    description TEXT,
    access ENUM('staff','admins') NOT NULL DEFAULT 'staff',
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_resources_access (access),
    KEY idx_resources_textbook (textbook),
    CONSTRAINT fk_resources_author FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS documents (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(190) NOT NULL,
    url VARCHAR(500) DEFAULT NULL,
    file VARCHAR(255) DEFAULT NULL,
    description VARCHAR(255) DEFAULT NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_documents_author FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS certificates (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    stream_id INT UNSIGNED NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'student',
    number VARCHAR(40) NOT NULL,
    issued_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_certificate (user_id, stream_id),
    CONSTRAINT fk_cert_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_cert_stream FOREIGN KEY (stream_id) REFERENCES streams (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
