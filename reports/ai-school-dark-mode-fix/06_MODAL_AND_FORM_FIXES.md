# 06 — MODAL AND FORM CONTROLS DARK MODE FIXES

**Project:** AI School Management System  
**Component:** Modal Dialogs, Input Controls & Action Windows  

---

## 1. Form Inputs & Field Wrappers Theming

Prior to this fix, form fields inside certain custom pages and Blade modals retained white backgrounds with gray text, resulting in low contrast and white input boxes inside dark pages.

### Unified Input Specifications for Dark Mode:
- **Input Background:** `#172235` (`--ai-dark-input`).
- **Border:** `#334155` (`--ai-dark-border`).
- **Text Color:** `#F8FAFC` (`--ai-dark-text-primary`).
- **Placeholder Text:** `#94A3B8` (`--ai-dark-text-muted`).
- **Focus Ring:** `0 0 0 3px rgba(37, 99, 235, 0.25)` with border `#2563EB`.
- **Field Labels:** `#CBD5E1` (`--ai-dark-text-secondary`).
- **Validation Errors:** High-contrast Rose Red `#FB7185` (`--ai-dark-danger`).

### Scoped CSS Enforced in `custom-theme.css`:
```css
.dark .fi-input,
.dark .fi-select-input,
.dark input[type="text"],
.dark input[type="number"],
.dark input[type="email"],
.dark input[type="password"],
.dark input[type="date"],
.dark input[type="search"],
.dark select,
.dark textarea,
.dark .ai-tenant-select,
.dark .bootstrap-field input,
.dark .bootstrap-field select {
    background-color: var(--ai-dark-input) !important;
    border-color: var(--ai-dark-border) !important;
    color: var(--ai-dark-text-primary) !important;
}

.dark input::placeholder,
.dark textarea::placeholder {
    color: var(--ai-dark-text-muted) !important;
}

.dark .fi-input:focus,
.dark input:focus,
.dark select:focus,
.dark textarea:focus {
    border-color: var(--ai-dark-btn-primary) !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25) !important;
}

.dark .fi-fo-field-wrp-label,
.dark label {
    color: var(--ai-dark-text-secondary) !important;
}
```

---

## 2. Modal Dialogs Architecture

Modals across the system (e.g., student registration, enrollment, payment collection, case reassignment, attendance editing) now follow a strict dark elevation pattern:

| Modal Component | Dark Mode Style | Color Token | Visual & Functional Behavior |
|:---|:---|:---:|:---|
| **Backdrop Overlay** | `rgba(0, 0, 0, 0.6)` with backdrop blur | — | Focuses user attention on active modal window |
| **Modal Window** | Background `#1E293B`, Border `#334155` | `--ai-dark-card` | Fits viewport with `max-height: 90vh` |
| **Modal Header** | Background `#1E293B`, Border `#334155` | `--ai-dark-card` | Clear title `#F8FAFC` and close button |
| **Modal Content** | Background `#1E293B`, text `#F8FAFC` | `--ai-dark-card` | Inner scrolling container preventing window overflow |
| **Info / Summary Box**| Background `#172235`, Border `#334155` | `--ai-dark-input` | Clean student summary and amounts display |
| **Modal Footer** | Background `#172235`, Border `#334155` | `--ai-dark-modal-footer`| Sticky footer ensuring buttons are always reachable |
| **Primary Action Button**| Background `#2563EB`, text `#FFFFFF` | `--ai-dark-btn-primary`| Prominent confirm/save button |
| **Cancel Button** | Background `#334155`, text `#CBD5E1` | `--ai-dark-border` | Clear dismissive action without distraction |

---

## 3. Payment Collection Modal Remediation
- **Affected Files:**
  - `installments-due-today.blade.php`
  - `installments-due-week.blade.php`
  - `installments-due-month.blade.php`
- **Before Fix:** The modal rendered as a stark white card (`bg-white`) with dark text `#1e293b`, gray input borders, and light-gray info box.
- **After Fix:**
  - Window container upgraded to `bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700`.
  - Student summary box upgraded to `bg-slate-50 dark:bg-slate-900/70 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200`.
  - Form fields use `bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-600 text-slate-900 dark:text-slate-100`.
  - Cancel button uses `bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200`.
  - Confirm payment button uses emerald green `bg-emerald-600 hover:bg-emerald-700 text-white`.
- **Validation:** Livewire wire:model bindings (`paymentAmount`, `vaultId`, `paymentNotes`) and `savePayment` method remain 100% untouched and functional.
