## 2024-05-24 - Input Validation TypeErrors (DoS Prevention)
**Vulnerability:** Unhandled PHP `TypeError` exceptions from passing arrays in HTTP query parameters (e.g., `?date[]=foo`) or JSON payloads to native functions expecting strings like `preg_match` or `DateTime::createFromFormat`.
**Learning:** PHP 8+ throws fatal errors (TypeErrors) when built-in string functions receive arrays instead of strings. Attackers can intentionally trigger these errors to cause application-level DoS or fill error logs, potentially leaking stack traces.
**Prevention:** Always explicitly validate input types using `is_string()` or cast safely using `(int)` before passing user-controlled variables to native functions, especially when handling data from `$_GET`, `$_POST`, or JSON payloads.
