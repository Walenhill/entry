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

// Ensure path does not contain query string for matching
$routePath = explode('?', $path)[0];

switch ($method) {
    case 'GET':
        switch ($routePath) {
            case 'slots':
                $dateFilter = isset($queryParams['date']) && is_string($queryParams['date']) ? $queryParams['date'] : null;
                $role = isset($queryParams['role']) && is_string($queryParams['role']) ? $queryParams['role'] : 'client';

                $isAdmin = false;
                if ($role === 'admin') {
                    checkAdminAuth(); // Will exit if unauthorized
                    $isAdmin = true;
                }

                $slots = getSlots($isAdmin, $dateFilter);
                jsonResponse($slots);
                break;

            case 'stats':
                checkAdminAuth();
                $stats = getStatistics();
                jsonResponse(['success' => true, 'data' => $stats]);
                break;

            default:
                if (preg_match('#^slots/(\d+)$#', $routePath, $matches)) {
                    $slotId = (int)$matches[1];
                    $slot = getSlotById($slotId);

                    if (!$slot) {
                        jsonResponse(['error' => 'Slot not found'], 404);
                    }

                    $role = $queryParams['role'] ?? 'client';
                    $isAdmin = false;

                    if ($role === 'admin') {
                        checkAdminAuth();
                        $isAdmin = true;
                    }

                    // Obscure PII if user is not admin
                    if (!$isAdmin) {
                        unset($slot['client_name']);
                        unset($slot['client_phone']);
                    }

                    jsonResponse($slot);
                } else {
                    jsonResponse(['error' => 'Endpoint not found'], 404);
                }
                break;
        }
        break;

    case 'POST':
        $data = getInput();
        switch ($routePath) {
            case 'auth/login':
                $result = loginAdmin($data['password'] ?? '');

                if ($result['success']) {
                    jsonResponse([
                        'success' => true,
                        'message' => 'Login successful'
                    ]);
                } else {
                    jsonResponse(['error' => $result['error']], 401);
                }
                break;

            case 'auth/logout':
                checkAdminAuth();
                $result = logoutAdmin();
                jsonResponse($result);
                break;

            case 'slots/generate':
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
                break;

            case 'slots':
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
                break;

            default:
                if (preg_match('#^slots/(\d+)/book$#', $routePath, $matches)) {
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
                } elseif (preg_match('#^slots/(\d+)/cancel$#', $routePath, $matches)) {
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
                } else {
                    jsonResponse(['error' => 'Endpoint not found'], 404);
                }
                break;
        }
        break;

    case 'PUT':
    case 'PATCH':
        $data = getInput();
        if (preg_match('#^slots/(\d+)$#', $routePath, $matches)) {
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
        } else {
            jsonResponse(['error' => 'Endpoint not found'], 404);
        }
        break;

    case 'DELETE':
        if (preg_match('#^slots/(\d+)$#', $routePath, $matches)) {
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
        } else {
            jsonResponse(['error' => 'Endpoint not found'], 404);
        }
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
