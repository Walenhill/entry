## 2025-02-14 - Fix TypeError during fetchSlots
**Learning:** Checking for `response.data.success` directly on an API response can throw a `TypeError: Cannot read properties of null` if the backend returns an empty response (like `204 No Content` which resolves to `null`).
**Action:** Use optional chaining `response.data?.success` to safely parse fields on potentially null objects in API responses to prevent application crashes.

## 2024-06-12 - Date Format Input Validation
**Learning:** The `/slots` endpoint accepted a `date` query parameter but didn't enforce a strict format (`YYYY-MM-DD`). Adding a simple regex check prevents malformed queries and potential application errors down the line.
**Action:** Always validate the format of query parameters, especially dates and numbers, to ensure they conform to expected standards before they are processed by the backend.
