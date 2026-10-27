# 05 — SHARED COMPONENT STYLING

**Project:** AI School Management System  
**Design Scope:** Enterprise Components (Cards, Tables, Forms, Buttons, Modals, Badges)  

---

## 1. Dashboard KPI Metric Cards
- **Border Radius:** `14px` (`var(--ai-radius-md)`).
- **Internal Padding:** `1.15rem 1.35rem` (approx 18–22px).
- **Surface:** White `#FFFFFF` in Light Mode, Slate `#1E293B` in Dark Mode.
- **Value Typography:** `1.65rem` (26px), ExtraBold (800), high-contrast.
- **Label Typography:** `0.88rem` (14px), SemiBold (600), muted slate `#64748B`.
- **Hover Motion:** Subtle `-2px` translateY with elevated modern drop-shadow.

---

## 2. Medium-Density Data Tables
- **Standardized Row Height:** `48–52px` (achieved via `padding: 0.82rem 1.15rem`).
- **Header:** `#F8FAFC` background, `#475569` uppercase bold text, 13px, subtle bottom border.
- **Row Hover:** `#F8FAFC` transition in Light Mode, `#25334D` in Dark Mode.
- **Cell Content:** 14px Medium typography with clean tabular numeric alignment for currencies, dates, and student codes.
- **Horizontal Scroll Safety:** Scoped within `.fi-ta-content` with thin scrollbars, preventing global horizontal page blowout.

---

## 3. Forms & Input Fields
- **Standard Control Height:** `42px` across text inputs, select dropdowns, date pickers, and number fields.
- **Border Radius:** `8px` (`var(--ai-radius-sm)`).
- **Borders:** `1px solid #CBD5E1` (Light) / `1px solid #334155` (Dark).
- **Focus Ring:** `0 0 0 3px rgba(54, 79, 172, 0.2)` with brand blue border `#364FAC`.
- **Validation Messages:** `0.8rem` red `#EF4444` with clear icon association.

---

## 4. Buttons & Interactive Actions
- **Primary Buttons:** `#364FAC` (Hover: `#293C85`), white text, 42px min-height, radius 8px.
- **Secondary Buttons:** White background with `#CBD5E1` border and `#334155` text.
- **Destructive Actions:** Red `#EF4444` (Hover: `#DC2626`), white text.
- **Success / Collect Actions:** Green `#58BA47` (Hover: `#459637`), white text.
- **Row Action Icons:** 32x32px clean touch target with soft hover tint.

---

## 5. Modals & Dialog Windows
- **Border Radius:** `16px`.
- **Dimensions:** Responsive `max-width` tailored by modal type, with `max-height: 90vh`.
- **Header:** Padding 18px 22px, title 18px Bold.
- **Content:** Padding 22px, scrollable inner container.
- **Footer:** Padding 16px 22px, light gray background `#F8FAFC`, sticky buttons ensuring save action is always reachable on any device.
