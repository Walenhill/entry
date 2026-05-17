<?php
/**
 * Slots CRUD operations
 */

require_once __DIR__ . '/helpers.php';

/**
 * Get all slots
 * @param bool $isAdmin - if true, return all slots; if false, only available
 * @param string|null $dateFilter - filter by date (YYYY-MM-DD)
 */
function getSlots($isAdmin = false, $dateFilter = null) {
    $conn = getDbConnection();
    
    $sql = "SELECT * FROM slots";
    $conditions = [];
    
    if (!$isAdmin) {
        $conditions[] = "status = 'available'";
    }
    
    if ($dateFilter) {
        // Use a range query instead of DATE(start_time) = ? to make the query SARGable
        // and allow MySQL to use the idx_start_time index.
        $conditions[] = "start_time >= ? AND start_time < ? + INTERVAL 1 DAY";
    }
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    
    $sql .= " ORDER BY start_time ASC";
    
    $stmt = $conn->prepare($sql);
    
    if ($dateFilter) {
        $stmt->bind_param("ss", $dateFilter, $dateFilter);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Performance optimization: Using fetch_all(MYSQLI_ASSOC) instead of a while loop with fetch_assoc()
    // pushes the array construction down to the C layer, avoiding slow user-land PHP iteration
    $slots = $result->fetch_all(MYSQLI_ASSOC);
    
    $stmt->close();
    return $slots;
}

/**
 * Get single slot by ID
 */
function getSlotById($id) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("SELECT * FROM slots WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return null;
    }
    
    $slot = $result->fetch_assoc();
    $stmt->close();
    return $slot;
}

/**
 * Create a single slot
 * @param array $data - [start_time, end_time, description]
 * @return array|false - created slot or false on failure
 */
function createSlot($data) {
    $conn = getDbConnection();
    
    // Validate datetime
    if (!validateDateTime($data['start_time']) || !validateDateTime($data['end_time'])) {
        return ['error' => 'Invalid datetime format. Use YYYY-MM-DD HH:MM:SS'];
    }
    
    // Check start < end
    if ($data['start_time'] >= $data['end_time']) {
        return ['error' => 'Start time must be before end time'];
    }
    
    // Check for overlaps
    if (hasOverlappingSlots($data['start_time'], $data['end_time'])) {
        return ['error' => 'Time slot overlaps with existing booking'];
    }
    
    $description = sanitizeInput($data['description'] ?? '');
    if (mb_strlen($description) > 255) {
        return ['error' => 'Description must not exceed 255 characters'];
    }

    $stmt = $conn->prepare("INSERT INTO slots (start_time, end_time, description, status) VALUES (?, ?, ?, 'available')");
    $stmt->bind_param("sss", $data['start_time'], $data['end_time'], $description);
    
    if (!$stmt->execute()) {
        error_log('Failed to create slot: ' . $conn->error);
        $stmt->close();
        return ['error' => 'Failed to create slot.'];
    }
    
    $newId = $conn->insert_id;
    $stmt->close();
    
    return getSlotById($newId);
}

/**
 * Generate multiple slots from template
 * @param array $template - [date, start_hour, end_hour, duration, description]
 * @return array - [created_count, skipped_count, slots]
 */
function generateSlotsFromTemplate($template) {
    $conn = getDbConnection();
    
    $date = $template['date'] ?? null;
    $startHour = isset($template['start_hour']) ? (int)$template['start_hour'] : 0;
    $endHour = isset($template['end_hour']) ? (int)$template['end_hour'] : 0;
    $duration = isset($template['duration']) ? (int)$template['duration'] : 0;
    $description = sanitizeInput($template['description'] ?? 'Appointment');
    
    // Validate inputs
    if (mb_strlen($description) > 255) {
        return ['error' => 'Description must not exceed 255 characters'];
    }
    if (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return ['error' => 'Invalid date format. Use YYYY-MM-DD'];
    }
    
    if ($startHour < 0 || $startHour > 23 || $endHour < 1 || $endHour > 24) {
        return ['error' => 'Invalid hours (0-23)'];
    }
    
    if ($duration <= 0 || $duration > 600) {
        return ['error' => 'Duration must be between 1 and 600 minutes'];
    }
    
    if ($startHour >= $endHour) {
        return ['error' => 'Start hour must be before end hour'];
    }
    
    $createdCount = 0;
    $skippedCount = 0;
    $createdSlots = [];
    
    $currentTime = strtotime("$date " . sprintf('%02d:00:00', $startHour));
    $endTime = strtotime("$date " . sprintf('%02d:00:00', $endHour));
    
    // Prepare the insertion statement once outside the loop
    $insertStmt = $conn->prepare("INSERT INTO slots (start_time, end_time, description, status) VALUES (?, ?, ?, 'available')");
    $loopStartTimeStr = "";
    $loopEndTimeStr = "";
    $insertStmt->bind_param("sss", $loopStartTimeStr, $loopEndTimeStr, $description);

    $batchStartTimeStr = date('Y-m-d H:i:s', $currentTime);
    $batchEndTimeStr = date('Y-m-d H:i:s', $endTime);

    // Pre-fetch all non-cancelled slots that could overlap with any slot in this template generation
    $existingSlotsStmt = $conn->prepare("SELECT start_time, end_time FROM slots WHERE status != 'cancelled' AND (start_time < ? AND end_time > ?)");
    $existingSlotsStmt->bind_param("ss", $batchEndTimeStr, $batchStartTimeStr);
    $existingSlotsStmt->execute();
    $existingSlotsResult = $existingSlotsStmt->get_result();
    $existingSlots = $existingSlotsResult->fetch_all(MYSQLI_ASSOC);
    $existingSlotsStmt->close();

    // Performance optimization: Wrap multiple inserts in a single transaction
    // This avoids auto-commit overhead and significantly speeds up batch generation
    $conn->begin_transaction();

    try {
        while ($currentTime + ($duration * 60) <= $endTime) {
            $loopStartTimeStr = date('Y-m-d H:i:s', $currentTime);
            $loopEndTimeStr = date('Y-m-d H:i:s', $currentTime + ($duration * 60));

            // Check for overlaps in memory
            $hasOverlap = false;
            foreach ($existingSlots as $existingSlot) {
                if ($existingSlot['start_time'] < $loopEndTimeStr && $existingSlot['end_time'] > $loopStartTimeStr) {
                    $hasOverlap = true;
                    break;
                }
            }

            if ($hasOverlap) {
                $skippedCount++;
            } else {
                if ($insertStmt->execute()) {
                    $newId = $conn->insert_id;
                    // Construct the slot directly in memory to avoid O(N) database reads
                    $createdSlots[] = [
                        'id' => $newId,
                        'start_time' => $loopStartTimeStr,
                        'end_time' => $loopEndTimeStr,
                        'description' => $description,
                        'status' => 'available',
                        'client_name' => null,
                        'client_phone' => null
                    ];
                    $createdCount++;
                } else {
                    error_log('Failed to execute template insert: ' . $insertStmt->error);
                }
            }

            $currentTime += ($duration * 60);
        }
        
        $conn->commit();
    } catch (\Exception $e) {
        $conn->rollback();
        error_log('Transaction failed during batch insert: ' . $e->getMessage());
        // Depending on business logic, we might want to return an error, but let's rethrow or return error
        $insertStmt->close();
        return ['error' => 'An internal error occurred during batch slot generation'];
    }
    
    $insertStmt->close();

    return [
        'created_count' => $createdCount,
        'skipped_count' => $skippedCount,
        'slots' => $createdSlots
    ];
}

/**
 * Book a slot
 * @param int $id - slot ID
 * @param array $clientData - [client_name, client_phone]
 * @return array|false - updated slot or error
 */
function bookSlot($id, $clientData) {
    $conn = getDbConnection();
    
    $slot = getSlotById($id);
    
    if (!$slot) {
        return ['error' => 'Slot not found'];
    }
    
    if ($slot['status'] !== 'available') {
        return ['error' => 'Slot is not available'];
    }
    
    $clientName = sanitizeInput($clientData['client_name'] ?? '');
    $clientPhone = sanitizeInput($clientData['client_phone'] ?? '');
    
    if (empty($clientName) || empty($clientPhone)) {
        return ['error' => 'Client name and phone are required'];
    }

    if (mb_strlen($clientName) > 100) {
        return ['error' => 'Client name must not exceed 100 characters'];
    }
    if (mb_strlen($clientPhone) > 20) {
        return ['error' => 'Client phone must not exceed 20 characters'];
    }
    
    $stmt = $conn->prepare("UPDATE slots SET status = 'booked', client_name = ?, client_phone = ? WHERE id = ? AND status = 'available'");
    $stmt->bind_param("ssi", $clientName, $clientPhone, $id);
    
    if (!$stmt->execute()) {
        error_log('Failed to book slot: ' . $conn->error);
        $stmt->close();
        return ['error' => 'Failed to book slot.'];
    }
    
    if ($stmt->affected_rows === 0) {
        $stmt->close();
        return ['error' => 'Slot is no longer available'];
    }

    $stmt->close();
    return getSlotById($id);
}

/**
 * Cancel a booking
 * @param int $id - slot ID
 * @return array|false - updated slot or error
 */
function cancelBooking($id) {
    $conn = getDbConnection();
    
    $slot = getSlotById($id);
    
    if (!$slot) {
        return ['error' => 'Slot not found'];
    }
    
    if ($slot['status'] !== 'booked') {
        return ['error' => 'Slot is not booked'];
    }
    
    $stmt = $conn->prepare("UPDATE slots SET status = 'available', client_name = NULL, client_phone = NULL WHERE id = ? AND status = 'booked'");
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        error_log('Failed to cancel booking: ' . $conn->error);
        $stmt->close();
        return ['error' => 'Failed to cancel booking.'];
    }
    
    if ($stmt->affected_rows === 0) {
        $stmt->close();
        return ['error' => 'Failed to cancel booking: Slot is no longer booked'];
    }

    $stmt->close();
    return getSlotById($id);
}

/**
 * Delete a slot
 * @param int $id - slot ID
 * @return bool
 */
function deleteSlot($id) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("DELETE FROM slots WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    $success = $stmt->execute();
    if (!$success) {
        error_log('Failed to delete slot: ' . $conn->error);
    }

    $stmt->close();
    
    return $success;
}

/**
 * Check if time range overlaps with existing slots (excluding cancelled)
 */
function hasOverlappingSlots($startTime, $endTime) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("SELECT id FROM slots WHERE status != 'cancelled' AND (start_time < ? AND end_time > ?) LIMIT 1");
    $stmt->bind_param("ss", $endTime, $startTime);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $hasOverlap = $result->num_rows > 0;
    $stmt->close();
    
    return $hasOverlap;
}

/**
 * Get statistics for admin dashboard
 */
function getStatistics() {
    $conn = getDbConnection();
    
    // Total slots by status
    // Performance optimization: Using GROUP BY allows MySQL to utilize the idx_status index via an index scan.
    // The previous SUM(CASE WHEN ...) forced a full table scan because it evaluated every row.
    $result = $conn->query("SELECT status, COUNT(*) as count FROM slots GROUP BY status");

    $summary = [
        'total' => 0,
        'booked' => 0,
        'available' => 0,
        'cancelled' => 0
    ];

    while ($row = $result->fetch_assoc()) {
        $status = $row['status'];
        $count = (int)$row['count'];
        $summary[$status] = $count;
        $summary['total'] += $count;
    }
    
    // Occupancy rate
    $total = (int)$summary['total'];
    $booked = (int)$summary['booked'];
    $occupancyRate = $total > 0 ? round(($booked / $total) * 100, 2) : 0;
    
    // Top clients by visits
    $result = $conn->query("SELECT client_name, client_phone, COUNT(*) as visits 
        FROM slots 
        WHERE status = 'booked' AND client_name IS NOT NULL
        GROUP BY client_phone, client_name 
        ORDER BY visits DESC 
        LIMIT 5");
    // Performance optimization: Using fetch_all(MYSQLI_ASSOC) instead of a while loop with fetch_assoc()
    // pushes the array construction down to the C layer, avoiding slow user-land PHP iteration
    $topClients = $result->fetch_all(MYSQLI_ASSOC);
    
    return [
        'summary' => $summary,
        'occupancy_rate' => $occupancyRate,
        'top_clients' => $topClients
    ];
}
