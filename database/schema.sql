-- Tabellen für Forensite (Stand inkl. Migration 001_post_image.sql)
-- Frisch einrichten:
-- /Applications/XAMPP/xamppfiles/bin/mysql -uroot -e "CREATE DATABASE forensite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
-- /Applications/XAMPP/xamppfiles/bin/mysql -uroot forensite < database/schema.sql
-- Danach optional Testdaten: database/seed.sql

SET NAMES utf8mb4;

CREATE TABLE users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username      VARCHAR(30)  NOT NULL,
    email         VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY username (username),
    UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE forums (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(50)  NOT NULL,
    description TEXT         NULL,
    created_by  INT UNSIGNED NULL,   -- NULL, wenn der Ersteller gelöscht wurde
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY name (name),          -- Collation *_ci: "gaming" = "Gaming"
    KEY created_by (created_by),
    FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE forum_members (
    forum_id  INT UNSIGNED NOT NULL,
    user_id   INT UNSIGNED NOT NULL,
    role      ENUM('member', 'moderator') NOT NULL DEFAULT 'member',
    joined_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (forum_id, user_id),
    KEY user_id (user_id),
    FOREIGN KEY (forum_id) REFERENCES forums (id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)  REFERENCES users (id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE posts (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    forum_id   INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    title      VARCHAR(200) NOT NULL,
    body       TEXT         NOT NULL,
    image_path VARCHAR(255) NULL,   -- z. B. uploads/posts/<zufall>.jpg
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY idx_posts_forum_created (forum_id, created_at),
    FOREIGN KEY (forum_id) REFERENCES forums (id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)  REFERENCES users (id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comments (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    post_id    INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    parent_id  INT UNSIGNED NULL,   -- NULL = direkt auf den Post, sonst Antwort auf diesen Kommentar
    body       TEXT         NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY post_id (post_id),
    KEY user_id (user_id),
    KEY parent_id (parent_id),
    FOREIGN KEY (post_id)   REFERENCES posts (id)    ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users (id)    ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE votes (
    user_id INT UNSIGNED NOT NULL,
    post_id INT UNSIGNED NOT NULL,
    value   TINYINT      NOT NULL,   -- 1 = Upvote, -1 = Downvote
    PRIMARY KEY (user_id, post_id),  -- eine Stimme pro User und Post
    KEY post_id (post_id),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
