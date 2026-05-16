## 2024-05-16 - [Form Input Maxlength Validation]
**Learning:** Adding maxlength constraints and explicit visual character counters mapped via `aria-describedby` prevents backend `mb_strlen` or strict-mode string truncation exceptions while simultaneously improving the experience for screen reader users by providing clear, contextually linked boundaries.
**Action:** When adding user text inputs, always implement explicit `maxlength` attributes corresponding to database limits, and include a visual counter tied to `aria-describedby` for accessible limit awareness.
