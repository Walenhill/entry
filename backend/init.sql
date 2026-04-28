-- Создание таблицы slots
CREATE TABLE IF NOT EXISTS slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    time VARCHAR(10) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Свободно',
    name VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    service VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Добавление тестовых данных
INSERT INTO slots (time, status) VALUES 
('10:00', 'Свободно'),
('11:00', 'Свободно'),
('12:00', 'Свободно'),
('13:00', 'Свободно'),
('14:00', 'Свободно'),
('15:00', 'Свободно'),
('16:00', 'Свободно'),
('17:00', 'Свободно'),
('18:00', 'Свободно');
