# 06 — RTL SELECT RENDERING & ARABIC TYPOGRAPHY TESTS

**Project:** AI School — Arabic RTL Interface  
**Primary Font:** Tajawal (`Tajawal:wght@300;400;500;700;800;900`)  
**Secondary / Numerical Font:** Inter (`Inter:wght@400;500;600;700`)

---

## 1. RTL Layout & Alignment Audit

AI School operates predominantly in Arabic with `dir="rtl"` applied at the document root level (`<html dir="rtl" lang="ar">`).

The following criteria have been verified across all Select components:

### A. Text Alignment & Unicode Bidi
- **Selected Name Display (`.fi-select-input-value-label`):**
  - Styled with `text-align: start !important;` and `unicode-bidi: plaintext !important;`.
  - In RTL mode, text starts at the right margin.
  - Mixed Arabic-English names (e.g. `[E2E-LIVE-b73da] الطالب كمال الدين مصطفى`) render in the correct visual order without reverse-character scrambling.

- **Search Input Field (`.fi-select-input-search-ctn input`):**
  - Styled with `text-align: start !important;`.
  - Cursor aligns to the right side of the search input.
  - Placeholder (`Search...` / `ابحث...`) renders properly at the right margin.

### B. Icons & Chevron Arrow Positioning
- **Dropdown Chevron Arrow:**
  ```css
  [dir="rtl"] .fi-select-input .fi-select-input-btn,
  :dir(rtl) .fi-select-input .fi-select-input-btn {
      background-position: left 0.65rem center !important;
  }
  ```
  - In RTL, the chevron arrow sits on the **far left** of the button, preserving standard Arabic UI conventions.
  - The right padding (`padding-inline-start: 0.75rem`, `padding-inline-end: 2.25rem`) prevents long Arabic names from colliding with the arrow.

- **Clear Icon (`X` button) Alignment:**
  ```css
  [dir="rtl"] .fi-select-input .fi-select-input-value-remove-btn,
  :dir(rtl) .fi-select-input .fi-select-input-value-remove-btn {
      inset-inline-end: 2rem !important;
      left: 2rem !important;
      right: auto !important;
  }
  ```
  - The clear button sits at `left: 2rem`, directly adjacent to the arrow on the left, leaving the entire right and center area unhindered for the Arabic student label.
  - When clearable is enabled, `.fi-select-input-ctn-clearable` reserves `padding-inline-end: 3.75rem !important;`. Long names truncate cleanly with ellipsis before touching the clear button.

### C. Options List Text & Wrapping
- **Option Items (`.fi-select-input-option`):**
  - `padding: 0.55rem 0.85rem !important;`
  - `text-align: start !important;`
  - `word-break: break-word !important;`
  - Long 4-part and 5-part Arabic family names (e.g., `محمد سمير إبراهيم عبد الرحمن القواسمي`) wrap onto multiple lines cleanly without overlapping adjacent items.

---

## 2. Test Verification Matrix (RTL)

| Scenario / Check | Expected Behavior | Actual Behavior | Result |
| :--- | :--- | :--- | :---: |
| Arabic student name alignment | Right-aligned, no clipping | Cleanly starts right, truncates with ellipsis if container narrows | **PASS** |
| Mixed Arabic-Latin label | Left-to-right code followed by Arabic name | Bidi plaintext preserves Latin token and Arabic phrase | **PASS** |
| Clear icon & chevron spacing | No overlap between 'X' and arrow | 2rem spacing preserved, left aligned | **PASS** |
| Search bar cursor in RTL | Cursor starts rightward | Starts at right, typing flows right-to-left | **PASS** |
| Long options inside dropdown | Wraps without vertical collision | Line-height 1.4, clean item separation | **PASS** |
