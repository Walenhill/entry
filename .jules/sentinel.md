## 2024-05-24 - High Severity Vulnerabilities in Axios (CVE-2023-45857, CVE-2024-3933, etc.)
**Vulnerability:** The frontend application used an outdated version of `axios` (^1.19.0) which had multiple known high-severity vulnerabilities, including prototype pollution and SSRF.
**Learning:** These vulnerabilities allow attackers to hijack requests or leak data, especially if untrusted input is passed to axios configurations.
**Prevention:** Keep critical dependencies like `axios` up-to-date and run dependency audits regularly (`pnpm audit`).
