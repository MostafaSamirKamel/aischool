# 04 — TABLE AND TEXT CONTRAST FIXES

**Project:** AI School Management System  
**Component:** Data Tables, Multi-Column Financial Reports & Typography Contrast  

---

## 1. Table Theming Architecture

Prior to this fix, custom report tables and standard Filament tables in Dark Mode suffered from conflicting background inheritances. Some tables retained light gray or white backgrounds, causing light-colored text to wash out, while others had dark-gray text on dark backgrounds.

The unified table theming architecture enforces the approved palette:

| Table Component | Dark Mode Style | Color Token | Visual Purpose |
|:---|:---|:---:|:---|
| **Table Container** | Background `#1E293B`, Border `#334155` | `--ai-dark-card` | Seamless integration within page canvas |
| **Header Row (`<th>`)** | Background `#25344A`, Border `#334155` | `--ai-dark-table-header` | High-contrast uppercase header band |
| **Header Typography** | Color `#CBD5E1`, Weight `700` | `--ai-dark-text-secondary`| High clarity without glare (8.0:1 contrast) |
| **Data Rows (`<td>`)** | Background `#172235`, Border `#334155` | `--ai-dark-table-bg` | Distinct data surface preventing light bleed |
| **Primary Data Text** | Color `#F8FAFC`, Weight `600` | `--ai-dark-text-primary` | Maximum readability for student names & amounts (14.8:1) |
| **Secondary Data Text**| Color `#CBD5E1` | `--ai-dark-text-secondary`| Clear dates, phone numbers, codes |
| **Row Hover State** | Background `#1E2D44` | `--ai-dark-table-row-hover`| Distinct visual row tracking on cursor hover |
| **Pagination Bar** | Background `#1E293B`, Border `#334155` | `--ai-dark-card` | Clean footer navigation |

---

## 2. Specific Table Implementations Fixed

### A. Installment Report Tables
- **Screens:**
  - [`/admin/installments-due-today`](file:///d:/ai_school_new-main%20%281%29/ai_school_new-main/resources/views/filament/admin/pages/installments-due-today.blade.php)
  - [`/admin/installments-due-week`](file:///d:/ai_school_new-main%20%281%29/ai_school_new-main/resources/views/filament/admin/pages/installments-due-week.blade.php)
  - [`/admin/installments-due-month`](file:///d:/ai_school_new-main%20%281%29/ai_school_new-main/resources/views/filament/admin/pages/installments-due-month.blade.php)
- **Fix Applied:**
  Replaced hardcoded `.installment-table th { background: linear-gradient(...); }` and `.installment-table td { border-bottom: 1px solid #f0f0f0; }` with dark mode rules:
  ```css
  .dark .installment-table th {
      background: #25344A !important;
      color: #CBD5E1 !important;
      border-bottom: 1px solid #334155 !important;
  }
  .dark .installment-table td {
      background: #172235 !important;
      color: #F8FAFC !important;
      border-bottom: 1px solid #334155 !important;
  }
  .dark .installment-table tbody tr:hover td {
      background: #1E2D44 !important;
  }
  ```
- **Result:** Student names render in `#F8FAFC`, phone numbers in `#CBD5E1`, total amounts in `#38BDF8`, paid amounts in `#34D399`, and remaining amounts in `#FBBF24`.

### B. Vault & Cash Flow Movement Tables
- **Screens:**
  - [`/admin/vault-daily-movement`](file:///d:/ai_school_new-main%20%281%29/ai_school_new-main/resources/views/filament/admin/widgets/vault-daily-movement.blade.php)
  - [`/admin/vault-report`](file:///d:/ai_school_new-main%20%281%29/ai_school_new-main/resources/views/filament/admin/pages/vault-report.blade.php)
- **Fix Applied:**
  Mapped `.dark .bootstrap-table th` to `#25344A` and `.dark .bootstrap-table td` to `#172235` with subtle borders `#334155`. Cash badges, wallet badges, and bank badges received semi-transparent dark backgrounds with bright accent text.

### C. Standard Filament Resource Tables
- **Screens:** Students, Groups, Enrollments, Payments, Invoices, Instructors, Salaries.
- **Fix Applied:**
  Enforced `.dark .fi-ta-table th` (`#25344A`), `.dark .fi-ta-row td` (`#172235`), and `.dark .fi-ta-row:hover td` (`#1E2D44`) in `custom-theme.css`. Horizontal scrolling (`.fi-ta-content`) remains intact, ensuring multi-column financial tables never truncate amounts or dates.

---

## 3. High-Contrast Typography Hierarchy

All text elements are now strictly bound to the dark mode hierarchy:
1. **Primary Text (`#F8FAFC`):** H1-H6 headings, student names, KPI values, monetary numbers.
2. **Secondary Text (`#CBD5E1`):** Table column headers, field labels, breadcrumbs, dates.
3. **Muted Text (`#94A3B8`):** Subtitles, hint text, phone numbers, empty-state descriptions.

No component displays dark-gray text on dark backgrounds or white text on white backgrounds.
