## 2024-10-24 - Accessibility prefers-reduced-motion
**Learning:** Adding `prefers-reduced-motion` explicitly via Playwright's `page.emulate_media(reduced_motion='reduce')` validates accessibility settings. Also, CSS `@media (prefers-reduced-motion: reduce)` globally applied via wildcard selectors (`*`, `*::before`, `*::after`) must use `!important` to reliably override specific scoped and component styles.
**Action:** Use Playwright's `page.emulate_media` rather than context settings for CSS validation of user preferences, and favor global wildcards with `!important` for accessibility style overrides.
## 2024-05-15 - Handle null error in extractErrorMessage
**Learning:** Even with optional chaining (`error.response?.data`), passing `null` as the `error` parameter will throw a `TypeError` because optional chaining only protects properties of the object, not the base object itself if it's evaluated without the `?`.
**Action:** Always ensure the base object passed to an error extraction utility is truthy, or use optional chaining from the very root (e.g., `error?.response?.data`) to avoid unexpected crashes.
## 2023-10-11 - Safe error handling in Vue API client
**Learning:** In the Axios response interceptor, the `error` object and its properties like `error.response` or `error.config` can be null or undefined, causing Uncaught TypeErrors (e.g., `Cannot read properties of null`) when trying to access `.status` or `.url`.
**Action:** Use optional chaining (`error?.response?.status` and `error?.config?.url`) when extracting error properties to safely handle network failures or interrupted requests without crashing the interceptor.
