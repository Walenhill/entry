## 2024-05-15 - Testing Password Toggle Accessibility
**Learning:** Using an emoji as the content for a toggle button without `aria-hidden="true"` causes screen readers to redundantly announce the emoji (e.g., "Eye symbol"), clashing with the primary `aria-label` which provides the actual functional context.
**Action:** Always wrap visual icons or emojis inside interactive elements with `<span aria-hidden="true">` to ensure screen readers only announce the semantic `aria-label` or `title`.
