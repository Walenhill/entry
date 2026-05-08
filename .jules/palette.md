## 2026-05-04 - Form Required Indicators and Keyboard Navigation
**Learning:** Found that required fields (`<input required>`) lacked visual indicators (`*`) in some forms, causing inconsistency with `BookingModal.vue`. Additionally, keyboard navigation lacked clear visual cues for focus states on links and buttons.
**Action:** Always ensure that `required` fields have a matching visual indicator (like `<span class="text-danger">*</span>`) in their labels. Added `:focus-visible` to `a` and `.btn` elements in the global CSS to provide strong, visible focus rings using existing design system variables (`var(--accent-secondary)`).

## 2026-05-04 - Modal Keyboard Navigation and Autofill Optimization
**Learning:** Found that custom modals (like `BookingModal.vue`) lacked keyboard navigation support, trapping keyboard users or making it difficult to close without a mouse. Also learned that `type="tel"` inputs lacking autocomplete significantly degraded mobile user experience since the numeric keypad isn't prioritized for plain text inputs.
**Action:** Always add a global `keydown` event listener to close modal components via the `Escape` key. Use `autocomplete` attributes consistently on standard fields (like `name` and `tel`) and ensure phone fields utilize `type="tel"` to trigger optimized mobile keyboards.
## 2026-05-08 - Focus Management and Empty States
**Learning:** Custom Vue modals lack native focus management. It's critical to manually focus the first input using `nextTick()` and template refs to ensure keyboard accessibility. Furthermore, empty states without an immediate, contextual CTA force users to hunt for primary actions.
**Action:** Always implement automatic focus for the first interactive element when custom modals open, and ensure empty states always contain an inline primary action button.
