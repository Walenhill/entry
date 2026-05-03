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
    
    $slots = [];
    while ($row = $result->fetch_assoc()) {
        $slots[] = $row;
    }
    
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
    
    // Hardcode default staff_id and service_id for now
    $staffId = $data['staff_id'] ?? 1;
    $serviceId = $data['service_id'] ?? 1;

    $stmt = $conn->prepare("INSERT INTO slots (staff_id, service_id, start_time, end_time, description, status) VALUES (?, ?, ?, ?, ?, 'available')");
    $description = sanitizeInput($data['description'] ?? '');
    $stmt->bind_param("iisss", $staffId, $serviceId, $data['start_time'], $data['end_time'], $description);
    
    if (!$stmt->execute()) {
        $stmt->close();
        return ['error' => 'Failed to create slot: ' . $conn->error];
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
    
    $date = $template['date'];
    $startHour = (int)$template['start_hour'];
    $endHour = (int)$template['end_hour'];
    $duration = (int)$template['duration'];
    $description = sanitizeInput($template['description'] ?? 'Appointment');
    
    // Validate inputs
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
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
    
    // Hardcode default staff_id and service_id for now
    $staffId = $template['staff_id'] ?? 1;
    $serviceId = $template['service_id'] ?? 1;

    while ($currentTime + ($duration * 60) <= $endTime) {
        $startTimeStr = date('Y-m-d H:i:s', $currentTime);
        $endTimeStr = date('Y-m-d H:i:s', $currentTime + ($duration * 60));
        
        // Check for overlaps
        if (hasOverlappingSlots($startTimeStr, $endTimeStr)) {
            $skippedCount++;
        } else {
            $stmt = $conn->prepare("INSERT INTO slots (staff_id, service_id, start_time, end_time, description, status) VALUES (?, ?, ?, ?, ?, 'available')");
            $stmt->bind_param("iisss", $staffId, $serviceId, $startTimeStr, $endTimeStr, $description);
            
            if ($stmt->execute()) {
                $newId = $conn->insert_id;
                $createdSlots[] = getSlotById($newId);
                $createdCount++;
            }
            $stmt->close();
        }
        
        $currentTime += ($duration * 60);
    }
    
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
    
    $stmt = $conn->prepare("UPDATE slots SET status = 'booked', client_name = ?, client_phone = ? WHERE id = ?");
    $stmt->bind_param("ssi", $clientName, $clientPhone, $id);
    
    if (!$stmt->execute()) {
        $stmt->close();
        return ['error' => 'Failed to book slot: ' . $conn->error];
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
    
    $stmt = $conn->prepare("UPDATE slots SET status = 'cancelled', client_name = NULL, client_phone = NULL WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        $stmt->close();
        return ['error' => 'Failed to cancel booking: ' . $conn->error];
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
    $stmt->close();
    
    return $success;
}

/**
 * Check if time range overlaps with existing slots (excluding cancelled)
 */
function hasOverlappingSlots($startTime, $endTime) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("SELECT id FROM slots WHERE status != 'cancelled' AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))");
    $stmt->bind_param("ssss", $endTime, $startTime, $endTime, $startTime);
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
    $result = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) as booked,
        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
        FROM slots");
    $summary = $result->fetch_assoc();
    
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
    $topClients = [];
    while ($row = $result->fetch_assoc()) {
        $topClients[] = $row;
    }
    
    return [
        'summary' => $summary,
        'occupancy_rate' => $occupancyRate,
        'top_clients' => $topClients
    ];
}
