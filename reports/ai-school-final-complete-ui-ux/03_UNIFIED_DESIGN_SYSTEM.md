# Report 03: Unified Design System and Token Architecture

## 1. Design Tokens Specification
The unified design system is declared centrally in `public/css/custom-theme.css`:

```css
:root {
    /* Primary Brand Identity */
    --ai-navy: #0F172A;
    --ai-primary: #2563EB;
    --ai-primary-hover: #1D4ED8;
    --ai-primary-secondary: #3B82F6;
    --ai-primary-weak: #EFF6FF;
    --ai-primary-ring: rgba(37, 99, 235, 0.2);

    /* Functional Accents */
    --ai-success: #16A34A;
    --ai-warning: #D97706;
    --ai-danger: #DC2626;
    --ai-info: #0284C7;

    /* Light Mode Surfaces */
    --ai-bg: #F8FAFC;
    --ai-card: #FFFFFF;
    --ai-text: #0F172A;
    --ai-text-secondary: #64748B;
    --ai-line: #E2E8F0;

    /* Dark Mode Surfaces */
    --ai-dark-bg: #0B1220;
    --ai-dark-card: #172033;
    --ai-dark-elevated: #1E293B;
    --ai-dark-text-primary: #F1F5F9;
    --ai-dark-text-secondary: #CBD5E1;
    --ai-dark-border: #334155;

    /* Typography */
    --font-family-tajawal: 'Tajawal', 'Inter', system-ui, sans-serif;
}
```

---

## 2. Typography Rules
- **Arabic Language:** Styled exclusively in `Tajawal` font (weights 400, 500, 700, 800, 900).
- **LTR / Numbers / Code:** Directional containment (`direction: ltr; text-align: right; unicode-bidi: embed;`) for phone numbers, dates, currency amounts, and programming code snippets.
- **RTL Baseline:** All panels declare `dir="rtl"` with proper margin/padding inline start/end mappings.

---

## 3. Verification Status
- **Test ID:** UI-DS-01
- **Result:** **PASS (Unified tokens enforced across all stylesheets)**