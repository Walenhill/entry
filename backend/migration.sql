-- Миграция для обновления структуры таблицы slots
-- Запустить только если таблица уже существует со старой структурой

-- Добавляем новые колонки если их нет
ALTER TABLE slots 
ADD COLUMN IF NOT EXISTS start_time DATETIME AFTER id,
ADD COLUMN IF NOT EXISTS end_time DATETIME AFTER start_time;

-- Переименовываем старые колонки
ALTER TABLE slots CHANGE COLUMN IF EXISTS time description VARCHAR(255);
ALTER TABLE slots CHANGE COLUMN IF EXISTS name client_name VARCHAR(100);
ALTER TABLE slots CHANGE COLUMN IF EXISTS phone client_phone VARCHAR(20);

-- Удаляем ненужную колонку service если есть
ALTER TABLE slots DROP COLUMN IF EXISTS service;

-- Меняем тип status на ENUM
ALTER TABLE slots MODIFY COLUMN status ENUM('available', 'booked', 'cancelled') DEFAULT 'available';

-- Обновляем существующие записи (конвертируем старые данные)
UPDATE slots SET 
    start_time = CONCAT(DATE(NOW()), ' ', time),
    end_time = CONCAT(DATE(NOW()), ' ', time)
WHERE start_time IS NULL AND time IS NOT NULL;

-- Добавляем индексы
CREATE INDEX IF NOT EXISTS idx_start_time ON slots(start_time);
CREATE INDEX IF NOT EXISTS idx_status ON slots(status);
