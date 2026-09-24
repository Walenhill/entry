
## 2026-09-07 - Extracted duplicated CSS classes into global style
**Learning:** When performing small cleanups across Vue components, extracting duplicated style blocks (like loading spinners or state containers) into a global `style.css` file efficiently reduces code duplication and bundle size.
**Action:** Use global CSS for shared, identical UI element styles instead of duplicating them in scoped component styles.

## 2026-09-09 - Enhance input character limit accessibility
**Learning:** When users hit the `maxlength` limit of an input, the browser silently ignores further typing, which can be confusing for visual users and completely invisible to screen reader users.
**Action:** Use reactive class bindings (like `text-danger`) and ARIA attributes (`:aria-invalid`, `aria-live="polite"` with visually hidden text) to explicitly notify all users when an input limit is reached without requiring custom JS validation logic.

## 2026-09-14 - Deduplicate API error message extraction
**Learning:** When multiple API consumers manually extract error messages from deeply nested fields like `err.response?.data?.error`, it leads to fragile, duplicated fallback logic across the codebase.
**Action:** Extract error parsing logic into a central, reusable utility function (e.g., `extractErrorMessage`) inside the API error handler module, and use it consistently wherever manual error parsing is needed.

## 2026-09-16 - Sync server-side regex validation with frontend
**Learning:** Backend validation should mirror frontend HTML5 pattern checks (e.g., regex  for non-empty strings) to prevent bypassing the validation via direct API calls.
**Action:** Use targeted file reads via  on the frontend file to accurately sync the correct expression on the backend without hallucinating or guessing patterns.
## 2026-09-16 - Sync server-side regex validation with frontend
**Learning:** Backend validation should mirror frontend HTML5 pattern checks (e.g., regex `.*\S+.*` for non-empty strings) to prevent bypassing the validation via direct API calls.
**Action:** Use targeted file reads via `grep pattern` on the frontend file to accurately sync the correct expression on the backend without hallucinating or guessing patterns.
## 2026-09-17 - [Add frontend unit tests for auth store]
**Learning:** [It's important to provide complete test coverage for frontend stores, especially ones handling auth, because API request behaviors and local storage updates are critical for app state.]
**Action:** [When targeting small test coverage improvement, make sure to add comprehensive coverage for a specific logical unit (like Pinia store actions).]

## 2024-03-22 - Add frontend unit tests for auth store
**Learning:** It's important to provide complete test coverage for frontend stores and APIs, especially ones handling auth, because API request behaviors and local storage updates are critical for app state.
**Action:** When targeting small test coverage improvement, make sure to add comprehensive coverage for a specific logical unit (like the auth API authentication check).
## 2026-09-20 - Sync server-side regex validation with frontend
**Learning:** Backend validation should mirror frontend HTML5 pattern checks (e.g., regex `.*\S+.*`) to prevent bypassing the validation via direct API calls. When bringing HTML5 validation to PHP using `preg_match` with the `.` wildcard, you must use the `/s` modifier (PCRE_DOTALL) to ensure it can handle multiline strings correctly, otherwise it incorrectly rejects strings containing newlines.
**Action:** Use targeted file reads via `grep pattern` on the frontend file to accurately sync the correct expression on the backend without hallucinating or guessing patterns, and add `/s` in PHP if needed for newline support.
## 2026-09-24 - Deduplicate API error message extraction in slots store
**Learning:** When multiple actions in a Pinia store manually extract error messages using fallback strings, it leads to inconsistent error reporting and duplicated logic. It's better to leverage a central utility like `extractErrorMessage` to provide robust handling of backend validation responses.
**Action:** When updating store actions, ensure they import and utilize `extractErrorMessage` for setting component-level error states rather than hardcoding default failure strings.
