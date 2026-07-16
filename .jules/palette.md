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

## 2025-05-12 - Prevent Past Dates and Enhance Accessibility in Create Slot Form
**Learning:** Native `<input type="date">` elements do not restrict past dates by default, which can lead to accidental bookings in the past. Additionally, dynamically inserted forms lack initial focus, hampering keyboard navigation.
**Action:** Always add a dynamic `min` attribute (e.g., `min="today"`) to date inputs for future-oriented data (like bookings), and use `ref` with `onMounted` to explicitly focus the first input when a component mounts to establish an immediate keyboard interaction context.

## 2026-05-13 - Modal ARIA Semantics and Screen Reader Context
**Learning:** Found that custom modal implementations (like `BookingModal.vue`) visually behave like dialogs but lack the necessary HTML semantics. Without `role="dialog"` and `aria-modal="true"`, screen readers do not recognize them as distinct dialogs and do not trap virtual focus, causing users to accidentally read content underneath the modal.
**Action:** Always add `role="dialog"`, `aria-modal="true"`, and `aria-labelledby` (pointing to the modal's title ID) to the container of any custom modal component to ensure proper screen reader behavior.

## 2025-05-27 - [Focus Restoration and Escape Exits in Dynamic Forms]
**Learning:** Found that closing dynamically inserted UI components (like inline forms or modals) often drops keyboard focus to the `body`, forcing screen-reader and keyboard users to tab through the entire page again to find their previous location. Additionally, inline pseudo-modals lacked standard `Escape` key exit patterns.
**Action:** Always capture `document.activeElement` before mounting a dynamic form/modal and restore focus to it using `await nextTick()` after unmounting. Additionally, standard exit interactions like the `Escape` key must be universally supported across both full-page modals and inline dynamic form insertions.

## 2026-05-14 - Mobile Menu Focus Management and ARIA States
**Learning:** Mobile menu toggle buttons and close buttons lack necessary ARIA attributes (`aria-expanded`, `aria-controls`), making it impossible for screen reader users to understand their purpose or state. Furthermore, opening and closing the menu drops keyboard focus, requiring users to navigate from the start of the page again.
**Action:** Always add `aria-expanded` and `aria-controls` to mobile menu toggle and close buttons. Implement focus management by capturing the toggle button's reference and restoring focus to it when the menu is closed, and explicitly moving focus to the close button when the menu opens.

## 2026-05-15 - ARIA Labels for Layout Containers
**Learning:** Found that structural layout elements like `<nav>` in `DashboardLayout.vue` and visual overlay backdrops `.mobile-backdrop` lacked standard ARIA semantics, making screen reader navigation confusing.
**Action:** Always add a descriptive `aria-label` (e.g., "Основная навигация") to primary `<nav>` components and ensure completely visual overlays without content trap `aria-hidden="true"` so screen readers ignore them.

## 2026-05-16 - Screen Reader Context for Telephone Links
**Learning:** Found that `tel:` links showing only numbers (e.g. `<a href="tel:1234567890">1234567890</a>`) are read by screen readers as arbitrary sequences of digits without explaining what happens when clicked or who the number belongs to. This lacks context for visually impaired users compared to sighted users who infer context from neighboring layout elements.
**Action:** Always add an explicit `:aria-label` to phone links that includes contextual actions and information, such as `aria-label="Позвонить клиенту: {{ client.phone }}"`, so screen readers can announce the purpose of the link.

## 2024-06-28 - Disabled Action Buttons Accessibility
**Learning:** Disabled action buttons (like icon-only buttons or modal close buttons) become inert and drop their tooltips, which can confuse users and reduce accessibility during asynchronous operations.
**Action:** When disabling action buttons, do not completely replace the existing `aria-label` or apply a `title` directly to the disabled `<button>`. Instead, wrap the disabled button in a `<span>` to hold the `title` attribute, and append the blocked state to the original `aria-label` (e.g., `aria-label="[Action] - Действие недоступно во время загрузки"`) to preserve context and accessibility.

## 2026-07-16 - Wrapping Text-Based Visual Icons
**Learning:** Found that using raw text characters as visual icons (like '×' for close or '☰' for menu) inside interactive elements causes screen readers to redundantly announce the literal character alongside the element's `aria-label`, reducing clarity.
**Action:** Always wrap visual text characters used as icons in a `<span aria-hidden="true">` element to hide them from screen readers, relying entirely on the `aria-label` for semantic meaning.
