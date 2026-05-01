<?php
/**
 * API Routes and Controllers
 * This file handles all HTTP requests and routes them to appropriate CRUD functions
 */

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/slots_crud.php';
require_once __DIR__ . '/includes/auth.php';

// Get request details
$method = $_SERVER['REQUEST_METHOD'];
$path = getRequestPath();
$queryParams = getQueryParams();

// Route handling
switch ($method) {
    case 'GET':
        handleGetRequest($path, $queryParams);
        break;
    
    case 'POST':
        handlePostRequest($path);
        break;
    
    case 'PUT':
    case 'PATCH':
        handleUpdateRequest($path);
        break;
    
    case 'DELETE':
        handleDeleteRequest($path);
        break;
    
    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}

/**
 * Handle GET requests
 */
function handleGetRequest($path, $queryParams) {
    // GET /slots - Get all slots (admin) or available only (client)
    if ($path === 'slots' || strpos($path, 'slots?') === 0) {
        $dateFilter = $queryParams['date'] ?? null;
        $role = $queryParams['role'] ?? 'client';
        
        $isAdmin = false;
        if ($role === 'admin') {
            checkAdminAuth(); // Will exit if unauthorized
            $isAdmin = true;
        }
        
        $slots = getSlots($isAdmin, $dateFilter);
        jsonResponse($slots);
    }
    
    // GET /auth/check - Check if authenticated
    elseif ($path === 'auth/check') {
        initSecureSession();
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            jsonResponse(['success' => true, 'authenticated' => true]);
        } else {
            jsonResponse(['success' => false, 'authenticated' => false], 401);
        }
    }

    // GET /stats - Get statistics (admin only)
    elseif ($path === 'stats') {
        checkAdminAuth();
        $stats = getStatistics();
        jsonResponse(['success' => true, 'data' => $stats]);
    }
    
    // GET /slots/{id} - Get single slot
    elseif (preg_match('#^slots/(\d+)$#', $path, $matches)) {
        $slotId = (int)$matches[1];
        $slot = getSlotById($slotId);
        
        if (!$slot) {
            jsonResponse(['error' => 'Slot not found'], 404);
        }
        
        jsonResponse($slot);
    }
    
    else {
        jsonResponse(['error' => 'Endpoint not found'], 404);
    }
}

/**
 * Handle POST requests
 */
function handlePostRequest($path) {
    $data = getInput();
    
    // POST /auth/login - Admin login
    if ($path === 'auth/login') {
        $result = loginAdmin($data['password'] ?? '');
        
        if ($result['success']) {
            jsonResponse([
                'success' => true,
                'message' => 'Login successful'
            ]);
        } else {
            jsonResponse(['error' => $result['error']], 401);
        }
        return;
    }
    
    // POST /auth/logout - Admin logout
    if ($path === 'auth/logout') {
        checkAdminAuth();
        $result = logoutAdmin();
        jsonResponse($result);
        return;
    }
    
    // POST /slots/generate - Generate slots from template (admin only)
    if ($path === 'slots/generate') {
        checkAdminAuth();
        
        $result = generateSlotsFromTemplate($data);
        
        if (isset($result['error'])) {
            jsonResponse(['error' => $result['error']], 400);
        }
        
        jsonResponse([
            'success' => true,
            'message' => "Created {$result['created_count']} slots",
            'count' => $result['created_count'],
            'skipped' => $result['skipped_count'],
            'slots' => $result['slots']
        ], 201);
        return;
    }
    
    // POST /slots - Create single slot (admin only)
    if ($path === 'slots') {
        checkAdminAuth();
        
        // Validate required fields
        if (empty($data['start_time']) || empty($data['end_time'])) {
            jsonResponse(['error' => 'start_time and end_time are required'], 400);
        }
        
        $result = createSlot($data);
        
        if (isset($result['error'])) {
            $statusCode = strpos($result['error'], 'overlaps') !== false ? 409 : 400;
            jsonResponse(['error' => $result['error']], $statusCode);
        }
        
        jsonResponse([
            'success' => true,
            'slot' => $result
        ], 201);
        return;
    }
    
    // POST /slots/{id}/book - Book a slot (client)
    if (preg_match('#^slots/(\d+)/book$#', $path, $matches)) {
        $slotId = (int)$matches[1];
        
        // Validate required fields
        if (empty($data['client_name']) || empty($data['client_phone'])) {
            jsonResponse(['error' => 'client_name and client_phone are required'], 400);
        }
        
        $result = bookSlot($slotId, $data);
        
        if (isset($result['error'])) {
            $statusCode = $result['error'] === 'Slot not found' ? 404 : 409;
            jsonResponse(['error' => $result['error']], $statusCode);
        }
        
        jsonResponse([
            'success' => true,
            'slot' => $result
        ]);
        return;
    }
    
    // POST /slots/{id}/cancel - Cancel booking (admin only)
    if (preg_match('#^slots/(\d+)/cancel$#', $path, $matches)) {
        checkAdminAuth();
        $slotId = (int)$matches[1];
        
        $result = cancelBooking($slotId);
        
        if (isset($result['error'])) {
            $statusCode = $result['error'] === 'Slot not found' ? 404 : 400;
            jsonResponse(['error' => $result['error']], $statusCode);
        }
        
        jsonResponse([
            'success' => true,
            'slot' => $result
        ]);
        return;
    }
    
    jsonResponse(['error' => 'Endpoint not found'], 404);
}

/**
 * Handle PUT/PATCH requests
 */
function handleUpdateRequest($path) {
    $data = getInput();
    
    // PUT /slots/{id} - Update slot (admin only)
    if (preg_match('#^slots/(\d+)$#', $path, $matches)) {
        checkAdminAuth();
        $slotId = (int)$matches[1];
        
        $slot = getSlotById($slotId);
        if (!$slot) {
            jsonResponse(['error' => 'Slot not found'], 404);
        }
        
        // Only update description for now
        if (isset($data['description'])) {
            $conn = getDbConnection();
            $description = sanitizeInput($data['description']);
            $stmt = $conn->prepare("UPDATE slots SET description = ? WHERE id = ?");
            $stmt->bind_param("si", $description, $slotId);
            
            if ($stmt->execute()) {
                $updatedSlot = getSlotById($slotId);
                jsonResponse([
                    'success' => true,
                    'slot' => $updatedSlot
                ]);
            } else {
                jsonResponse(['error' => 'Failed to update slot'], 500);
            }
            $stmt->close();
        } else {
            jsonResponse(['error' => 'No fields to update'], 400);
        }
        return;
    }
    
    jsonResponse(['error' => 'Endpoint not found'], 404);
}

/**
 * Handle DELETE requests
 */
function handleDeleteRequest($path) {
    // DELETE /slots/{id} - Delete slot (admin only)
    if (preg_match('#^slots/(\d+)$#', $path, $matches)) {
        checkAdminAuth();
        $slotId = (int)$matches[1];
        
        $slot = getSlotById($slotId);
        if (!$slot) {
            jsonResponse(['error' => 'Slot not found'], 404);
        }
        
        if (deleteSlot($slotId)) {
            jsonResponse([
                'success' => true,
                'message' => 'Slot deleted successfully'
            ]);
        } else {
            jsonResponse(['error' => 'Failed to delete slot'], 500);
        }
        return;
    }
    
    jsonResponse(['error' => 'Endpoint not found'], 404);
}
