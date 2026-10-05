## 2024-05-18 - Added input validation to slots/generate API endpoint
**Learning:** When validating numeric API payload inputs in PHP that can legitimately be `0` (e.g., `start_hour`, `duration`), use `isset()` rather than `empty()` to avoid falsely rejecting valid zero values.
**Action:** Always inspect the domain semantics of numeric fields before using `empty()` for validation. Use `isset()` for fields where `0` is a valid input.
## 2024-05-20 - Centralized Error Response in Database Connection
**Learning:** The PHP backend manually handled `http_response_code(500); echo json_encode(...);` and `exit;` in multiple places such as DB connection failure logic, while already possessing a centralized `jsonResponse()` helper designed for this purpose. Centralizing these helps maintain DRY principles, header consistency, and cache controls.
**Action:** When implementing or refactoring PHP backend API endpoints, default to using the `jsonResponse` helper for JSON return consistency rather than manual echos, which can easily drift from architectural standards.
## 2025-02-12 - Add missing updateSlot API method
**Learning:** Even if a backend API endpoint (like `PUT /slots/{id}`) is properly implemented and documented in the README, the corresponding frontend API client methods (like inside `slotsApi`) might be missing. This causes integration friction and incomplete API wrappers.
**Action:** Always cross-reference the backend routes with the frontend API client definition when looking for simple API consistency improvements.
