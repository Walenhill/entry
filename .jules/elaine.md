## 2025-02-14 - Fix TypeError during fetchSlots
**Learning:** Checking for `response.data.success` directly on an API response can throw a `TypeError: Cannot read properties of null` if the backend returns an empty response (like `204 No Content` which resolves to `null`).
**Action:** Use optional chaining `response.data?.success` to safely parse fields on potentially null objects in API responses to prevent application crashes.
