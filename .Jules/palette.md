## 2023-11-20 - Adding Icons for Links Without Modifying Global Styles
**Learning:** Adding subtle emoji prefixes (`📞`) and title tooltips (`title="Нажмите, чтобы позвонить"`) with inline layout styles (`display: inline-flex; align-items: center; gap: 0.25rem;`) is an effective way to improve the clickability and affordance of generic links without relying on external icon libraries or adding custom classes to global stylesheets. Hiding emojis from screen readers (`aria-hidden="true"`) ensures accessibility tools read out the custom `aria-label` correctly.
**Action:** Use this pattern to quickly boost clarity on actionable text when you are constrained from adding global utility classes or SVG components.

## 2024-03-24 - Vue SPA Route Transitions and Reduced Motion
**Learning:** Adding a subtle fade transition to `<router-view>` via `<transition name="page-fade" mode="out-in">` drastically improves the "feel" of a Vue SPA with minimal code. However, any animated transition that applies to the entire page content must include `@media (prefers-reduced-motion: reduce)` to disable it, otherwise it can cause accessibility and usability issues for users sensitive to motion.
**Action:** Use this pattern to add polish to SPAs, always ensuring `prefers-reduced-motion` is paired with page-level transitions.
