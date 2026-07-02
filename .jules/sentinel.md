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
## 2024-05-13 - [Missing Security Headers]
**Vulnerability:** API responses lacked standard security headers, making the application more susceptible to MIME-sniffing, clickjacking, and XSS.
**Learning:** Security headers are not automatically applied in vanilla PHP APIs unless explicitly configured.
**Prevention:** Ensure standard security headers (Content-Security-Policy, Strict-Transport-Security, X-Frame-Options, X-Content-Type-Options) are set at the main entry point for all API responses.

## 2026-05-16 - Prevent Memory Exhaustion DoS in JSON parsing
**Vulnerability:** Unrestricted `file_get_contents('php://input')` when reading request bodies for JSON decoding allows attackers to send arbitrarily large payloads, exhausting server memory and causing Denial of Service.
**Learning:** Default PHP stream reading does not cap size. Even if a web server has upload limits, memory limits can be hit during string allocation and JSON parsing of massive payloads if not explicitly restricted at the application layer.
**Prevention:** Always specify a maximum read length (e.g., `, false, null, 0, 1048576` for 1MB) when parsing raw JSON inputs via `file_get_contents('php://input')` to proactively defend against memory exhaustion attacks.

## 2024-05-19 - PII Leakage via Functional Regression in Slot Retrieval
**Vulnerability:** The API endpoint `GET /slots` conditionally hid booked slots from non-admin users by appending a `status = 'available'` SQL filter, breaking the frontend's ability to show the complete schedule while simultaneously preventing PII exposure.
**Learning:** Over-filtering at the database level to solve an authorization issue can cause functional regressions on the frontend (clients need to see that a slot exists, just not who booked it).
**Prevention:** Remove restrictive database filters and instead handle data sanitization at the application layer by explicitly unsetting sensitive PII fields (`client_name`, `client_phone`) from the response payload before sending it to unauthorized clients.
## 2026-05-22 - Input Validation TypeErrors (DoS Prevention)
**Vulnerability:** Unhandled PHP `TypeError` exceptions from passing arrays in JSON payloads to native string functions like `trim()`.
**Learning:** PHP 8+ throws fatal errors (TypeErrors) when built-in string functions receive arrays instead of strings. Attackers can intentionally trigger these errors to cause application-level DoS or fill error logs, potentially leaking stack traces. This was specifically vulnerable in the POST `/slots/{id}/book` endpoint.
**Prevention:** Always explicitly validate input types using `is_string()` before passing user-controlled variables from JSON payloads or query parameters to native string functions.
## 2024-06-01 - Missing row-level filtering leads to Information Disclosure
**Vulnerability:** The API endpoint `GET /slots` returned all slots, including booked and cancelled ones, to unauthenticated users despite an intent to only return available slots.
**Learning:** Row-level filtering was omitted from the database query when constructing `$conditions` for clients. Even though sensitive columns were excluded, the existence and metadata of booked/cancelled slots were leaked.
**Prevention:** Always ensure that both column-level AND row-level access controls are explicitly applied when retrieving lists of resources for unauthenticated or restricted users.
## 2026-06-04 - Direct access to dotfiles via missing htaccess rule
**Vulnerability:** The application was serving hidden files (like `.env`) because `backend/.htaccess` did not explicitly deny access to dotfiles.
**Learning:** Default Apache configuration does not always protect dotfiles. A specific rule must be added to `.htaccess` to ensure sensitive configurations are not exposed.
**Prevention:** Always include `<FilesMatch "^\."> Require all denied </FilesMatch>` in Apache `.htaccess` files to block web access to dotfiles.
## 2024-10-27 - IDOR / Information Disclosure in Individual Resource Endpoints
**Vulnerability:** The API endpoint `GET /slots/{id}` returned individual slot details, including the existence and status of booked or cancelled slots, to unauthenticated users. While PII was stripped, the mere existence and metadata of non-available slots were leaked.
**Learning:** Checking authorization logic on list endpoints (e.g., `GET /slots`) but failing to enforce the exact same row-level access controls on individual resource retrieval endpoints (e.g., `GET /slots/{id}`) creates an Insecure Direct Object Reference (IDOR) or Information Disclosure vulnerability.
**Prevention:** Always ensure that row-level access controls (like checking `status === 'available'`) are uniformly applied across both list and individual resource retrieval endpoints for unauthenticated or restricted users. Return generic 404s for unauthorized resources to prevent existence leakage.
## 2026-07-02 - Prevent Information Disclosure via browser cache
**Vulnerability:** The PHP API did not explicitly set strict anti-caching headers on JSON responses.
**Learning:** Browsers and intermediate proxies might cache API responses containing sensitive information (like user data, tokens, or PII) if caching headers are absent.
**Prevention:** Always set `Cache-Control: no-store, no-cache, must-revalidate, max-age=0` and `Pragma: no-cache` on all sensitive or dynamic JSON endpoints to prevent information disclosure via browser cache.
