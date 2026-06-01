-- CardioPredict - Schema MySQL
-- Importer ce fichier dans une base MySQL/MariaDB via phpMyAdmin ou la ligne de commande.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS blood_pressure_history;
DROP TABLE IF EXISTS calorie_history;
DROP TABLE IF EXISTS bmi_history;
DROP TABLE IF EXISTS prediction_history;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(80) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE prediction_history (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    mode VARCHAR(20) NOT NULL,
    model_name VARCHAR(120) NULL,
    probability DECIMAL(6,5) NULL,
    risk_label VARCHAR(80) NULL,
    prediction_label VARCHAR(160) NULL,
    age DECIMAL(5,1) NULL,
    gender VARCHAR(30) NULL,
    weight DECIMAL(6,2) NULL,
    height DECIMAL(6,2) NULL,
    ap_hi INT NULL,
    ap_lo INT NULL,
    cholesterol TINYINT NULL,
    gluc TINYINT NULL,
    PRIMARY KEY (id),
    KEY idx_prediction_history_user_created (user_id, created_at),
    CONSTRAINT fk_prediction_history_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bmi_history (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    weight DECIMAL(6,2) NOT NULL,
    height DECIMAL(6,2) NOT NULL,
    bmi DECIMAL(5,2) NOT NULL,
    category VARCHAR(80) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_bmi_history_user_created (user_id, created_at),
    CONSTRAINT fk_bmi_history_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE calorie_history (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    age INT NOT NULL,
    gender VARCHAR(30) NOT NULL,
    weight DECIMAL(6,2) NOT NULL,
    height DECIMAL(6,2) NOT NULL,
    activity_level VARCHAR(40) NOT NULL,
    bmr DECIMAL(8,2) NOT NULL,
    daily_calories DECIMAL(8,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_calorie_history_user_created (user_id, created_at),
    CONSTRAINT fk_calorie_history_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blood_pressure_history (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ap_hi INT NOT NULL,
    ap_lo INT NOT NULL,
    pulse_pressure INT NOT NULL,
    mean_arterial_pressure DECIMAL(6,2) NOT NULL,
    category VARCHAR(80) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_blood_pressure_history_user_created (user_id, created_at),
    CONSTRAINT fk_blood_pressure_history_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
