## 2025-02-06 - Add aria-keyshortcuts for Esc actions
**Learning:** Adding `aria-keyshortcuts="Escape"` to buttons that can be triggered by the keyboard Esc key (like modals or sidebars) provides explicit context for screen reader users, improving accessibility beyond just visual hints or tooltips.
**Action:** Next time when adding keyboard event listeners for UI actions (e.g., `handleKeydown` for `Escape`), check if the corresponding trigger buttons explicitly bind the `aria-keyshortcuts` attribute.
