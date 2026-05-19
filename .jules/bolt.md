## 2024-06-12 - MySQL Indexed Columns and Non-SARGable Functions
**Learning:** Wrapping an indexed column in a function (like `DATE(start_time) = ?`) makes the query non-SARGable. MySQL cannot use the index (`idx_start_time`) on that column and must perform a full table scan.
**Action:** Replace function calls on indexed columns with equivalent range queries (e.g., `start_time >= ? AND start_time < ? + INTERVAL 1 DAY`) so the database engine can effectively use indexes.

## 2026-05-04 - Avoiding O(N) Database Queries and Compilations in Loops
**Learning:** Running $conn->prepare() and subsequent SELECTs inside a loop during bulk creation (like generateSlotsFromTemplate) significantly degrades performance due to repeated statement compilations and database queries. This is a common bottleneck in PHP/MySQL batch operations.
**Action:** Hoist the $conn->prepare() statement outside the loop to compile once, and construct output objects directly in memory from loop variables instead of refetching from the DB after each insert.

## 2026-05-06 - Avoid Date Object Instantiation in Frontend Loops
**Learning:** Instantiating `new Date()` objects within a `.map()` loop (e.g., `response.data.map(slot => new Date(slot.start_time))`) introduces unnecessary memory allocation and processing overhead, making it significantly slower compared to parsing strings directly.
**Action:** When mapping database timestamps returned in a strict format like `YYYY-MM-DD HH:MM:SS`, use string slicing (`substring()`) to extract date and time components instead of heavy Date object parsing.

## 2024-05-07 - Use fetch_all for Faster Database Reads
**Learning:** Iterating over MySQL result sets using a `while ($row = $result->fetch_assoc())` loop executes the array construction in user-land PHP, which is less efficient than using the native `$result->fetch_all(MYSQLI_ASSOC)` method that performs this operation directly in the C layer (mysqlnd).
**Action:** Replace `while` loops that manually append `fetch_assoc()` results to an array with a single `$result->fetch_all(MYSQLI_ASSOC)` call.

## 2024-05-10 - Avoiding N+1 Queries in Bulk Generation
**Learning:** In the `generateSlotsFromTemplate` function, checking for overlapping slots inside the `while` loop generated a separate SQL query for each slot being created. For a large number of slots, this creates a severe N+1 problem and slows down generation significantly, even with a prepared statement.
**Action:** When performing high-frequency database checks within a loop (like slot overlap detection), pre-fetch the relevant dataset for the entire scope (e.g., the full date range) into a PHP array using a single query before the loop begins. Perform the validation logic entirely in-memory to reduce database roundtrips from N to 1.

## 2026-05-11 - Avoiding Global Re-fetches for Pinia CRUD Operations
**Learning:** In the frontend Vue application, dispatching a global re-fetch (e.g., `await this.fetchSlots()`) after every single-item CRUD operation (like create, update, or delete) creates an unnecessary performance bottleneck by triggering redundant network requests and full state recreations.
**Action:** Optimize Pinia stores by mutating the local array state directly using the successful API response (e.g., `this.slots.push(...)` or `this.slots[index] = ...`) instead of re-fetching the entire dataset from the backend API.

## 2024-05-12 - Replacing localeCompare for String Dates
**Learning:** Using `localeCompare` to sort arrays of ISO-like date strings (e.g. `YYYY-MM-DD HH:MM:SS`) adds unnecessary overhead because these strings are strictly formatted and do not require locale-aware sorting logic.
**Action:** Replace `localeCompare` with standard string comparison operators (`<`, `>`) when sorting strictly formatted date or time strings to improve sorting performance.
## 2026-05-14 - Optimize bulk insert performance in PHP with MySQLi transaction
**Learning:** In the booking system API, batch generating slots executed N independent `INSERT` queries within a `while` loop with auto-commit enabled by default. This incurred significant disk I/O and transaction commit overhead for each single row (e.g., generating a full day of 1-minute slots took ~0.64s).
**Action:** When performing high-frequency, non-interdependent bulk database inserts in a loop, wrap the operation in a single database transaction using `$conn->begin_transaction()`, `$conn->commit()`, and `$conn->rollback()` to batch disk syncs, drastically improving performance (reduced insertion time by ~70%).
## 2026-05-16 - Replacing SUM(CASE) with GROUP BY for Indexed Columns
**Learning:** Using `SUM(CASE WHEN status = ... THEN 1 ELSE 0 END)` to aggregate counts by status forces MySQL to perform a full table scan because it must evaluate the expression for every single row. If the column has an index (e.g., `idx_status`), this index is completely ignored.
**Action:** Replace `SUM(CASE)` expressions with `GROUP BY status` queries when summarizing data. This allows the database engine to utilize the index via an index scan (often avoiding a table scan entirely for simple counts) and significantly speeds up aggregations, particularly on large tables. The final summary structure can be reconstructed in the application layer.
## 2024-06-12 - Debugging and Scratchpad Scripts
**Learning:** Leaving temporary debugging or scratchpad scripts (like testing filesorts or index performance) in the repository root can expose sensitive database connection details or create security vulnerabilities if deployed to a web-accessible environment. It also clutters the codebase and fails code review.
**Action:** Always strictly delete any temporary files or scripts created for testing logic or performance before submitting a PR.
