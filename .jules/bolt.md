## 2024-06-12 - MySQL Indexed Columns and Non-SARGable Functions
**Learning:** Wrapping an indexed column in a function (like `DATE(start_time) = ?`) makes the query non-SARGable. MySQL cannot use the index (`idx_start_time`) on that column and must perform a full table scan.
**Action:** Replace function calls on indexed columns with equivalent range queries (e.g., `start_time >= ? AND start_time < ? + INTERVAL 1 DAY`) so the database engine can effectively use indexes.
