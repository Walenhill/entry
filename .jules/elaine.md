
## 2026-09-07 - Extracted duplicated CSS classes into global style
**Learning:** When performing small cleanups across Vue components, extracting duplicated style blocks (like loading spinners or state containers) into a global `style.css` file efficiently reduces code duplication and bundle size.
**Action:** Use global CSS for shared, identical UI element styles instead of duplicating them in scoped component styles.
