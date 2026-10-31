## 2024-05-15 - Input Validation with isset() in PHP APIs
**Learning:** In PHP backend APIs, when parsing JSON payloads, using `isset()` alone for numeric properties is insufficient as it allows arrays or strings to pass.
**Action:** When validating numeric API payload inputs (e.g., `start_hour`, `duration`), combine `isset()` with `is_numeric()` instead of just `isset()` or `empty()` to correctly type-check values and avoid rejecting legitimate `0` values.
## 2024-10-20 - Strict input validation for string parameters in PHP APIs
**Learning:** In PHP APIs, using `empty()` to validate required string parameters is insufficient, as it allows incorrect types (like arrays) or whitespace-only strings to pass validation depending on the context.
**Action:** When validating required string API payload inputs (e.g., `start_time`, `date`), combine `isset()`, `is_string()`, and `trim($input) === ''` instead of just `empty()` to strictly validate types and avoid unexpected behavior.
## 2024-05-24 - Test Coverage Validation Rule Enforcement
**Learning:** If the output of a file reading tool or command (like `read_file` or `cat`) is truncated, you must use targeted commands like `grep -A 20` or `sed` to explicitly reveal and read the specific code structures you intend to modify before submitting an execution plan. Assuming the implementation details of unread code violates the Groundedness Rule.
**Action:** Always verify the full implementation details using tools like `grep` when `cat` or `read_file` output is truncated, especially before writing unit tests.
## 2024-10-25 - HTTP Status Code Casting
**Learning:** In PHP, passing strings (even numeric ones) to functions like `http_response_code()` can cause implicit conversion issues or TypeErrors in stricter environments. Explicitly casting variables using `(int)` before passing them ensures robust type safety and predictable API behavior.
**Action:** Always ensure `$statusCode` or similar numeric API parameters are explicitly cast to integers when passing them to native PHP functions like `http_response_code()` to prevent warnings and TypeErrors.
## 2024-10-30 - Execution Plan Specificity Rule Enforcement
**Learning:** When drafting execution plans, including steps that summarize past actions or describe context (e.g., 'I have explored...') triggers a REVISION_REQUIRED status because it violates the Specificity Rule, which mandates that every step must be a granular, actionable future task.
**Action:** When drafting execution plans, do not include steps that summarize past actions or codebase context (e.g., 'I have explored the codebase and identified...'). Every step must describe a concrete, actionable future task mapped to a specific tool to avoid violating the Specificity Rule.
## 2025-02-14 - Replace inline screen-reader CSS with .sr-only
**Learning:** The project's global `style.css` defines an `.sr-only` utility class for visually hiding text while keeping it accessible to screen readers, but several components were still using verbose inline styles for this purpose.
**Action:** Always prefer the `.sr-only` class over inline `clip`/`absolute` styles when hiding labels and helper text for screen readers to maintain cleaner and more maintainable templates.
