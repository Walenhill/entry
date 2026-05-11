## 2024-05-24 - Input Validation TypeErrors (DoS Prevention)
**Vulnerability:** Unhandled PHP `TypeError` exceptions from passing arrays in HTTP query parameters (e.g., `?date[]=foo`) or JSON payloads to native functions expecting strings like `preg_match` or `DateTime::createFromFormat`.
**Learning:** PHP 8+ throws fatal errors (TypeErrors) when built-in string functions receive arrays instead of strings. Attackers can intentionally trigger these errors to cause application-level DoS or fill error logs, potentially leaking stack traces.
**Prevention:** Always explicitly validate input types using `is_string()` or cast safely using `(int)` before passing user-controlled variables to native functions, especially when handling data from `$_GET`, `$_POST`, or JSON payloads.

## 2024-05-24 - Information Disclosure via Database Errors
**Vulnerability:** Raw database errors (`$conn->error`) were directly returned to the client in HTTP responses during slot creation, booking, and cancellation operations in `backend/includes/slots_crud.php`.
**Learning:** Returning raw database errors exposes internal database structure, table names, query details, and potentially sensitive database versions to end-users, aiding attackers in formulating targeted SQL injection or other attacks.
**Prevention:** Catch and log raw exceptions/errors securely on the server side using `error_log()`, and always return generic, user-friendly error messages (e.g., "An internal error occurred" or "Failed to create slot") to the client.

## 2024-05-10 - [TOCTOU Race Condition in Slot Booking/Cancellation]
**Vulnerability:** A Time-of-Check to Time-of-Use (TOCTOU) vulnerability existed in `bookSlot` and `cancelBooking` where the database query relied on the slot's current status checked outside the update query, meaning two concurrent requests could double-book or mis-cancel the same slot.
**Learning:** Checking a condition in PHP and then executing an `UPDATE` query without enforcing the condition in the `UPDATE`'s `WHERE` clause is inherently vulnerable to race conditions under high concurrent load.
**Prevention:** Always bundle state requirements into the `WHERE` clause of the SQL `UPDATE` statement (e.g., `WHERE id = ? AND status = 'available'`) and check `$stmt->affected_rows` to verify atomic state transitions.
## 2026-05-11 - [CORS Origin Reflection Bypass]
**Vulnerability:** The CORS policy in `backend/main.php` used loose `in_array()` matching and reflected the unsanitized `$_SERVER['HTTP_ORIGIN']` directly.
**Learning:** Type juggling bypasses are possible when loose comparison is used in PHP, and returning the client's input directly as an origin header defeats the purpose of an allowlist.
**Prevention:** Always use strict comparison (`array_search(..., true)`) for whitelists and return the strictly matched string from the backend's array, never the direct client input.
