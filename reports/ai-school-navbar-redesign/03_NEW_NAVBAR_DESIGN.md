# AI SCHOOL — NEW NAVBAR DESIGN

**Date:** October 26, 2026  
**Scope:** Design specification of the single-row Top Navbar and visual elements.

---

## 1. VISUAL DESIGN PHILOSOPHY
The new Employee Top Navbar is crafted following modern SaaS educational dashboard standards:
- **Single-Row Compact Header:** Fixed height of 64px preserving maximum vertical workspace for complex tables, cards, and forms.
- **Unified Color Tokens:** Utilizes Section 23 brand tokens (`#0F172A` Navy, `#2563EB` Primary Blue, `#F8FAFC` Light Background, `#172033` Dark Surface).
- **RTL-First Symmetry:** Aligned naturally for Arabic reading direction with Logo/Branch on the right and Utilities/User Profile on the left.
- **Active Glow Indicators:** Subtle colored accent bar (`.ai-nav-glow`) at the bottom of active tabs.

---

## 2. DESKTOP SINGLE-ROW STRUCTURE (RTL)

```
[ AI SCHOOL | الفرع ]  ---  [ الرئيسية ] [ الطلاب والتسجيل ▾ ] [ الشؤون الأكاديمية ▾ ] [ الإدارة المالية ▾ ] [ التقارير ▾ ] [ إدارة النظام ▾ ]  ---  [ 🔍 بحث ] [ 🔔 ] [ 🌙/☀️ ] [ (م) أحمد ▾ ]
```

### Component Details
1. **Brand Badge:**
   - 36x36px rounded badge with AI School logo.
   - Dynamic branch/tenant name badge (`$tenant->name`) in vibrant blue pill.
2. **Dashboard Button:**
   - Standalone icon + label button with active indicator when viewing root dashboard.
3. **Mega Category Buttons:**
   - Category icon + label + animated rotating arrow chevron `▾`.
   - Hover background: `rgba(37, 99, 235, 0.06)`.
   - Active state: `#2563EB` text with glow bar.
4. **Utility Bar:**
   - Global search input (expanding on focus).
   - Real-time notifications bell with unread badge.
   - Accessible theme toggle button (SVG sun/moon).
   - User profile dropdown with gradient initials avatar, user name, role badge, and secure logout form.
