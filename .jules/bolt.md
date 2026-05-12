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
