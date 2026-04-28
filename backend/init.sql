-- Создание таблицы slots с поддержкой статусов и данных клиента
CREATE TABLE IF NOT EXISTS slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    description VARCHAR(255),
    status ENUM('available', 'booked', 'cancelled') DEFAULT 'available',
    client_name VARCHAR(100),
    client_phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_start_time (start_time),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Добавление тестовых данных (свободные слоты)
INSERT INTO slots (start_time, end_time, description, status) VALUES 
('2024-06-01 10:00:00', '2024-06-01 11:00:00', 'Утренний слот', 'available'),
('2024-06-01 11:00:00', '2024-06-01 12:00:00', 'Слот перед обедом', 'available'),
('2024-06-01 12:00:00', '2024-06-01 13:00:00', 'Обеденный слот', 'available'),
('2024-06-01 13:00:00', '2024-06-01 14:00:00', 'Послеобеденный слот', 'available'),
('2024-06-01 14:00:00', '2024-06-01 15:00:00', 'Дневной слот', 'available'),
('2024-06-01 15:00:00', '2024-06-01 16:00:00', 'Полуденный слот', 'available'),
('2024-06-01 16:00:00', '2024-06-01 17:00:00', 'Вечерний слот', 'available'),
('2024-06-01 17:00:00', '2024-06-01 18:00:00', 'Поздний слот', 'available'),
('2024-06-01 18:00:00', '2024-06-01 19:00:00', 'Завершающий слот', 'available');
