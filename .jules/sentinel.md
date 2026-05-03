## 2024-05-03 - [Missing CSRF Protection Implementation]
**Vulnerability:** The backend endpoints for POST, PUT, and DELETE explicitly called a `verifyCsrfToken()` function, but this function was not defined anywhere in the application. This resulted in a complete lack of CSRF protection and caused PHP Fatal Errors whenever state-changing endpoints were hit.
**Learning:** Security measures that are called but not implemented act as a logic bomb and a false sense of security. It indicates that a security requirement was known but forgotten during implementation.
**Prevention:** Ensure that any function intended for security checks is fully implemented and tested. Automated tests should verify both the presence and effectiveness of CSRF protection on all state-changing endpoints.
