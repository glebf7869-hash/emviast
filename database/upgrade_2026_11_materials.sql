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
