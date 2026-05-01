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
        ini_set('session.cookie_secure', 0); // Установить в 1 при использовании HTTPS
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
    
    // Проверка блокировки IP
    if (isIpBlocked($ipAddress)) {
        return ['success' => false, 'error' => 'Too many failed attempts. Try again later.'];
    }
    
    $storedHash = getAdminPasswordHash();
    
    // Если хеш еще не установлен, используем пароль по умолчанию
    if (!$storedHash) {
        $defaultPassword = getenv('MASTER_PASSWORD') ?: 'admin123';
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
 * Выход админа
 */
function logoutAdmin() {
    initSecureSession();
    session_destroy();
    return ['success' => true];
}

/**
 * Генерация CSRF токена для форм
 */
function generateCsrfToken() {
    initSecureSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Проверка CSRF токена
 */
function verifyCsrfToken($token) {
    initSecureSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
