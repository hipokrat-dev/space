-- Run once in Hostinger phpMyAdmin against the selected database.
CREATE TABLE IF NOT EXISTS learners (
 id CHAR(64) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS lessons (
 learner_id CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 lesson_id VARCHAR(32) NOT NULL,
 completed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (learner_id, lesson_id),
 CONSTRAINT lessons_learner_fk FOREIGN KEY (learner_id) REFERENCES learners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS attempts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 learner_id CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 score SMALLINT UNSIGNED NOT NULL,
 answers JSON NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX attempts_learner_date (learner_id,created_at),
 CONSTRAINT attempts_learner_fk FOREIGN KEY (learner_id) REFERENCES learners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
