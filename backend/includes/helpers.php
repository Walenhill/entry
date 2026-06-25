<?php
/**
 * Database connection helper
 */

function getDbConnection() {
    static $conn = null;
    
    if ($conn === null) {
        $host = getenv('DB_HOST') ?: 'localhost';

        // Performance optimization: Prefixing the host with 'p:' enables persistent connections in mysqli.
        // This simulates connection pooling, reducing TCP handshake and authentication latency for subsequent requests.
        if (strpos($host, 'p:') !== 0) {
            $host = 'p:' . $host;
        }

        $user = getenv('DB_USERNAME') ?: 'root';
        $pass = getenv('DB_PASSWORD') ?: '';
        $name = getenv('DB_NAME') ?: 'booking_system';
        
        try {
            $conn = new mysqli($host, $user, $pass, $name);
            $conn->set_charset("utf8mb4");
        } catch (\Throwable $e) {
            // Log the actual error for debugging, but don't expose it to the user
            // This prevents fatal errors in PHP 8.1+ which throw mysqli_sql_exception on connection failure
            error_log('Database connection failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }
        
        if ($conn->connect_error) {
            // Log the actual error for debugging, but don't expose it to the user
            error_log('Database connection failed: ' . $conn->connect_error);
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }
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
    // Restrict input payload to 1MB (1048576 bytes) to prevent memory exhaustion / DoS
    $input = file_get_contents('php://input', false, null, 0, 1048576);
    $decoded = json_decode($input, true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * Sanitize input string
 */
function sanitizeInput($input) {
    if (is_scalar($input)) {
        $input = (string)$input;
    }
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
