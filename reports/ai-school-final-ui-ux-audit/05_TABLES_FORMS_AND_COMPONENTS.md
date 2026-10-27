# 05 — TABLES, FORMS AND REUSABLE COMPONENTS AUDIT

**Audit Dimension:** Medium-Density Data Tables, Form Inputs, Buttons, Badges, and KPI Cards  

---

## 1. Medium-Density Data Tables

All data tables across administrative, academic, and financial modules were audited against the medium-density standard:
- **Row Height:** Verified between **48–52px** (via standard `padding: 0.82rem 1.15rem`).
- **Header Appearance:** Background `#F8FAFC`, uppercase bold slate `#475569`, 13px, border-bottom `1px solid #E2E8F0`.
- **Row Hover:** Smooth transition to `#F8FAFC` (Light) or `#25334D` (Dark).
- **Financial Table Containment:**
  - Multi-column tables (e.g., installments due, payments, ledger statements) are encapsulated in `.fi-ta-content` with horizontal scrolling.
  - No essential columns, currencies, or student names are truncated or hidden.
- **Status Badges:** Standardized rounded pills (`border-radius: 9999px`) with semantic colors (Paid/Completed: Green, Pending: Amber, Unpaid/Cancelled: Red).

---

## 2. Forms & Inputs

- **Control Heights:** 42px standard across text inputs, selects, date pickers, and number fields.
- **Focus Rings:** `0 0 0 3px rgba(54, 79, 172, 0.2)` with brand blue border `#364FAC`.
- **Validation Messages:** Red `#EF4444`, 13px, positioned immediately below invalid inputs.
- **Field Layouts:** Multi-column grids on desktop collapse into clean single-column layouts on mobile viewports.

---

## 3. KPI Stat Cards & Buttons

- **KPI Cards:** Standard 14px radius, 18px padding, bold 26px numerical figures, subtle hover elevation.
- **Buttons:** Min-height 42px, radius 8px, clear contrast across Primary (Blue), Secondary (White/Border), Danger (Red), and Success (Green).
