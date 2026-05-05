## 2026-05-04 - Form Required Indicators and Keyboard Navigation
**Learning:** Found that required fields (`<input required>`) lacked visual indicators (`*`) in some forms, causing inconsistency with `BookingModal.vue`. Additionally, keyboard navigation lacked clear visual cues for focus states on links and buttons.
**Action:** Always ensure that `required` fields have a matching visual indicator (like `<span class="text-danger">*</span>`) in their labels. Added `:focus-visible` to `a` and `.btn` elements in the global CSS to provide strong, visible focus rings using existing design system variables (`var(--accent-secondary)`).

## 2025-05-05 - [Modal Accessibility & UX]
**Learning:** Keyboard accessibility (Escape key to close) and autofocus on the first input inside modals significantly improve the user flow, and must be manually implemented in custom Vue components. ARIA attributes (role="dialog", aria-modal="true") are crucial for screen readers handling these overlays.
**Action:** Always include an Escape key listener that is bound/unbound based on modal visibility, use `nextTick` autofocus on primary inputs, and standard dialog ARIA attributes when building custom modal components instead of relying purely on visual overlays.
