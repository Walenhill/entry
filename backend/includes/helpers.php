<?php
/**
 * Database connection helper
 */

function getDbConnection() {
    static $conn = null;
    
    if ($conn === null) {
        $host = getenv('DB_HOST') ?: 'localhost';
        $user = getenv('DB_USERNAME') ?: 'root';
        $pass = getenv('DB_PASSWORD') ?: '';
        $name = getenv('DB_NAME') ?: 'booking_system';
        
        $conn = new mysqli($host, $user, $pass, $name);
        
        if ($conn->connect_error) {
            // Log the actual error for debugging, but don't expose it to the user
            error_log('Database connection failed: ' . $conn->connect_error);
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }
        
        $conn->set_charset("utf8mb4");
    }
    
    return $conn;
}

/**
 * Send JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Get JSON input from request
 */
function getInput() {
    $input = file_get_contents('php://input');
    $decoded = json_decode($input, true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * Sanitize input string
 */
function sanitizeInput($input) {
    if (!is_string($input)) {
        return '';
    }
    return trim(strip_tags($input));
}

/**
 * Validate datetime format (YYYY-MM-DD HH:MM:SS)
 */
function validateDateTime($datetime) {
    if (!$datetime || !is_string($datetime)) return false;
    $format = 'Y-m-d H:i:s';
    $d = DateTime::createFromFormat($format, $datetime);
    return $d && $d->format($format) === $datetime;
}

/**
 * Get request path without /api/ prefix
 */
function getRequestPath() {
    $request = $_SERVER['REQUEST_URI'] ?? '/';
    $parsedUrl = parse_url($request);
    $path = $parsedUrl['path'] ?? '/';
    
    // Remove /api/ prefix if present
    if (strpos($path, '/api/') === 0) {
        $path = substr($path, 5);
    }
    
    return $path;
}

/**
 * Get query parameters from URL
 */
function getQueryParams() {
    $request = $_SERVER['REQUEST_URI'] ?? '/';
    $parsedUrl = parse_url($request);
    $queryParams = [];
    
    if (isset($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $queryParams);
    }
    
    return $queryParams;
}

/**
 * Send a message via Telegram Bot API
 */
function sendTelegramMessage($chatId, $message) {
    $botToken = getenv('BOT_TOKEN');
    if (!$botToken || !$chatId) {
        return false;
    }

    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

    $data = [
        'chat_id' => (string)$chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
            'ignore_errors' => true // Prevent file_get_contents from returning false on 400/500 errors
        ]
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === false) {
        error_log("Telegram API request failed.");
        return false;
    }

    $response = json_decode($result, true);
    if (!$response['ok']) {
        error_log("Telegram API Error: " . ($response['description'] ?? 'Unknown error'));
        return false;
    }

    return true;
}
