## 2023-11-20 - Adding Icons for Links Without Modifying Global Styles
**Learning:** Adding subtle emoji prefixes (`📞`) and title tooltips (`title="Нажмите, чтобы позвонить"`) with inline layout styles (`display: inline-flex; align-items: center; gap: 0.25rem;`) is an effective way to improve the clickability and affordance of generic links without relying on external icon libraries or adding custom classes to global stylesheets. Hiding emojis from screen readers (`aria-hidden="true"`) ensures accessibility tools read out the custom `aria-label` correctly.
**Action:** Use this pattern to quickly boost clarity on actionable text when you are constrained from adding global utility classes or SVG components.

## 2024-03-24 - Vue SPA Route Transitions and Reduced Motion
**Learning:** Adding a subtle fade transition to `<router-view>` via `<transition name="page-fade" mode="out-in">` drastically improves the "feel" of a Vue SPA with minimal code. However, any animated transition that applies to the entire page content must include `@media (prefers-reduced-motion: reduce)` to disable it, otherwise it can cause accessibility and usability issues for users sensitive to motion.
**Action:** Use this pattern to add polish to SPAs, always ensuring `prefers-reduced-motion` is paired with page-level transitions.
## 2026-09-24 - Data Table Keyboard Accessibility & Scanability
**Learning:** Discovered that raw HTML tables lack descriptive names for assistive technologies, and row-level hover states were missing, making horizontal scanning difficult for sighted users.
**Action:** Always associate data tables with their headings using `aria-labelledby` and add `:hover` states to table rows (respecting `prefers-reduced-motion`) to improve visual scanability.

## 2024-10-13 - Adding explicit form labeling
**Learning:** Adding explicit `aria-labelledby` linking forms to their respective headings (like "Вход в панель" or "Создать новый слот") provides crucial screen reader context, making it easier for users using assistive technologies to understand the form's purpose immediately.
**Action:** Always verify if forms have an implicit or explicit label. If missing, link them to the nearest descriptive heading using `id` and `aria-labelledby`.

## 2024-10-13 - Clearing field-specific error states dynamically
**Learning:** In Vue forms, field-specific error states (e.g., a "wrong password" message) can persist even after the user starts typing a correction. This creates a confusing UX where the user sees an error for input they are actively modifying.
**Action:** To improve frontend UX in Vue forms, clear field-specific error states (e.g., `@input="error = ''"`) as soon as the user begins modifying the input, preventing stale error messages from persisting on the screen.
## 2024-05-14 - Modal Focus Trapping
**Learning:** By default, Vue modals that use simple `v-if` overlay layers do not prevent keyboard focus from escaping into the underlying page content when users press Tab, which violates WCAG 2.1 guidelines for focus management in dialogs.
**Action:** Always implement a manual focus trap within custom modal components by listening for the 'Tab' keydown event, querying all focusable elements (`button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])`), and programmatically cycling focus between the first and last elements based on the Shift modifier.
## 2026-10-06 - Interactive Table Rows Styling
**Learning:** Found that table rows containing interactive elements (like phone links) only showed background-color highlighting on `:hover`, excluding keyboard-only users who tab through these links.
**Action:** Pair `:hover` with `:focus-within` on table rows that act as interactive containers, ensuring keyboard users receive the same visual elevation and context cues as mouse users.

## 2026-10-08 - Focus Trapping in Off-Canvas Menus
**Learning:** Implementing off-canvas mobile menus (like sidebars) using absolute/fixed positioning requires a manual focus trap. Otherwise, keyboard users can 'Tab' out of the visible menu and invisibly focus on underlying page content, which breaks accessibility.
**Action:** Whenever creating a mobile menu or off-canvas element that visually overlays the main content, ensure a keyboard event listener captures 'Tab' to cycle focus cyclically between the menu's focusable elements until it is closed.

## 2026-10-12 - Dark Mode Text and Link Contrast Verification
**Learning:** Default Tailwind/Tailwind-like color palettes often fail WCAG AA contrast (4.5:1) when applied as text or links on dark backgrounds (e.g., `#27272a` or `#18181b`). For instance, `#ef4444` and `#71717a` have ~3.95:1 and ~3.08:1 contrast, rendering them illegible. Additionally, buttons with danger backgrounds need appropriately dark backgrounds (`#dc2626` and `#b91c1c`) to support white text.
**Action:** Always manually calculate or verify the contrast ratio of text and link colors on dark surfaces, preferring lighter shades (`#fca5a5`, `#a1a1aa`) over mid-tones for typography.

## 2024-10-13 - Adding physical feedback for form errors
**Learning:** Using a subtle CSS `shake` animation on a form container provides intuitive physical feedback for validation or authentication failures (like incorrect passwords), immediately drawing the user's attention without relying solely on color (which helps color-blind users). However, this must be paired with `@media (prefers-reduced-motion: reduce)` to disable the shaking, as such sudden movements can cause discomfort for users with motion sensitivity.
**Action:** When adding motion-based UI feedback (like shaking error states), always wrap the keyframes or animation application in a `prefers-reduced-motion` media query to ensure accessibility for all users.

## 2024-10-14 - Native Theming for Date/Time Pickers and Scrollbars
**Learning:** Adding `color-scheme: dark;` to the `:root` element instructs the browser to use a dark theme for native form controls (like `<input type="date">` and `<input type="time">`) and default scrollbars. This is a one-line CSS fix that dramatically improves the UX for users on dark-themed sites who otherwise see glaring white calendar popups or scrollbars. Additionally, setting `<meta name="theme-color" content="#18181b" />` ensures mobile browsers style their address bars to match the background, completing the immersive dark mode experience.
**Action:** When building dark mode apps, always include `color-scheme: dark;` globally to correctly style native UI components without complex custom CSS.

## 2024-10-15 - Inline Form Validation Error Accessibility
**Learning:** Using only text color (e.g., `.text-danger`) to indicate validation errors is insufficient for accessibility, as color-blind users or those with reduced contrast might miss it. Additionally, if the error container lacks `role="alert"` and `aria-live="assertive"`, screen readers won't announce the error immediately when it appears dynamically.
**Action:** When adding inline validation errors, always combine `role="alert"`, `aria-live="assertive"`, and an accessible icon (e.g., `<span aria-hidden="true">⚠️</span>`) paired with flex alignment to provide immediate, multi-sensory feedback for invalid form states.
