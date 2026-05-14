<?php
/**
 * Main entry point - API Router
 * All requests are routed through this file
 */

// Load environment variables from .env file
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

// Set CORS headers for frontend access
$allowedOrigins = [
    'http://localhost:5173',
    'http://127.0.0.1:5173',
];

// Add dynamic frontend URL if defined in environment
$frontendUrl = getenv('FRONTEND_URL');
if ($frontendUrl && !in_array($frontendUrl, $allowedOrigins)) {
    $allowedOrigins[] = rtrim($frontendUrl, '/');
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Use strict matching to prevent type juggling bypasses
$matchedIndex = array_search($origin, $allowedOrigins, true);

if ($matchedIndex !== false) {
    // Reflect the strictly matched origin from our whitelist, not the client's input
    $safeOrigin = $allowedOrigins[$matchedIndex];
    header("Access-Control-Allow-Origin: $safeOrigin");
    header("Access-Control-Allow-Credentials: true");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Security Headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none';");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Route all requests to api.php
require_once __DIR__ . '/api.php';
