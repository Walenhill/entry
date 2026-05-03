-- Таблица сотрудников (специалистов)
CREATE TABLE IF NOT EXISTS staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role ENUM('owner', 'staff') DEFAULT 'staff',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица услуг
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    duration_minutes INT NOT NULL DEFAULT 60,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Создание таблицы slots с поддержкой статусов, клиентов и связями с сотрудниками/услугами
CREATE TABLE IF NOT EXISTS slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    service_id INT,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    description VARCHAR(255),
    status ENUM('available', 'booked', 'cancelled') DEFAULT 'available',
    client_name VARCHAR(100),
    client_phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    INDEX idx_start_time (start_time),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица попыток входа (для защиты от брутфорса)
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, attempt_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица настроек (хранение хеша пароля админа)
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Добавление базового специалиста (Владельца)
INSERT INTO staff (name, role) VALUES ('Основной специалист', 'owner');

-- Добавление базовых услуг
INSERT INTO services (name, description, duration_minutes, price) VALUES
('Стандартная консультация', 'Базовая услуга по умолчанию', 60, 1000.00),
('Краткая встреча', 'Быстрый созвон или осмотр', 30, 500.00);

-- Добавление тестовых данных (свободные слоты для Основного специалиста и Стандартной консультации)
INSERT INTO slots (staff_id, service_id, start_time, end_time, description, status) VALUES
(1, 1, '2024-06-01 10:00:00', '2024-06-01 11:00:00', 'Утренний слот', 'available'),
(1, 1, '2024-06-01 11:00:00', '2024-06-01 12:00:00', 'Слот перед обедом', 'available'),
(1, 1, '2024-06-01 12:00:00', '2024-06-01 13:00:00', 'Обеденный слот', 'available'),
(1, 1, '2024-06-01 13:00:00', '2024-06-01 14:00:00', 'Послеобеденный слот', 'available');
