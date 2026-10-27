# 04 — EMPLOYEE SIDEBAR AND HEADER SPECIFICATION

**Project:** AI School Management System  
**Component Design:** Master Employee Sidebar & Compact Header  

---

## 1. Dark Navy Sidebar Design

The employee sidebar follows the approved enterprise specification:
- **Position:** Right-hand side (RTL layout) across all employee panels (`/admin`, `/instructor`, `/supervisor`).
- **Background Color:** `#162746` (Approved Dark Navy).
- **Brand Header Section:** Deepest Navy `#0F1D36` with crisp white logo typography and subtle 1px border `rgba(255, 255, 255, 0.08)`.
- **Navigation Groups:**
  - Group headings styled in `#8FA3C7`, uppercase, bold, letter-spacing `0.05em`.
  - Independent expand/collapse preservation without visual clutter.
- **Navigation Links:**
  - Standard text color: `#E2E8F0` (clean off-white).
  - Hover state: Background `#1E355F`, text `#FFFFFF`, smooth 2px translate.
  - Active state: Background `#254580`, text `#FFFFFF`, bold, with a vibrant `#58BA47` (AI School Green) indicator bar pinned to the inner edge.
- **Icons:** Standardized 18–20px aligned with high visual consistency.
- **Collapsible Behavior:** Supports standard desktop collapse with preserved tooltips and active indicator states.

---

## 2. Compact Professional Header (Topbar)

- **Dimensions:** Fixed height of **62px** (within the 60–64px standard).
- **Surface:**
  - Light Mode: `rgba(255, 255, 255, 0.96)` with backdrop blur and border-bottom `1px solid #E2E8F0`.
  - Dark Mode: `#162746` with border-bottom `rgba(255, 255, 255, 0.08)`.
- **Integrated Controls:**
  1. **Breadcrumbs / Section Context:** High contrast, legible typography.
  2. **Sidebar Toggle Button:** Accessible collapse trigger.
  3. **Theme Switcher Button:** One-click Light/Dark toggle with Sun/Moon dynamic SVG icons.
  4. **Notifications:** Real-time database notification bell.
  5. **User Profile & Avatar:** Elevated user badge with direct profile access.
- **Safety:** Sticky positioning does not obstruct table headers, modals, or dropdown menus (`z-index: 40`).
