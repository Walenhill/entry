## 2024-05-15 - Input Validation with isset() in PHP APIs
**Learning:** In PHP backend APIs, when parsing JSON payloads, using `isset()` alone for numeric properties is insufficient as it allows arrays or strings to pass.
**Action:** When validating numeric API payload inputs (e.g., `start_hour`, `duration`), combine `isset()` with `is_numeric()` instead of just `isset()` or `empty()` to correctly type-check values and avoid rejecting legitimate `0` values.
## 2024-10-20 - Strict input validation for string parameters in PHP APIs
**Learning:** In PHP APIs, using `empty()` to validate required string parameters is insufficient, as it allows incorrect types (like arrays) or whitespace-only strings to pass validation depending on the context.
**Action:** When validating required string API payload inputs (e.g., `start_time`, `date`), combine `isset()`, `is_string()`, and `trim($input) === ''` instead of just `empty()` to strictly validate types and avoid unexpected behavior.
