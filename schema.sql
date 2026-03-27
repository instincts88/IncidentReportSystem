-- =============================================
-- Incident Report System — Database Schema v2
-- =============================================

CREATE DATABASE IF NOT EXISTS incident_report_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE incident_report_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120)  NOT NULL,
    email       VARCHAR(180)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    role        ENUM('user','manager','admin') NOT NULL DEFAULT 'user',
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    last_login  DATETIME,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role  (role)
) ENGINE=InnoDB;

-- Incidents table
CREATE TABLE IF NOT EXISTS incidents (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(200)  NOT NULL,
    description  TEXT          NOT NULL,
    priority     ENUM('critical','high','medium','low') NOT NULL DEFAULT 'medium',
    status       ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
    category     VARCHAR(80),
    location     VARCHAR(120),
    reported_by  INT UNSIGNED  NOT NULL,
    assigned_to  INT UNSIGNED,
    created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at  DATETIME,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status   (status),
    INDEX idx_priority (priority),
    INDEX idx_reporter (reported_by),
    INDEX idx_assignee (assigned_to),
    FULLTEXT idx_search (title, description)
) ENGINE=InnoDB;

-- Comments table
CREATE TABLE IF NOT EXISTS comments (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    incident_id  INT UNSIGNED NOT NULL,
    user_id      INT UNSIGNED NOT NULL,
    content      TEXT         NOT NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)     REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_incident (incident_id)
) ENGINE=InnoDB;

-- Activity log
CREATE TABLE IF NOT EXISTS activity_log (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    incident_id  INT UNSIGNED NOT NULL,
    user_id      INT UNSIGNED,
    action       VARCHAR(50)  NOT NULL,
    description  VARCHAR(255),
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
    INDEX idx_incident (incident_id)
) ENGINE=InnoDB;

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL,
    incident_id  INT UNSIGNED,
    message      VARCHAR(255) NOT NULL,
    icon         VARCHAR(50)  DEFAULT 'bell',
    url          VARCHAR(255),
    is_read      TINYINT(1)   NOT NULL DEFAULT 0,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user   (user_id),
    INDEX idx_unread (user_id, is_read)
) ENGINE=InnoDB;

-- Default admin user (password: Admin@1234)
INSERT IGNORE INTO users (name, email, password, role, is_active)
VALUES ('Administrator', 'admin@example.com',
    '$2y$12$eImiTXuWVxfM37uY4JANjQ==',  -- placeholder, run setup.php to set real hash
    'admin', 1);
