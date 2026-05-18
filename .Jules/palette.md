## 2024-05-16 - [Form Input Maxlength Validation]
**Learning:** Adding maxlength constraints and explicit visual character counters mapped via `aria-describedby` prevents backend `mb_strlen` or strict-mode string truncation exceptions while simultaneously improving the experience for screen reader users by providing clear, contextually linked boundaries.
**Action:** When adding user text inputs, always implement explicit `maxlength` attributes corresponding to database limits, and include a visual counter tied to `aria-describedby` for accessible limit awareness.

## 2025-05-18 - [Button Async State Feedback]
**Learning:** Adding a small, animated spinner inside async submission buttons provides critical, immediate visual feedback that the application is processing the request, as relying purely on the `disabled` state and changing the text (e.g., "Сохранение...") is often not prominent enough to assure users that progress is occurring, preventing multiple click attempts and frustration.
**Action:** Always include a visual loading indicator (like `.spinner-small`) paired with an `aria-hidden="true"` attribute alongside the text change inside primary action buttons during asynchronous tasks.
