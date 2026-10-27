# 04 — SIDEBAR AND HEADER REVIEW

**Audit Dimension:** Navigation Architecture, Topbar Density, Responsive Collapse, and Visual Hierarchy  

---

## 1. Dark Navy Sidebar Verification

- **Placement:** Positioned on the RIGHT for native Arabic RTL orientation across all employee panels (`/admin`, `/instructor`, `/supervisor`).
- **Color Execution:**
  - Main Sidebar Surface: `#162746` (Approved Dark Navy).
  - Header / Logo Section: `#0F1D36` (Deepest Navy for maximum contrast).
  - Navigation Text: `#E2E8F0` (Crisp Readable Off-White).
  - Navigation Icons: `#94A3B8` (Uniform 18–20px sizing).
  - Hover Effect: Background `#1E355F`, Text `#FFFFFF`, smooth translate.
  - Active Item: Background `#254580`, Text `#FFFFFF`, with pinned Green `#58BA47` indicator.
  - Group Headings: `#8FA3C7`, uppercase, bold, letter-spacing `0.05em`.
- **Collapsible Interaction:**
  - Supports desktop expand/collapse smoothly without breaking nested groups or permission-scoped items.
  - Long navigation menus scroll internally with thin customized scrollbars without pushing main page content.

---

## 2. Compact Header (Topbar) Verification

- **Height:** Standardized to **62px** across all employee panels.
- **Surface:**
  - Light Mode: `rgba(255, 255, 255, 0.96)` with backdrop blur and border-bottom `1px solid #E2E8F0`.
  - Dark Mode: `#162746` with border-bottom `rgba(255, 255, 255, 0.08)`.
- **Integrated Controls:**
  - Dynamic page title and breadcrumbs.
  - Sidebar collapse/expand toggle button.
  - Dedicated one-click Light/Dark mode switch with Sun/Moon dynamic icons.
  - Real-time notification badge.
  - Elevated user profile badge and avatar.
- **Safety:** Sticky positioning maintains `z-index: 40`, ensuring zero occlusion of table headers, filters, or modal overlays.
