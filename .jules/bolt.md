## 2024-06-12 - MySQL Indexed Columns and Non-SARGable Functions
**Learning:** Wrapping an indexed column in a function (like `DATE(start_time) = ?`) makes the query non-SARGable. MySQL cannot use the index (`idx_start_time`) on that column and must perform a full table scan.
**Action:** Replace function calls on indexed columns with equivalent range queries (e.g., `start_time >= ? AND start_time < ? + INTERVAL 1 DAY`) so the database engine can effectively use indexes.

## 2026-05-04 - Avoiding O(N) Database Queries and Compilations in Loops
**Learning:** Running $conn->prepare() and subsequent SELECTs inside a loop during bulk creation (like generateSlotsFromTemplate) significantly degrades performance due to repeated statement compilations and database queries. This is a common bottleneck in PHP/MySQL batch operations.
**Action:** Hoist the $conn->prepare() statement outside the loop to compile once, and construct output objects directly in memory from loop variables instead of refetching from the DB after each insert.
