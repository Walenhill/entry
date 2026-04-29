<?php
// Загрузка переменных окружения из .env файла
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// Подключение к базе данных через переменные окружения
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: '';
$dbName = getenv('DB_DATABASE') ?: 'booking_db';

$connect = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($connect->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $connect->connect_error]);
    exit();
}

// Установка кодировки
$connect->set_charset("utf8mb4");

$request = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($request);
$path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';

// Убираем префикс /api/ если он есть
if (strpos($path, '/api/') === 0) {
    $path = substr($path, 5);
}

$method = $_SERVER['REQUEST_METHOD'];

// Получаем мастер-пароль из переменных окружения
$masterPassword = getenv('MASTER_PASSWORD') ?: 'master123';

// Простой секретный ключ для генерации токена (не хранить в коде в production!)
$secretKey = getenv('SECRET_KEY') ?: 'your-secret-key-change-in-production';

// Функция для проверки авторизации админа
function checkAdminAuth() {
    global $masterPassword, $secretKey;
    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    
    if (empty($authHeader) || strpos($authHeader, 'Bearer ') !== 0) {
        return false;
    }
    
    $token = substr($authHeader, 7);
    
    // Проверяем, что токен соответствует ожидаемому формату
    // В реальном проекте используйте JWT библиотеку
    $expectedToken = hash('sha256', $masterPassword . $secretKey);
    return $token === $expectedToken;
}

// Функция для генерации токена
function generateToken($password) {
    global $secretKey;
    return hash('sha256', $password . $secretKey);
}

// Функция для получения JSON тела запроса
function getJsonInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

// Функция валидации даты и времени
function validateDateTime($dateTimeStr) {
    if (!$dateTimeStr) return false;
    $format = 'Y-m-d H:i:s';
    $dt = DateTime::createFromFormat($format, $dateTimeStr);
    return $dt && $dt->format($format) === $dateTimeStr;
}

// Функция проверки пересечения слотов
function hasTimeOverlap($connect, $startTime, $endTime, $excludeId = null) {
    $sql = "SELECT id FROM slots WHERE status != 'cancelled' AND (
        (start_time < ? AND end_time > ?) OR
        (start_time < ? AND end_time > ?) OR
        (start_time >= ? AND end_time <= ?)
    )";
    
    $params = [$startTime, $startTime, $endTime, $endTime, $startTime, $endTime];
    $types = "ssssss";
    
    if ($excludeId !== null) {
        $sql .= " AND id != ?";
        $params[] = $excludeId;
        $types .= "i";
    }
    
    $stmt = $connect->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $hasOverlap = $result->num_rows > 0;
    $stmt->close();
    
    return $hasOverlap;
}

switch ($method) {
    case 'GET':
        // GET /slots - получение всех слотов (для админа) или только свободных (для клиентов)
        // GET /slots?date=2024-06-01 - фильтрация по дате
        if ($path === 'slots' || strpos($path, 'slots?') === 0) {
            header('Content-type: application/json');
            
            // Получаем параметры query string
            $queryParams = [];
            if (isset($parsedUrl['query'])) {
                parse_str($parsedUrl['query'], $queryParams);
            }
            
            $dateFilter = isset($queryParams['date']) ? $queryParams['date'] : null;
            $role = isset($queryParams['role']) ? $queryParams['role'] : 'client';
            
            // Для админа проверяем авторизацию
            $isAdmin = false;
            if ($role === 'admin') {
                $isAdmin = checkAdminAuth();
                if (!$isAdmin) {
                    http_response_code(401);
                    echo json_encode(['error' => 'Unauthorized']);
                    exit();
                }
            }
            
            // Формируем SQL запрос
            if ($isAdmin) {
                // Админ видит все слоты
                $sql = "SELECT id, start_time, end_time, description, status, client_name, client_phone, created_at FROM slots";
                $params = [];
                $types = "";
                
                if ($dateFilter) {
                    $sql .= " WHERE DATE(start_time) = ?";
                    $params[] = $dateFilter;
                    $types .= "s";
                }
                
                $sql .= " ORDER BY start_time ASC";
            } else {
                // Клиент видит только свободные слоты
                $sql = "SELECT id, start_time, end_time, description FROM slots WHERE status = 'available'";
                $params = [];
                $types = "";
                
                if ($dateFilter) {
                    $sql .= " AND DATE(start_time) = ?";
                    $params[] = $dateFilter;
                    $types .= "s";
                }
                
                $sql .= " ORDER BY start_time ASC";
            }
            
            $stmt = $connect->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $slots = [];
            while ($row = $result->fetch_assoc()) {
                $slots[] = $row;
            }
            
            echo json_encode($slots);
            $stmt->close();
        }
        break;
    
    case 'GET':
        // GET /stats - статистика (только админ)
        if ($path === 'stats') {
            header('Content-type: application/json');
            
            if (!checkAdminAuth()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit();
            }
            
            try {
                // Общая статистика
                $stmt = $pdo->query("SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) as booked,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                    FROM slots");
                $summary = $stmt->fetch(PDO::FETCH_ASSOC);

                // Загрузка в процентах
                $total = (int)$summary['total'];
                $booked = (int)$summary['booked'];
                $loadPercentage = $total > 0 ? round(($booked / $total) * 100, 1) : 0;

                // Топ клиентов
                $stmt = $pdo->query("SELECT client_name, client_phone, COUNT(*) as visits 
                    FROM slots 
                    WHERE status = 'booked' AND client_name IS NOT NULL
                    GROUP BY client_phone, client_name 
                    ORDER BY visits DESC 
                    LIMIT 5");
                $topClients = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode([
                    'success' => true,
                    'data' => [
                        'summary' => $summary,
                        'load_percentage' => $loadPercentage,
                        'top_clients' => $topClients
                    ]
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            }
            exit();
        }
        break;
        
    case 'POST':
        $jsonData = getJsonInput();
        
        // POST /slots/generate - генерация слотов по шаблону (только админ)
        if ($path === 'slots/generate') {
            header('Content-type: application/json');
            
            if (!checkAdminAuth()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit();
            }
            
            // Ожидаем: { "date": "2024-06-01", "start_hour": 10, "end_hour": 18, "duration": 60, "description": "Консультация" }
            $date = isset($jsonData['date']) ? $jsonData['date'] : null;
            $startHour = isset($jsonData['start_hour']) ? (int)$jsonData['start_hour'] : 9;
            $endHour = isset($jsonData['end_hour']) ? (int)$jsonData['end_hour'] : 18;
            $duration = isset($jsonData['duration']) ? (int)$jsonData['duration'] : 60;
            $description = isset($jsonData['description']) ? $jsonData['description'] : 'Слот';
            
            if (!$date) {
                http_response_code(400);
                echo json_encode(['error' => 'Date is required']);
                exit();
            }
            
            // Валидация формата даты
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid date format. Use YYYY-MM-DD']);
                exit();
            }
            
            // Валидация часов
            if ($startHour < 0 || $startHour > 23 || $endHour < 0 || $endHour > 23 || $startHour >= $endHour) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid hours. start_hour must be less than end_hour']);
                exit();
            }
            
            // Валидация длительности
            if ($duration <= 0 || $duration > 600) {
                http_response_code(400);
                echo json_encode(['error' => 'Duration must be between 1 and 600 minutes']);
                exit();
            }
            
            $createdCount = 0;
            $currentTime = strtotime("$date " . sprintf('%02d:00:00', $startHour));
            $endTime = strtotime("$date " . sprintf('%02d:00:00', $endHour));
            
            while ($currentTime + ($duration * 60) <= $endTime) {
                $startTime = date('Y-m-d H:i:s', $currentTime);
                $slotEndTime = date('Y-m-d H:i:s', $currentTime + ($duration * 60));
                
                // Проверка на пересечение перед созданием
                if (!hasTimeOverlap($connect, $startTime, $slotEndTime)) {
                    $stmt = $connect->prepare("INSERT INTO slots (start_time, end_time, description, status) VALUES (?, ?, ?, 'available')");
                    $stmt->bind_param("sss", $startTime, $slotEndTime, $description);
                    
                    if ($stmt->execute()) {
                        $createdCount++;
                    }
                    $stmt->close();
                }
                
                $currentTime += ($duration * 60);
            }
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => "Created $createdCount slots",
                'count' => $createdCount
            ]);
        }
        
        // POST /slots - создание одного слота (только админ)
        elseif ($path === 'slots') {
            header('Content-type: application/json');
            
            if (!checkAdminAuth()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit();
            }
            
            // Ожидаем: { "start_time": "2024-06-01 10:00:00", "end_time": "2024-06-01 11:00:00", "description": "Консультация" }
            $startTime = isset($jsonData['start_time']) ? $jsonData['start_time'] : null;
            $endTime = isset($jsonData['end_time']) ? $jsonData['end_time'] : null;
            $description = isset($jsonData['description']) ? $jsonData['description'] : '';
            
            if (!$startTime || !$endTime) {
                http_response_code(400);
                echo json_encode(['error' => 'start_time and end_time are required']);
                exit();
            }
            
            // Валидация формата даты и времени
            if (!validateDateTime($startTime) || !validateDateTime($endTime)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid date/time format. Use YYYY-MM-DD HH:MM:SS']);
                exit();
            }
            
            // Проверка: время начала должно быть меньше времени конца
            if (strtotime($startTime) >= strtotime($endTime)) {
                http_response_code(400);
                echo json_encode(['error' => 'start_time must be before end_time']);
                exit();
            }
            
            // Проверка на пересечение с существующими слотами
            if (hasTimeOverlap($connect, $startTime, $endTime)) {
                http_response_code(409);
                echo json_encode(['error' => 'Time slot overlaps with existing slot']);
                exit();
            }
            
            $stmt = $connect->prepare("INSERT INTO slots (start_time, end_time, description, status) VALUES (?, ?, ?, 'available')");
            $stmt->bind_param("sss", $startTime, $endTime, $description);
            
            if ($stmt->execute()) {
                $newId = $connect->insert_id;
                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'slot' => [
                        'id' => $newId,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'description' => $description,
                        'status' => 'available'
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create slot']);
            }
            $stmt->close();
        }
        
        // POST /slots/{id}/book - бронирование слота (клиент)
        elseif (preg_match('#^slots/(\d+)/book$#', $path, $matches)) {
            header('Content-type: application/json');
            
            $slotId = (int)$matches[1];
            
            // Ожидаем: { "client_name": "Иван", "client_phone": "+79991234567" }
            $clientName = isset($jsonData['client_name']) ? $jsonData['client_name'] : null;
            $clientPhone = isset($jsonData['client_phone']) ? $jsonData['client_phone'] : null;
            
            if (!$clientName || !$clientPhone) {
                http_response_code(400);
                echo json_encode(['error' => 'client_name and client_phone are required']);
                exit();
            }
            
            // Проверяем, что слот существует и свободен
            $checkStmt = $connect->prepare("SELECT id, status FROM slots WHERE id = ?");
            $checkStmt->bind_param("i", $slotId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Slot not found']);
                $checkStmt->close();
                exit();
            }
            
            $slot = $result->fetch_assoc();
            $checkStmt->close();
            
            if ($slot['status'] !== 'available') {
                http_response_code(409);
                echo json_encode(['error' => 'Slot is not available']);
                exit();
            }
            
            // Бронируем слот
            $bookStmt = $connect->prepare("UPDATE slots SET status = 'booked', client_name = ?, client_phone = ? WHERE id = ?");
            $bookStmt->bind_param("ssi", $clientName, $clientPhone, $slotId);
            
            if ($bookStmt->execute()) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Slot booked successfully',
                    'slot' => [
                        'id' => $slotId,
                        'status' => 'booked',
                        'client_name' => $clientName,
                        'client_phone' => $clientPhone
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to book slot']);
            }
            $bookStmt->close();
        }
        
        // POST /slots/{id}/cancel - отмена бронирования (только админ)
        elseif (preg_match('#^slots/(\d+)/cancel$#', $path, $matches)) {
            header('Content-type: application/json');
            
            if (!checkAdminAuth()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit();
            }
            
            $slotId = (int)$matches[1];
            
            // Проверяем, что слот существует и забронирован
            $checkStmt = $connect->prepare("SELECT id, status FROM slots WHERE id = ?");
            $checkStmt->bind_param("i", $slotId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Slot not found']);
                $checkStmt->close();
                exit();
            }
            
            $slot = $result->fetch_assoc();
            $checkStmt->close();
            
            if ($slot['status'] !== 'booked') {
                http_response_code(409);
                echo json_encode(['error' => 'Slot is not booked']);
                exit();
            }
            
            // Отменяем бронирование
            $cancelStmt = $connect->prepare("UPDATE slots SET status = 'cancelled' WHERE id = ?");
            $cancelStmt->bind_param("i", $slotId);
            
            if ($cancelStmt->execute()) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Booking cancelled successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to cancel booking']);
            }
            $cancelStmt->close();
        }
        
        // POST /auth/login - аутентификация админа
        elseif ($path === 'auth/login') {
            header('Content-type: application/json');
            
            $password = isset($jsonData['password']) ? $jsonData['password'] : '';
            
            if ($password === $masterPassword) {
                $token = generateToken($password);
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'token' => $token,
                    'message' => 'Login successful'
                ]);
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid password']);
            }
        }
        break;
        
    case 'DELETE':
        // DELETE /slots/{id} - удаление слота (только админ)
        if (preg_match('#^slots/(\d+)$#', $path, $matches)) {
            header('Content-type: application/json');
            
            if (!checkAdminAuth()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit();
            }
            
            $slotId = (int)$matches[1];
            
            $stmt = $connect->prepare("DELETE FROM slots WHERE id = ?");
            $stmt->bind_param("i", $slotId);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    http_response_code(200);
                    echo json_encode(['success' => true, 'message' => 'Slot deleted']);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Slot not found']);
                }
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete slot']);
            }
            $stmt->close();
        }
        break;
}

$connect->close();
?>
