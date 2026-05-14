<?php
/**
 * Authentication helper with modern security standards
 * - password_hash() с Argon2id
 * - Сессионная авторизация
 * - Rate limiting (защита от брутфорса)
 */

/**
 * Проверка блокировки IP после неудачных попыток входа
 * Возвращает true, если IP заблокирован
 */
function isIpBlocked($ipAddress) {
    $conn = getDbConnection();
    
    // Количество неудачных попыток за последние 15 минут
    $maxAttempts = 5;
    $blockDuration = 15; // минут
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as attempts 
        FROM login_attempts 
        WHERE ip_address = ? 
        AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE)
    ");
    $stmt->bind_param("si", $ipAddress, $blockDuration);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $result['attempts'] >= $maxAttempts;
}

/**
 * Запись попытки входа в БД
 */
function logLoginAttempt($ipAddress, $success) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("INSERT INTO login_attempts (ip_address, attempt_time) VALUES (?, NOW())");
    $stmt->bind_param("s", $ipAddress);
    $stmt->execute();
    $stmt->close();
    
    // Очистка старых записей (старше 1 часа)
    $conn->query("DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
}

/**
 * Очистка истории попыток для успешного входа
 */
function clearLoginAttempts($ipAddress) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
    $stmt->bind_param("s", $ipAddress);
    $stmt->execute();
    $stmt->close();
}

/**
 * Хэширование пароля с использованием Argon2id
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 1
    ]);
}

/**
 * Проверка пароля
 */
function verifyPassword($password, $hash) {
    if (!is_string($password)) {
        return false;
    }
    return password_verify($password, $hash);
}

/**
 * Получение хеша пароля админа из БД
 */
function getAdminPasswordHash() {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $key = 'admin_password_hash';
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    if ($row = $result->fetch_assoc()) {
        return $row['setting_value'];
    }
    
    return null;
}

/**
 * Установка хеша пароля админа
 */
function setAdminPasswordHash($hash) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("
        INSERT INTO settings (setting_key, setting_value) 
        VALUES ('admin_password_hash', ?)
        ON DUPLICATE KEY UPDATE setting_value = ?
    ");
    $stmt->bind_param("ss", $hash, $hash);
    $stmt->execute();
    $stmt->close();
}

/**
 * Инициализация сессии с безопасными настройками
 */
function initSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Безопасные настройки сессии
        ini_set('session.cookie_httponly', 1);

        // Dynamically set secure cookie flag based on connection protocol
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        ini_set('session.cookie_secure', $isSecure ? 1 : 0);

        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        
        session_start();
    }
}

/**
 * Проверка авторизации админа через сессию
 */
function checkAdminAuth() {
    initSecureSession();
    
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        jsonResponse(['error' => 'Authorization required'], 401);
    }
    
    // Проверка актуальности сессии (опционально можно добавить время жизни)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
        session_destroy();
        jsonResponse(['error' => 'Session expired'], 401);
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Вход админа
 */
function loginAdmin($password) {
    initSecureSession();
    
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Если REMOTE_ADDR - это локальный/частный IP (например, Docker-прокси),
    // пытаемся получить реальный IP клиента из заголовка X-Forwarded-For.
    if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            // To prevent IP spoofing without knowing exact trusted proxy topology,
            // we ONLY trust the rightmost IP in the X-Forwarded-For chain.
            // This is the IP appended by the reverse proxy that connected directly to us.
            // Any IPs to the left of it were provided by the client and cannot be trusted.
            $rightmostIp = trim(end($ips));

            if (filter_var($rightmostIp, FILTER_VALIDATE_IP)) {
                $ipAddress = $rightmostIp;
            }
        }
    }

    // Проверка блокировки IP
    if (isIpBlocked($ipAddress)) {
        return ['success' => false, 'error' => 'Too many failed attempts. Try again later.'];
    }
    
    $storedHash = getAdminPasswordHash();
    
    // Если хеш еще не установлен, используем пароль из переменных окружения
    if (!$storedHash) {
        $defaultPassword = getenv('MASTER_PASSWORD');
        if (!$defaultPassword) {
            return ['success' => false, 'error' => 'Server configuration error: MASTER_PASSWORD is not set.'];
        }
        if ($defaultPassword === 'admin123') {
            return ['success' => false, 'error' => 'Security error: Please change the default MASTER_PASSWORD to a secure value.'];
        }
        $storedHash = hashPassword($defaultPassword);
        setAdminPasswordHash($storedHash);
    }
    
    if (verifyPassword($password, $storedHash)) {
        // Успешный вход
        clearLoginAttempts($ipAddress);
        
        // Регенерация ID сессии для защиты от фиксации
        session_regenerate_id(true);
        
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['last_activity'] = time();
        
        return ['success' => true];
    } else {
        // Неудачная попытка
        logLoginAttempt($ipAddress, false);
        return ['success' => false, 'error' => 'Invalid password'];
    }
}

/**
 * Авторизация через Telegram
 */
function loginTelegram($initData) {
    if (empty($initData)) {
        return ['success' => false, 'error' => 'No initData provided'];
    }

    $botToken = getenv('BOT_TOKEN');
    if (!$botToken) {
        return ['success' => false, 'error' => 'Server configuration error: BOT_TOKEN is not set'];
    }

    // Парсим initData
    parse_str($initData, $parsedData);
    if (!isset($parsedData['hash'])) {
        return ['success' => false, 'error' => 'Invalid initData format'];
    }

    $hash = $parsedData['hash'];
    unset($parsedData['hash']);

    // Сортируем ключи в алфавитном порядке
    ksort($parsedData);

    // Формируем строку данных
    $dataCheckString = [];
    foreach ($parsedData as $key => $value) {
        $dataCheckString[] = "$key=$value";
    }
    $dataCheckString = implode("\n", $dataCheckString);

    // Вычисляем секретный ключ (HMAC-SHA256 токена бота с ключом "WebAppData")
    $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);

    // Вычисляем подпись данных
    $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

    // Сравниваем хеши
    if (!hash_equals($hash, $calculatedHash)) {
        return ['success' => false, 'error' => 'Data is NOT from Telegram'];
    }

    // Проверка актуальности данных (не старше 24 часов)
    if (isset($parsedData['auth_date']) && (time() - $parsedData['auth_date'] > 86400)) {
         return ['success' => false, 'error' => 'Data is outdated'];
    }

    // Данные подлинные. Извлекаем инфо пользователя
    $user = json_decode($parsedData['user'] ?? '{}', true);
    $userId = $user['id'] ?? null;

    if (!$userId) {
         return ['success' => false, 'error' => 'No user ID found in initData'];
    }

    // Проверяем, является ли пользователь админом
    $adminIdsStr = getenv('ADMIN_TG_IDS') ?: '';
    $adminIds = array_map('trim', explode(',', $adminIdsStr));

    $isAdmin = in_array((string)$userId, $adminIds, true);

    if ($isAdmin) {
        initSecureSession();
        // Регенерация ID сессии
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['last_activity'] = time();
    }

    return [
        'success' => true,
        'isAdmin' => $isAdmin,
        'user' => $user
    ];
}

/**
 * Выход админа
 */
function logoutAdmin() {
    initSecureSession();
    session_destroy();
    return ['success' => true];
}


