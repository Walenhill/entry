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
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
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
    return json_decode($input, true) ?? [];
}

/**
 * Check admin authorization
 */
function checkAdminAuth() {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    
    if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        jsonResponse(['error' => 'Authorization required'], 401);
    }
    
    $token = $matches[1];
    $secretKey = getenv('SECRET_KEY') ?: 'default_secret_key_change_me';
    $masterPassword = getenv('MASTER_PASSWORD') ?: 'admin123';
    $expectedToken = hash('sha256', $masterPassword . $secretKey);
    
    if ($token !== $expectedToken) {
        jsonResponse(['error' => 'Invalid token'], 403);
    }
    
    return true;
}

/**
 * Generate auth token
 */
function generateToken() {
    $password = getenv('MASTER_PASSWORD') ?: 'admin123';
    $secretKey = getenv('SECRET_KEY') ?: 'default_secret_key_change_me';
    return hash('sha256', $password . $secretKey);
}

/**
 * Validate datetime format (YYYY-MM-DD HH:MM:SS)
 */
function validateDateTime($datetime) {
    if (!$datetime) return false;
    $format = 'Y-m-d H:i:s';
    $d = DateTime::createFromFormat($format, $datetime);
    return $d && $d->format($format) === $datetime;
}

/**
 * Check if two time ranges overlap
 */
function hasTimeOverlap($start1, $end1, $start2, $end2) {
    return ($start1 < $end2) && ($start2 < $end1);
}

/**
 * Sanitize input string
 */
function sanitizeInput($input) {
    $conn = getDbConnection();
    return $conn->real_escape_string(trim($input));
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
