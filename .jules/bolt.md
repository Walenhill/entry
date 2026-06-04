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
## 2024-05-18 - Eliminating Filesort with Composite Indexes
**Learning:** When a query filters by one column (e.g., `status = 'available'`) and sorts by another (`ORDER BY start_time ASC`), single-column indexes force MySQL to perform an expensive `filesort` operation on the filtered results.
**Action:** Add a composite index on both columns (e.g., `(status, start_time)`) so the database engine can both filter and traverse the index in sorted order, completely eliminating filesort overhead.
## 2024-06-12 - Avoiding O(N) Array Modification in PHP by Selecting Specific Columns
**Learning:** In PHP, iterating over a large array to `unset()` specific elements (like sensitive database columns) introduces O(N) overhead in user-land memory. This is inefficient compared to simply querying only the necessary columns directly from the database.
**Action:** Instead of fetching all columns (`SELECT *`) and modifying the result set in PHP, selectively fetch columns directly from SQL (e.g. `SELECT id, start_time, end_time, description, status, created_at`) based on the required access level, offloading the optimization to the database engine.
## 2026-05-19 - Eliminating Redundant Database Reads Post-Mutation
**Learning:** In backend CRUD endpoints (create, book, cancel, update), it's a common anti-pattern to successfully execute an `INSERT` or `UPDATE` query and then immediately fire a `SELECT` query (e.g., `getSlotById($id)`) just to return the updated record to the client. This introduces an unnecessary database roundtrip (an N+1 read), effectively doubling the queries required for write operations.
**Action:** When a mutation succeeds and you already know the modified data (because it came from user input or was pre-fetched before the update), construct or mutate the output object directly in memory and return it. This avoids the redundant post-mutation `SELECT` query and reduces latency.
## 2026-05-27 - Preventing O(N) re-renders in unpaginated lists with v-memo
**Learning:** In the `SlotsView` Vue component, parent state updates (such as changing `cancelingSlotId` during an async cancellation operation) trigger a re-render of every single `<SlotCard>` in the `v-for` loop because they share the parent's reactive scope. This introduces an unnecessary O(N) rendering bottleneck, especially when the slots list is large and unpaginated.
**Action:** Prevent O(N) list re-renders by leveraging the `v-memo` directive on child components inside a `v-for`. Pass a dependency array explicitly declaring the specific reactive properties that require the item to re-render (e.g., `v-memo="[slot.is_booked, cancelingSlotId === slot.id, ...]"`). Ensure to destructure the mutable proxy fields to primitives to guarantee `v-memo`'s shallow equality check works properly.
## 2026-05-30 - Making queries SARGable with IN clauses
**Learning:** Using negative conditions like `status != 'cancelled'` makes the query non-SARGable, forcing a full table scan and ignoring indexes on the column.
**Action:** Replace inequality operators with `IN` clauses representing the explicit subset of required states (e.g., `status IN ('available', 'booked')`) to allow MySQL to utilize indexes properly.

## 2026-06-04 - O(N) Array Insertion in Sorted Pinia Stores
**Learning:** When adding a single item to an already sorted array in Pinia, using `push()` followed by `sort()` forces an O(N log N) operation and executes the comparator function multiple times unnecessarily.
**Action:** Optimize insertions into sorted arrays by using `findIndex()` to determine the correct insertion point, followed by `splice()`. This reduces the complexity to O(N) and prevents unnecessary sorting overhead.
