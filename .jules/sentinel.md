## 2024-05-24 - High Severity Vulnerabilities in Axios (CVE-2023-45857, CVE-2024-3933, etc.)
**Vulnerability:** The frontend application used an outdated version of `axios` (^1.19.0) which had multiple known high-severity vulnerabilities, including prototype pollution and SSRF.
**Learning:** These vulnerabilities allow attackers to hijack requests or leak data, especially if untrusted input is passed to axios configurations.
**Prevention:** Keep critical dependencies like `axios` up-to-date and run dependency audits regularly (`pnpm audit`).
## 2023-10-21 - Insecure Session Destruction
**Vulnerability:** The application was calling `session_destroy()` without emptying the `$_SESSION` array or invalidating the session cookie (`PHPSESSID`). This allowed old session cookies to potentially be reused or left on the client's browser, increasing the risk of session hijacking or fixation attacks.
**Learning:** `session_destroy()` only removes the session data from the server's storage; it does not clear the `$_SESSION` superglobal for the current request, nor does it delete the session cookie from the client's browser.
**Prevention:** To fully destroy a session, always empty `$_SESSION` (`$_SESSION = array();`) and use `setcookie` to expire the session cookie with a past time (e.g., `time() - 42000`) before calling `session_destroy()`.
