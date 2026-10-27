# 02 — VISUAL INCONSISTENCY AUDIT

**Project:** AI School Management System  
**Audit Stage:** Pre-Unification Visual Discrepancy Analysis  

---

## 1. Discovered Visual Inconsistencies Prior to Unification

| Area / Component | Previous State (Inconsistent) | Target Approved Standard | Severity |
|:---|:---|:---|:---:|
| **Employee Sidebar Background** | White background (`#FFFFFF`) with light border in `filament-admin-theme.css` and `custom-theme.css` | **Dark Navy Sidebar (`#162746`)** with `#0F1D36` brand header | High |
| **Sidebar Text & Icons** | Dark gray text (`#18243D`) with weak contrast on hover | Crisp off-white (`#E2E8F0`) with white hover and green accent active bar | High |
| **Typography** | Varied between `Cairo` and `Alexandria` without standardized font hierarchy | **`Tajawal`** as primary Arabic font with `Inter` for English/numbers | Medium |
| **Dark Mode Implementation** | Disabled (`->darkMode(false)`) in `AdminPanelProvider`, partial `.dark` CSS rules without header toggle | Persistent, optional Dark Mode with instant topbar toggle and `localStorage` persistence | High |
| **Default Theme** | Some browsers defaulted to system theme which could be dark | **Light Mode** strictly enforced as the default experience | Medium |
| **Header (Topbar)** | Inconsistent heights across panels (ranging from 56px to 74px) | Standardized compact 62px header across all employee panels | Medium |
| **Table Density** | Inconsistent row heights (some too dense at 40px, others too sparse at 68px) | Standardized **Medium Density (48–52px)** with clean padding | Medium |
| **Parent Mobile Navigation** | Missing dedicated mobile bottom navigation bar; desktop-style sidebar on mobile | Standardized **App-Like Bottom Navigation Bar** matching student mobile app | High |
| **Modals & Drawers** | Modals lacking standardized max-height rules, risking off-screen save buttons | Clean `max-height: 90vh` with scrollable body and sticky footer | Medium |

---

## 2. Root Cause Analysis
The inconsistencies stemmed from separate stylesheet overrides (`custom-theme.css`, `filament-admin-theme.css`, `student-theme-scoped.css`) where white sidebar rules conflicted with dark navy specifications, and dark mode was disabled in panel providers rather than gracefully enabled with persistent browser storage.

---

## 3. Resolution Plan
Consolidate all design tokens into `resources/css/custom-theme.css` and `resources/css/filament-admin-theme.css`, enable `Tajawal` font and `darkMode(true)` in all employee panels, and deploy dedicated parent mobile navigation.
