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
