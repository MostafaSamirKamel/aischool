# 06 — LIGHT AND DARK MODE IMPLEMENTATION & PERSISTENCE

**Project:** AI School Management System  
**Theme Strategy:** Default Light Mode + Persistent Optional Dark Mode  

---

## 1. Core Operating Principles

1. **Light Mode as Strict Default:**
   - Every new employee session or fresh browser visit defaults unconditionally to **Light Mode**.
   - Enforced via an initialization script in `PanelsRenderHook::HEAD_START`:
     ```javascript
     if (localStorage.getItem("theme") === null) {
         localStorage.setItem("theme", "light");
     }
     if (localStorage.getItem("theme") === "dark") {
         document.documentElement.classList.add("dark");
     } else {
         document.documentElement.classList.remove("dark");
     }
     ```
2. **Optional & Toggleable:**
   - Employees can toggle Dark Mode at any time using the one-click Sun/Moon toggle in the header or the profile menu.
3. **Persistence per Employee Browser:**
   - Stored safely in `localStorage` under key `theme`.
   - Persists across page transitions, livewire updates, and browser restarts without requiring any database schema alterations.
4. **Strict Theme Isolation:**
   - Employee Dark Mode applies exclusively to employee panels (`/admin`, `/instructor`, `/supervisor`).
   - The Student and Parent mobile portals retain their dedicated, approved mobile-app styling without dark-mode style leakage.

---

## 2. Dark Mode Color Specifications

| Surface / Element | Light Mode Value | Dark Mode Value |
|:---|:---:|:---:|
| **Canvas Background** | `#F8FAFC` | `#0F172A` (Deep Slate) |
| **Card / Section Surface** | `#FFFFFF` | `#1E293B` (Slate Surface) |
| **Sidebar Background** | `#162746` (Dark Navy) | `#111F38` (Harmonious Deep Navy) |
| **Sidebar Header** | `#0F1D36` | `#0B162B` |
| **Header (Topbar)** | `#FFFFFF` | `#162746` |
| **Table Header** | `#F8FAFC` | `#162032` |
| **Table Row Surface** | `#FFFFFF` | `#1E293B` |
| **Table Row Hover** | `#F8FAFC` | `#25334D` |
| **Input Background** | `#FFFFFF` | `#162032` |
| **Input Border** | `#CBD5E1` | `#334155` |
| **Primary Text** | `#0F172A` | `#F8FAFC` |
| **Secondary Text** | `#64748B` | `#94A3B8` |

---

## 3. Verification
- Toggle clicked -> `.dark` class added to `<html>` -> all surfaces, tables, forms, modals smoothly adapt.
- Page reloaded -> Dark Mode remains active.
- Toggle clicked again -> Light Mode restored and saved.
