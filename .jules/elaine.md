
## 2026-09-07 - Extracted duplicated CSS classes into global style
**Learning:** When performing small cleanups across Vue components, extracting duplicated style blocks (like loading spinners or state containers) into a global `style.css` file efficiently reduces code duplication and bundle size.
**Action:** Use global CSS for shared, identical UI element styles instead of duplicating them in scoped component styles.

## 2026-09-09 - Enhance input character limit accessibility
**Learning:** When users hit the `maxlength` limit of an input, the browser silently ignores further typing, which can be confusing for visual users and completely invisible to screen reader users.
**Action:** Use reactive class bindings (like `text-danger`) and ARIA attributes (`:aria-invalid`, `aria-live="polite"` with visually hidden text) to explicitly notify all users when an input limit is reached without requiring custom JS validation logic.
