## 2024-05-24 - Input Validation TypeErrors (DoS Prevention)
**Vulnerability:** Unhandled PHP `TypeError` exceptions from passing arrays in HTTP query parameters (e.g., `?date[]=foo`) or JSON payloads to native functions expecting strings like `preg_match` or `DateTime::createFromFormat`.
**Learning:** PHP 8+ throws fatal errors (TypeErrors) when built-in string functions receive arrays instead of strings. Attackers can intentionally trigger these errors to cause application-level DoS or fill error logs, potentially leaking stack traces.
**Prevention:** Always explicitly validate input types using `is_string()` or cast safely using `(int)` before passing user-controlled variables to native functions, especially when handling data from `$_GET`, `$_POST`, or JSON payloads.

## 2024-05-24 - Information Disclosure via Database Errors
**Vulnerability:** Raw database errors (`$conn->error`) were directly returned to the client in HTTP responses during slot creation, booking, and cancellation operations in `backend/includes/slots_crud.php`.
**Learning:** Returning raw database errors exposes internal database structure, table names, query details, and potentially sensitive database versions to end-users, aiding attackers in formulating targeted SQL injection or other attacks.
**Prevention:** Catch and log raw exceptions/errors securely on the server side using `error_log()`, and always return generic, user-friendly error messages (e.g., "An internal error occurred" or "Failed to create slot") to the client.

## 2024-05-09 - Time-of-Check to Time-of-Use (TOCTOU) Race Condition in Booking
**Vulnerability:** A race condition existed where two concurrent requests could try to book the same available slot. The first request would check if the slot was available, proceed, and then the second request would do the same before the first request completed the database update. Both would succeed, but the second request would silently overwrite the first request's booking details.
**Learning:** The initial code correctly checked availability (`if ($slot['status'] !== 'available')`), but this check and the subsequent `UPDATE` statement were not atomic. In high-concurrency environments, relying solely on application-level checks without database-level atomic constraints leads to data corruption.
**Prevention:** Always push the concurrency constraint down to the database level by including the expected state in the `UPDATE` query's `WHERE` clause (e.g., `WHERE id = ? AND status = 'available'`). Then, check `$stmt->affected_rows` to see if the update actually modified a row. If `affected_rows` is 0, another request already modified the record.
