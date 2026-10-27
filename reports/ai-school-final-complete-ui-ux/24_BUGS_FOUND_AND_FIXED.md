# Report 24: UI/UX Bugs Found and Repaired

## 1. Catalog of Repaired Defects
1. **Missing Global Z-Index on Flatpickr Calendars:**
   - *Defect:* Date picker popups opened with default z-index, causing them to be clipped or hidden when used inside Filament modal windows.
   - *Fix:* Added `.flatpickr-calendar { z-index: 999999 !important; }` in `public/css/custom-theme.css`.
2. **Mobile Modal Overflow on Small Displays (320px - 430px):**
   - *Defect:* Large modal classes caused horizontal window clipping on smartphones.
   - *Fix:* Added responsive media query forcing `width: calc(100vw - 1rem) !important;` on screens <= 640px.
3. **Student/Parent Bottom Navigation Content Overlap:**
   - *Defect:* Fixed mobile bottom navigation bar overlapped content at the very bottom of the page.
   - *Fix:* Added `padding-bottom: 5.5rem !important;` to `.fi-panel-student` and `.fi-panel-parent`.
4. **Phone Numbers & Numeric Bidi Alignment:**
   - *Defect:* Phone numbers with leading zeros were rendered with inverted punctuation in RTL Arabic context.
   - *Fix:* Added `.phone-number, input[type="tel"] { direction: ltr !important; text-align: right !important; unicode-bidi: embed !important; }`.

---

## 2. Verification Status
- **Test ID:** UI-BUG-01
- **Result:** **PASS (All 4 defects repaired and regression tested)**