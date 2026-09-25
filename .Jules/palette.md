## 2023-11-20 - Adding Icons for Links Without Modifying Global Styles
**Learning:** Adding subtle emoji prefixes (`📞`) and title tooltips (`title="Нажмите, чтобы позвонить"`) with inline layout styles (`display: inline-flex; align-items: center; gap: 0.25rem;`) is an effective way to improve the clickability and affordance of generic links without relying on external icon libraries or adding custom classes to global stylesheets. Hiding emojis from screen readers (`aria-hidden="true"`) ensures accessibility tools read out the custom `aria-label` correctly.
**Action:** Use this pattern to quickly boost clarity on actionable text when you are constrained from adding global utility classes or SVG components.

## 2024-03-24 - Vue SPA Route Transitions and Reduced Motion
**Learning:** Adding a subtle fade transition to `<router-view>` via `<transition name="page-fade" mode="out-in">` drastically improves the "feel" of a Vue SPA with minimal code. However, any animated transition that applies to the entire page content must include `@media (prefers-reduced-motion: reduce)` to disable it, otherwise it can cause accessibility and usability issues for users sensitive to motion.
**Action:** Use this pattern to add polish to SPAs, always ensuring `prefers-reduced-motion` is paired with page-level transitions.
## 2026-09-24 - Data Table Keyboard Accessibility & Scanability
**Learning:** Discovered that raw HTML tables lack descriptive names for assistive technologies, and row-level hover states were missing, making horizontal scanning difficult for sighted users.
**Action:** Always associate data tables with their headings using `aria-labelledby` and add `:hover` states to table rows (respecting `prefers-reduced-motion`) to improve visual scanability.

## $(date +%Y-%m-%d) - Adding explicit form labeling
**Learning:** Adding explicit `aria-labelledby` linking forms to their respective headings (like "Вход в панель" or "Создать новый слот") provides crucial screen reader context, making it easier for users using assistive technologies to understand the form's purpose immediately.
**Action:** Always verify if forms have an implicit or explicit label. If missing, link them to the nearest descriptive heading using `id` and `aria-labelledby`.
