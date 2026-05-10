## 2024-05-24 - Input Validation TypeErrors (DoS Prevention)
**Vulnerability:** Unhandled PHP `TypeError` exceptions from passing arrays in HTTP query parameters (e.g., `?date[]=foo`) or JSON payloads to native functions expecting strings like `preg_match` or `DateTime::createFromFormat`.
**Learning:** PHP 8+ throws fatal errors (TypeErrors) when built-in string functions receive arrays instead of strings. Attackers can intentionally trigger these errors to cause application-level DoS or fill error logs, potentially leaking stack traces.
**Prevention:** Always explicitly validate input types using `is_string()` or cast safely using `(int)` before passing user-controlled variables to native functions, especially when handling data from `$_GET`, `$_POST`, or JSON payloads.

## 2024-05-24 - Information Disclosure via Database Errors
**Vulnerability:** Raw database errors (`$conn->error`) were directly returned to the client in HTTP responses during slot creation, booking, and cancellation operations in `backend/includes/slots_crud.php`.
**Learning:** Returning raw database errors exposes internal database structure, table names, query details, and potentially sensitive database versions to end-users, aiding attackers in formulating targeted SQL injection or other attacks.
**Prevention:** Catch and log raw exceptions/errors securely on the server side using `error_log()`, and always return generic, user-friendly error messages (e.g., "An internal error occurred" or "Failed to create slot") to the client.

## 2026-05-09 - Use of Hardcoded Credentials in Authentication Logic
**Vulnerability:** The authentication logic contained a hardcoded check for a specific password string ('admin123') during the initial setup of the administrator account.
**Learning:** Including hardcoded credential strings or blacklists in source code is a security risk as it exposes sensitive patterns, can be flagged by static analysis tools (SAST), and creates bypasses or unnecessary restrictions that should be handled via configuration.
**Prevention:** Always use environment variables or secure configuration files for initial credentials, and rely on secure hashing algorithms and database persistence for credential management instead of hardcoding values or specific checks in the logic.
