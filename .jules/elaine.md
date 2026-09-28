## 2024-05-18 - Added input validation to slots/generate API endpoint
**Learning:** When validating numeric API payload inputs in PHP that can legitimately be `0` (e.g., `start_hour`, `duration`), use `isset()` rather than `empty()` to avoid falsely rejecting valid zero values.
**Action:** Always inspect the domain semantics of numeric fields before using `empty()` for validation. Use `isset()` for fields where `0` is a valid input.
