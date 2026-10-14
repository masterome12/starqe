-- ============================================================
-- STARQE v2.0 — Full Database Schema
-- Run in phpMyAdmin after creating database: starqe_db
-- ============================================================
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone="+08:00";

CREATE DATABASE IF NOT EXISTS `starqe_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `starqe_db`;

CREATE TABLE IF NOT EXISTS `users` (
  `user_id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `fullname`        VARCHAR(120)     NOT NULL,
  `email`           VARCHAR(180)     NOT NULL,
  `password_hash`   VARCHAR(255)     NOT NULL,
  `role`            ENUM('admin','user') NOT NULL DEFAULT 'user',
  `status`          ENUM('enabled','disabled') NOT NULL DEFAULT 'enabled',
  `profile_picture` VARCHAR(255)     DEFAULT NULL,
  `remember_token`  VARCHAR(64)      DEFAULT NULL,
  `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `stations` (
  `station_id`      INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `station_name`    VARCHAR(180)     NOT NULL,
  `address`         VARCHAR(255)     NOT NULL,
  `city`            VARCHAR(100)     NOT NULL,
  `latitude`        DECIMAL(10,8)    NOT NULL,
  `longitude`       DECIMAL(11,8)    NOT NULL,
  `status`          ENUM('available','busy','maintenance','full') NOT NULL DEFAULT 'available',
  `charging_type`   ENUM('AC','DC','AC/DC')  NOT NULL DEFAULT 'AC',
  `power_capacity`  DECIMAL(6,2)     NOT NULL DEFAULT 7.40,
  `available_ports` TINYINT UNSIGNED NOT NULL DEFAULT 2,
  `total_ports`     TINYINT UNSIGNED NOT NULL DEFAULT 2,
  `price_per_kwh`   DECIMAL(6,2)     NOT NULL DEFAULT 12.00,
  `operator`        VARCHAR(150)     DEFAULT NULL,
  `opening_time`    TIME             DEFAULT '06:00:00',
  `closing_time`    TIME             DEFAULT '22:00:00',
  `image`           VARCHAR(255)     DEFAULT NULL,
  `is_verified`     TINYINT(1)       NOT NULL DEFAULT 0,
  `is_fast_charge`  TINYINT(1)       NOT NULL DEFAULT 0,
  `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`station_id`),
  KEY `idx_status` (`status`),
  KEY `idx_city`   (`city`),
  KEY `idx_type`   (`charging_type`),
  KEY `idx_latlng` (`latitude`,`longitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bookings` (
  `booking_id`     INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED     NOT NULL,
  `station_id`     INT UNSIGNED     NOT NULL,
  `booking_date`   DATE             NOT NULL,
  `booking_time`   TIME             NOT NULL,
  `duration_hours` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `total_amount`   DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
  `status`         ENUM('pending','confirmed','approved','rejected','cancelled','completed') NOT NULL DEFAULT 'pending',
  `reference_no`   VARCHAR(20)      NOT NULL,
  `notes`          TEXT             DEFAULT NULL,
  `admin_note`     TEXT             DEFAULT NULL,
  `created_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`booking_id`),
  UNIQUE KEY `uq_ref` (`reference_no`),
  KEY `idx_user`    (`user_id`),
  KEY `idx_station` (`station_id`),
  KEY `idx_status`  (`status`),
  KEY `idx_date`    (`booking_date`),
  CONSTRAINT `fk_b_user`    FOREIGN KEY (`user_id`)    REFERENCES `users`    (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_b_station` FOREIGN KEY (`station_id`) REFERENCES `stations` (`station_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `favorites` (
  `favorite_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED NOT NULL,
  `station_id`  INT UNSIGNED NOT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`favorite_id`),
  UNIQUE KEY `uq_fav` (`user_id`,`station_id`),
  CONSTRAINT `fk_f_user`    FOREIGN KEY (`user_id`)    REFERENCES `users`    (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_f_station` FOREIGN KEY (`station_id`) REFERENCES `stations` (`station_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reports` (
  `report_id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED NOT NULL,
  `station_id`  INT UNSIGNED NOT NULL,
  `report_type` ENUM('full','offline','damaged','wrong_info','other') NOT NULL DEFAULT 'other',
  `remarks`     TEXT         DEFAULT NULL,
  `status`      ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_note`  TEXT         DEFAULT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`report_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_r_user`    FOREIGN KEY (`user_id`)    REFERENCES `users`    (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_r_station` FOREIGN KEY (`station_id`) REFERENCES `stations` (`station_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `history` (
  `history_id`  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED DEFAULT NULL,
  `action`      VARCHAR(100) NOT NULL,
  `description` TEXT         NOT NULL,
  `ip_address`  VARCHAR(45)  DEFAULT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `idx_uid` (`user_id`),
  CONSTRAINT `fk_h_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notifications` (
  `notif_id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `title`      VARCHAR(120) NOT NULL,
  `message`    TEXT         NOT NULL,
  `type`       VARCHAR(30)  NOT NULL DEFAULT 'info',
  `is_read`    TINYINT(1)   NOT NULL DEFAULT 0,
  `link`       VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notif_id`),
  KEY `idx_uid`    (`user_id`),
  KEY `idx_unread` (`user_id`,`is_read`),
  CONSTRAINT `fk_n_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings` (
  `setting_id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `site_name`            VARCHAR(100) NOT NULL DEFAULT 'STARQE',
  `contact_email`        VARCHAR(180) NOT NULL DEFAULT 'support@starqe.ph',
  `default_price`        DECIMAL(6,2) NOT NULL DEFAULT 12.00,
  `maintenance_mode`     TINYINT(1)   NOT NULL DEFAULT 0,
  `booking_advance_days` TINYINT      NOT NULL DEFAULT 7,
  `max_booking_hours`    TINYINT      NOT NULL DEFAULT 8,
  `updated_at`           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── SEED DATA ─────────────────────────────────────────────────
-- Default password for ALL users: password
INSERT INTO `users` (`fullname`,`email`,`password_hash`,`role`,`status`) VALUES
('STARQE Admin',  'admin@starqe.ph',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin','enabled'),
('Juan dela Cruz','juan@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','user', 'enabled'),
('Maria Santos',  'maria@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','user', 'enabled');

INSERT INTO `stations` (`station_name`,`address`,`city`,`latitude`,`longitude`,`status`,`charging_type`,`power_capacity`,`available_ports`,`total_ports`,`price_per_kwh`,`operator`,`opening_time`,`closing_time`,`is_verified`,`is_fast_charge`) VALUES
('AC Mobility – Glorietta 4',       'Glorietta 4, Ayala Ave',               'Makati',      14.55154, 121.02322,'available','AC',    22.00,4,4,14.00,'AC Mobility',   '06:00','22:00',1,0),
('AC Mobility – Greenbelt 5',       'Greenbelt 5, Esperanza St',            'Makati',      14.55020, 121.02120,'available','AC/DC', 50.00,2,4,18.00,'AC Mobility',   '06:00','22:00',1,1),
('EVIDA – Ayala Ave',               'Ayala Ave cor Paseo de Roxas',         'Makati',      14.55400, 121.02480,'busy',     'DC',    60.00,1,2,22.00,'EVIDA',         '00:00','23:59',1,1),
('Shell Recharge – Makati',         'Shell Station, Chino Roces Ave',       'Makati',      14.55800, 121.01900,'available','AC',     7.40,2,2,12.00,'Shell Recharge','06:00','22:00',1,0),
('AC Mobility – SM Aura',           'SM Aura, McKinley Pkwy, BGC',          'Taguig',      14.54660, 121.05280,'available','AC/DC', 50.00,3,6,16.00,'AC Mobility',   '08:00','22:00',1,1),
('AC Mobility – One Bonifacio',     'One Bonifacio High St, BGC',           'Taguig',      14.54900, 121.04800,'full',     'AC',    22.00,0,4,14.00,'AC Mobility',   '08:00','22:00',1,0),
('Charge & Go – Market Market',     'Market! Market!, Fort Bonifacio',      'Taguig',      14.54100, 121.05100,'available','DC',   100.00,2,2,25.00,'Charge & Go',   '00:00','23:59',1,1),
('AC Mobility – Trinoma',           'Trinoma Mall, North Ave',              'Quezon City', 14.65600, 121.03400,'available','AC/DC', 50.00,4,6,15.00,'AC Mobility',   '08:00','22:00',1,1),
('AC Mobility – Vertis North',      'Ayala Malls Vertis North',             'Quezon City', 14.66600, 121.03200,'available','AC',    22.00,2,4,14.00,'AC Mobility',   '08:00','22:00',1,0),
('EVIDA – SM North EDSA',           'SM North EDSA, North Ave',             'Quezon City', 14.65600, 121.03200,'maintenance','DC',  60.00,0,2,20.00,'EVIDA',         '00:00','23:59',1,1),
('Meralco EV – QC Circle',          'Quezon Memorial Circle, Elliptical Rd','Quezon City', 14.65200, 121.04400,'available','AC',    11.00,3,4,12.00,'Meralco EV',   '06:00','22:00',1,0),
('AC Mobility – Robinsons Galleria','Robinson Galleria, EDSA cor Ortigas',  'Pasig',       14.58700, 121.05800,'available','AC/DC', 50.00,3,4,16.00,'AC Mobility',   '08:00','22:00',1,1),
('Shell Recharge – Ortigas',        'Shell Station, Ortigas Ave',           'Pasig',       14.58500, 121.06200,'busy',     'AC',     7.40,0,2,12.00,'Shell Recharge','06:00','22:00',1,0),
('AC Mobility – SM Megamall',       'SM Megamall, EDSA',                    'Mandaluyong', 14.58600, 121.05600,'available','AC/DC', 50.00,4,8,15.00,'AC Mobility',   '08:00','22:00',1,1),
('EVIDA – The Podium',              'The Podium, ADB Ave',                  'Mandaluyong', 14.58200, 121.06100,'available','DC',    60.00,2,2,20.00,'EVIDA',         '00:00','23:59',1,1),
('AC Mobility – SM Mall of Asia',   'SM MOA, J.W. Diokno Blvd',            'Pasay',       14.53490, 120.98230,'available','AC/DC', 50.00,4,8,15.00,'AC Mobility',   '08:00','22:00',1,1),
('AC Mobility – Ayala Malls Bay',   'Ayala Malls Manila Bay',               'Paranaque',   14.52400, 120.99300,'available','AC/DC', 50.00,3,6,16.00,'AC Mobility',   '08:00','22:00',1,1),
('NLEX Hub – South Luzon',          'Bicutan Exit, SLEX',                   'Paranaque',   14.49900, 121.00800,'available','DC',   150.00,2,4,30.00,'NLEX Corp',     '00:00','23:59',1,1),
('AC Mobility – Alabang TC',        'Alabang Town Center, Alabang',         'Muntinlupa',  14.41900, 121.03800,'available','AC/DC', 50.00,4,6,16.00,'AC Mobility',   '08:00','22:00',1,1),
('Shell Recharge – Filinvest',      'Filinvest City, Alabang',              'Muntinlupa',  14.41700, 121.04200,'available','AC',    22.00,2,4,14.00,'Shell Recharge','06:00','22:00',1,0),
('AC Mobility – SM Calamba',        'SM City Calamba, National Rd',         'Calamba',     14.20800, 121.16100,'available','AC/DC', 50.00,3,4,15.00,'AC Mobility',   '08:00','22:00',1,1),
('NLEX Hub – SLEX Sta. Rosa',       'SLEX Southbound, Sta. Rosa',           'Sta. Rosa',   14.28600, 121.11700,'available','DC',   150.00,2,4,28.00,'NLEX Corp',     '00:00','23:59',1,1),
('AC Mobility – SM Seaside Cebu',   'SM Seaside City, Antuwanga Ave',       'Cebu City',   10.28700, 123.88100,'available','AC/DC', 50.00,4,6,16.00,'AC Mobility',   '08:00','22:00',1,1),
('AC Mobility – Ayala Center Cebu', 'Ayala Center, Archbishop Reyes Ave',   'Cebu City',   10.31800, 123.90400,'available','AC',    22.00,2,4,14.00,'AC Mobility',   '08:00','22:00',1,0),
('AC Mobility – Abreeza Davao',     'Abreeza Mall, J.P. Laurel Ave',        'Davao City',   7.08200, 125.61500,'available','AC/DC', 50.00,3,4,15.00,'AC Mobility',   '08:00','22:00',1,1),
('ChargeNow – SM Lanang',           'SM City Lanang, JP Laurel Ave',        'Davao City',   7.10200, 125.63900,'available','DC',    60.00,2,2,22.00,'ChargeNow PH',  '00:00','23:59',1,1);

INSERT INTO `settings` (`site_name`,`contact_email`,`default_price`,`maintenance_mode`,`booking_advance_days`,`max_booking_hours`) VALUES
('STARQE','support@starqe.ph',12.00,0,7,8);

INSERT INTO `bookings` (`user_id`,`station_id`,`booking_date`,`booking_time`,`duration_hours`,`total_amount`,`status`,`reference_no`) VALUES
(2,1,CURDATE(),'10:00:00',2,308.00,'confirmed','STQ-DEMO001'),
(3,14,DATE_SUB(CURDATE(),INTERVAL 2 DAY),'09:00:00',3,1155.00,'completed','STQ-DEMO002');
