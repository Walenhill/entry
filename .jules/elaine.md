## 2024-05-15 - Input Validation with isset() in PHP APIs
**Learning:** In PHP backend APIs, when parsing JSON payloads, using `isset()` alone for numeric properties is insufficient as it allows arrays or strings to pass.
**Action:** When validating numeric API payload inputs (e.g., `start_hour`, `duration`), combine `isset()` with `is_numeric()` instead of just `isset()` or `empty()` to correctly type-check values and avoid rejecting legitimate `0` values.
