# 03 — SHARED THEME CHANGES & CSS TOKEN ARCHITECTURE

**Project:** AI School Management System  
**Scope:** Master Stylesheets & Shared Presentation Variables  

---

## 1. Centralized CSS Custom Properties Architecture

To prevent fragmented styling and ad-hoc color overrides across pages, all dark mode values were centralized as CSS custom properties on `:root` inside [`resources/css/custom-theme.css`](file:///d:/ai_school_new-main%20%281%29/ai_school_new-main/resources/css/custom-theme.css) and compiled to [`public/css/custom-theme.css`](file:///d:/ai_school_new-main%20%281%29/ai_school_new-main/public/css/custom-theme.css):

```css
:root {
    /* Centralized Approved Dark Mode Palette Tokens */
    --ai-dark-bg: #0F172A;              /* Main page background */
    --ai-dark-sidebar: #111C32;         /* Sidebar background */
    --ai-dark-sidebar-header: #0B1322;  /* Sidebar header background */
    --ai-dark-topbar: #162746;          /* Topbar background */
    --ai-dark-card: #1E293B;            /* Card background */
    --ai-dark-table-bg: #172235;        /* Table background */
    --ai-dark-table-header: #25344A;    /* Table header background */
    --ai-dark-table-row-hover: #1E2D44; /* Table row hover */
    --ai-dark-modal: #1E293B;           /* Modal background */
    --ai-dark-modal-footer: #172235;    /* Modal footer */
    --ai-dark-input: #172235;           /* Input background */
    --ai-dark-border: #334155;          /* Border color */
    --ai-dark-text-primary: #F8FAFC;    /* Primary text */
    --ai-dark-text-secondary: #CBD5E1;  /* Secondary text */
    --ai-dark-text-muted: #94A3B8;      /* Muted text */
    --ai-dark-link: #38BDF8;            /* Link/accent */
    --ai-dark-btn-primary: #2563EB;     /* Primary button */
    --ai-dark-success: #34D399;         /* Success */
    --ai-dark-warning: #FBBF24;         /* Warning */
    --ai-dark-danger: #FB7185;          /* Error */
}
```

---

## 2. Global Scoped `.dark` Selectors

All dark mode rules are strictly prefixed with `.dark` (which is toggled on `document.documentElement` via the Sun/Moon switch and persisted in `localStorage`). This design ensures:
1. **Zero leakage into Light Mode:** In Light Mode, `.dark` is absent, and the application renders standard light-mode CSS rules unmodified.
2. **Zero interference with Student/Parent Mobile:** The approved mobile app design for students remains 100% preserved, and the parent mobile bottom navigation operates independently.

### Key Rules Implemented:
- **Canvas & Layout:**
  ```css
  .dark body, .dark .fi-body, .dark .fi-page {
      background-color: var(--ai-dark-bg) !important;
      color: var(--ai-dark-text-primary) !important;
  }
  ```
- **Sidebar & Topbar:**
  ```css
  .dark aside.fi-sidebar, .dark .fi-sidebar {
      background: var(--ai-dark-sidebar) !important;
      border-inline-end-color: var(--ai-dark-border) !important;
  }
  .dark .fi-sidebar-header {
      background: var(--ai-dark-sidebar-header) !important;
      border-bottom-color: var(--ai-dark-border) !important;
  }
  .dark .fi-topbar {
      background: var(--ai-dark-topbar) !important;
      border-bottom-color: var(--ai-dark-border) !important;
  }
  ```
- **Containers, Cards & Sections:**
  ```css
  .dark .fi-section,
  .dark .fi-ta-ctn,
  .dark .fi-modal-window,
  .dark .fi-wi-stats-overview-stat,
  .dark .fi-card,
  .dark .installment-card,
  .dark .bootstrap-panel,
  .dark .bootstrap-card,
  .dark .ai-quick-control-widget,
  .dark .bg-white {
      background-color: var(--ai-dark-card) !important;
      border-color: var(--ai-dark-border) !important;
      color: var(--ai-dark-text-primary) !important;
  }
  ```

---

## 3. Administrative Theme Alignment

In [`resources/css/filament-admin-theme.css`](file:///d:/ai_school_new-main%20%281%29/ai_school_new-main/resources/css/filament-admin-theme.css) and [`public/css/filament-admin-theme.css`](file:///d:/ai_school_new-main%20%281%29/ai_school_new-main/public/css/filament-admin-theme.css):
- Updated `.dark aside.fi-sidebar` to `#111C32` with border `#334155`.
- Updated `.dark .fi-sidebar-header` to `#0B1322` with border `#334155`.
- Preserved existing hover effects (`#1E355F`), brand green active indicators (`#58BA47`), and compact 62px header height.
