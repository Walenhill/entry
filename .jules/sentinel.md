## 2024-05-03 - [Missing CSRF Protection Implementation]
**Vulnerability:** The backend endpoints for POST, PUT, and DELETE explicitly called a `verifyCsrfToken()` function, but this function was not defined anywhere in the application. This resulted in a complete lack of CSRF protection and caused PHP Fatal Errors whenever state-changing endpoints were hit.
**Learning:** Security measures that are called but not implemented act as a logic bomb and a false sense of security. It indicates that a security requirement was known but forgotten during implementation.
**Prevention:** Ensure that any function intended for security checks is fully implemented and tested. Automated tests should verify both the presence and effectiveness of CSRF protection on all state-changing endpoints.
## 2024-05-04 - [Input Type Vulnerability Prevention]
**Vulnerability:** Lack of type checking on user inputs passed to PHP string functions (`trim`, `password_verify`), which can throw fatal `TypeError` exceptions if an array is passed, leading to 500 errors or potential stack trace leaks. Also missing basic HTML stripping in `sanitizeInput`.
**Learning:** In PHP 8+, native functions are strictly typed. We must manually verify `is_string` before using string functions on JSON decoded input.
**Prevention:** Always add `is_string` checks in sanitization and verification wrappers, and use `strip_tags` to prevent basic XSS payloads from persisting.
