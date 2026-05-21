## 2024-05-16 - [Form Input Maxlength Validation]
**Learning:** Adding maxlength constraints and explicit visual character counters mapped via `aria-describedby` prevents backend `mb_strlen` or strict-mode string truncation exceptions while simultaneously improving the experience for screen reader users by providing clear, contextually linked boundaries.
**Action:** When adding user text inputs, always implement explicit `maxlength` attributes corresponding to database limits, and include a visual counter tied to `aria-describedby` for accessible limit awareness.

## 2025-05-18 - [Button Async State Feedback]
**Learning:** Adding a small, animated spinner inside async submission buttons provides critical, immediate visual feedback that the application is processing the request, as relying purely on the `disabled` state and changing the text (e.g., "Сохранение...") is often not prominent enough to assure users that progress is occurring, preventing multiple click attempts and frustration.
**Action:** Always include a visual loading indicator (like `.spinner-small`) paired with an `aria-hidden="true"` attribute alongside the text change inside primary action buttons during asynchronous tasks.

## 2024-05-21 - [Password Visibility Toggle]
**Learning:** Adding a password visibility toggle on login forms significantly reduces user frustration and entry errors, particularly on mobile devices. Using standard unicode emoji combined with `aria-hidden="true"` and dynamic `aria-label`/`title` provides an accessible, dependency-free solution without needing new icons or SVGs.
**Action:** When adding password inputs, wrap the input and a toggle button in a flex container. Use a reactive variable to toggle the input `type` between `password` and `text`, and update the button's accessibility labels dynamically to reflect the current state.
