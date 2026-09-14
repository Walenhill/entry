
## 2026-09-07 - Extracted duplicated CSS classes into global style
**Learning:** When performing small cleanups across Vue components, extracting duplicated style blocks (like loading spinners or state containers) into a global `style.css` file efficiently reduces code duplication and bundle size.
**Action:** Use global CSS for shared, identical UI element styles instead of duplicating them in scoped component styles.

## 2026-09-09 - Enhance input character limit accessibility
**Learning:** When users hit the `maxlength` limit of an input, the browser silently ignores further typing, which can be confusing for visual users and completely invisible to screen reader users.
**Action:** Use reactive class bindings (like `text-danger`) and ARIA attributes (`:aria-invalid`, `aria-live="polite"` with visually hidden text) to explicitly notify all users when an input limit is reached without requiring custom JS validation logic.

## 2026-09-14 - Deduplicate API error message extraction
**Learning:** When multiple API consumers manually extract error messages from deeply nested fields like `err.response?.data?.error`, it leads to fragile, duplicated fallback logic across the codebase.
**Action:** Extract error parsing logic into a central, reusable utility function (e.g., `extractErrorMessage`) inside the API error handler module, and use it consistently wherever manual error parsing is needed.
