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

## 2026-05-10 - Overly Permissive CORS Origin Reflection
**Vulnerability:** The `backend/main.php` file directly echoed the user-provided `$_SERVER['HTTP_ORIGIN']` in the `Access-Control-Allow-Origin` header if it passed an `in_array` check.
**Learning:** Reflecting user input directly into security headers, even if loosely validated, is an anti-pattern. If the validation is bypassed or flawed, attackers can inject arbitrary origins, enabling cross-origin attacks or confusing static analysis tools into flagging it as a vulnerability.
**Prevention:** Instead of reflecting the input, always use strict matching (e.g., `array_search($origin, $allowedOrigins, true)`) and output the exact predefined string from the allowed whitelist.

## 2024-05-12 - Architectural DoS via Reverse Proxy Rate Limiting
**Vulnerability:** The application used `$_SERVER['REMOTE_ADDR']` for IP-based rate limiting on the admin login endpoint. In a Docker/Reverse Proxy environment, this resolves to the proxy's internal IP (e.g., Nginx) rather than the real client IP.
**Learning:** If a reverse proxy is in front of the application, all requests will appear to come from the proxy's IP. This means a single malicious actor failing logins will block the proxy's IP, effectively denying access to all legitimate users (a Denial of Service).
**Prevention:** If `REMOTE_ADDR` is determined to be a private/internal IP (indicating a local proxy), securely extract the real client IP from the `X-Forwarded-For` header. Always validate the extracted IP to prevent spoofing if the application is ever exposed directly.
## 2026-05-14 - Missing API Security Headers
**Vulnerability:** The backend API lacked fundamental security headers (e.g., Content-Security-Policy, Strict-Transport-Security, X-Frame-Options, X-Content-Type-Options) in its responses.
**Learning:** Without explicit security headers, the API is unnecessarily exposed to content sniffing, clickjacking, and XSS risks. A strict `Content-Security-Policy: default-src 'none'` is a particularly strong defense-in-depth measure for JSON APIs as it prevents browsers from executing arbitrary content if the API is directly loaded or spoofed.
**Prevention:** Always bundle standard HTTP security headers within the main application entry point or API router (e.g., `backend/main.php`) alongside CORS configuration to ensure every response is protected.
