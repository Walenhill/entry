## 2026-05-04 - Form Required Indicators and Keyboard Navigation
**Learning:** Found that required fields (`<input required>`) lacked visual indicators (`*`) in some forms, causing inconsistency with `BookingModal.vue`. Additionally, keyboard navigation lacked clear visual cues for focus states on links and buttons.
**Action:** Always ensure that `required` fields have a matching visual indicator (like `<span class="text-danger">*</span>`) in their labels. Added `:focus-visible` to `a` and `.btn` elements in the global CSS to provide strong, visible focus rings using existing design system variables (`var(--accent-secondary)`).

## 2026-05-04 - Modal Keyboard Navigation and Autofill Optimization
**Learning:** Found that custom modals (like `BookingModal.vue`) lacked keyboard navigation support, trapping keyboard users or making it difficult to close without a mouse. Also learned that `type="tel"` inputs lacking autocomplete significantly degraded mobile user experience since the numeric keypad isn't prioritized for plain text inputs.
**Action:** Always add a global `keydown` event listener to close modal components via the `Escape` key. Use `autocomplete` attributes consistently on standard fields (like `name` and `tel`) and ensure phone fields utilize `type="tel"` to trigger optimized mobile keyboards.

## 2024-05-09 - Adding CTA in Vue empty state
**Learning:** Adding a contextual CTA (Call-to-Action) button to empty states improves UX by preventing users from having to hunt for global action buttons.
**Action:** Next time I work on a list view with an empty state, I will ensure there is a clear CTA for the user to add an item.

## 2026-05-10 - Mapping Custom Visual Components to ARIA Roles
**Learning:** Found that custom visual components acting as progress bars (e.g., using `div` widths for percentages) lack semantic meaning for screen readers. Additionally, decorative emojis in stat cards read out confusingly, and visual percentage text duplicates information when ARIA attributes are introduced.
**Action:** Always map custom visual indicators (like load bars) to their semantic equivalents using `role="progressbar"`, `aria-valuenow`, `aria-valuemin`, and `aria-valuemax`. Use `aria-hidden="true"` generously on decorative text (like emojis) and visual text that duplicates ARIA information to reduce screen reader noise.
## 2024-05-11 - Dynamic ARIA Context in Card Actions
**Learning:** Screen reader users lose context when navigating through multiple identical actions (e.g., "Забронировать") in a list of cards if the actions don't reference the card's specific data (like date and time). Emoji icons next to or inside these buttons (like "🗑") often produce redundant or confusing readout if not explicitly hidden.
**Action:** Always include item-specific data (e.g., `${slot.date}`, `${slot.start_time}`) within the `:aria-label` of generic action buttons inside loops/cards, and wrap decorative text or emoji icons in `<span aria-hidden="true">`.
