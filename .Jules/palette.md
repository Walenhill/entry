## 2024-05-16 - [Form Input Maxlength Validation]
**Learning:** Adding maxlength constraints and explicit visual character counters mapped via `aria-describedby` prevents backend `mb_strlen` or strict-mode string truncation exceptions while simultaneously improving the experience for screen reader users by providing clear, contextually linked boundaries.
**Action:** When adding user text inputs, always implement explicit `maxlength` attributes corresponding to database limits, and include a visual counter tied to `aria-describedby` for accessible limit awareness.

## 2025-05-18 - [Button Async State Feedback]
**Learning:** Adding a small, animated spinner inside async submission buttons provides critical, immediate visual feedback that the application is processing the request, as relying purely on the `disabled` state and changing the text (e.g., "Сохранение...") is often not prominent enough to assure users that progress is occurring, preventing multiple click attempts and frustration.
**Action:** Always include a visual loading indicator (like `.spinner-small`) paired with an `aria-hidden="true"` attribute alongside the text change inside primary action buttons during asynchronous tasks.

## 2025-05-20 - [Password Visibility Toggle]
**Learning:** Adding a password visibility toggle button significantly reduces user frustration, especially on mobile devices where typing errors are common. A simple toggle allows users to verify their password before submitting, preventing repeated login failures. When implementing, it's critical to ensure the toggle button is accessible by updating the `aria-label` and `title` dynamically (e.g., "Скрыть пароль" vs "Показать пароль") to inform screen reader users of the current state and action.
**Action:** When adding password inputs, always include a visibility toggle feature with appropriate `aria-label` updates to ensure accessibility and better user experience.

## 2024-05-23 - [Button Async State Feedback - Refined]
**Learning:** Adding a small, animated spinner inside async submission buttons provides critical, immediate visual feedback that the application is processing the request, as relying purely on the `disabled` state and changing the text (e.g., "Отмена...") is often not prominent enough to assure users that progress is occurring, preventing multiple click attempts and frustration.
**Action:** Always include a visual loading indicator (like `.spinner-small`) paired with an `aria-hidden="true"` attribute alongside the text change inside primary action buttons during asynchronous tasks.

## 2025-05-24 - [Modal Asynchronous State Locking]
**Learning:** During asynchronous form submissions within modals, it is critical to disable not only the primary submit button, but also all secondary exit actions (such as the "Cancel" button, the top "×" close button, the Escape key listener, and the background overlay click handler). Failing to do so allows users to accidentally close the modal while a backend request is still in-flight, which can lead to missing validation feedback, orphaned requests, or confusing state desynchronization in the UI.
**Action:** Always bind the `isSubmitting` (or equivalent) loading state to the `disabled` attribute of all buttons within the modal, and explicitly check `!isSubmitting` in the Escape keydown and overlay click handlers to ensure the modal remains firmly locked until the asynchronous operation completes or errors out.
## 2024-05-26 - [Document Language and Metadata Accessibility]
**Learning:** Changing the scaffolded `lang="en"` attribute to accurately reflect the application localization (e.g., `lang="ru"`) is critical. If left as "en", screen readers will mispronounce Russian text using English phonetic rules, making it completely incomprehensible for visually impaired users. Furthermore, default generic scaffold titles (like "Vite + Vue") must be replaced, and basic SEO meta descriptions should be added to immediately provide context for assistive technologies upon page load.
**Action:** When working on localized applications built with tools like Vite, always verify that the root `index.html` has the correct `lang` attribute, a descriptive `<title>`, and proper document metadata before launch.

## 2025-05-27 - [Clickable Phone Numbers]
**Learning:** Displaying phone numbers merely as text requires users to manually copy and paste them to make a call, which is a major point of friction, especially on mobile devices. By formatting them as clickable `tel:` links, we bridge the gap between the application and the system's telephony features, creating a seamless and immediate action for the user.
**Action:** When displaying client phone numbers in the UI (e.g., in admin lists, tables, or slot cards), always wrap them in an `<a href="tel:...">` tag to enable one-click calling functionality.

## 2024-05-18 - [Empty States & Link Interactions in Stats]
**Learning:** Found that the default empty state for the top clients table was just a text string, which looked unpolished and wasn't screen-reader optimal. Also, dynamically rendered phone links (`<a href="tel:...">`) were missing the standard `.phone-link` class, meaning they lacked visual feedback (hover styles/colors) despite the CSS existing in the component block.
**Action:** Always verify that tables and lists have structured empty states (icon + heading + message) and ensure all functional links (like `tel:` or `mailto:`) explicitly include utility classes for interaction feedback.

## 2024-05-28 - [Disabled Button Tooltips & Action Context]
**Learning:** Native disabled buttons (like `<button disabled>`) do not consistently trigger mouse hover events in all browsers, which means applying a `title` attribute directly to the `<button>` will often prevent the tooltip from appearing just when the user needs it most (to understand *why* the action is disabled). Furthermore, simply disabling an icon-only button without updating its `aria-label` leaves screen-reader users without context regarding the asynchronous blockage.
**Action:** Always wrap dynamically disabled buttons in a `<span>` to hold the `title` attribute (e.g. `style="display: inline-flex;"` to preserve layout) and append the blocked state to the button's `aria-label` (e.g., `aria-label="[Action] - Действие недоступно во время загрузки"`) to ensure both sighted and assistive technology users receive the loading context.
