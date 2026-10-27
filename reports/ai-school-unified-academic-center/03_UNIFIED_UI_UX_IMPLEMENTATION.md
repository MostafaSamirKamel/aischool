# 03 — UNIFIED UI/UX IMPLEMENTATION & DESIGN SYSTEM

## 1. Design Aesthetics & Visual Identity
The unified Academic Center adheres strictly to the approved AI School design tokens:
- **Direction & Language:** Arabic RTL first (`dir="rtl"`), font family Tajawal where available, clean modern typography.
- **Color Palette:**
  - Primary / Navy: `--hrc-primary: #1e3a8a`, `--hrc-primary-dark: #172554`, `--hrc-primary-light: #eff6ff`
  - Emerald / Passing & Graded: `--hrc-emerald: #059669`, `--hrc-emerald-light: #ecfdf5`
  - Amber / Pending & Warnings: `--hrc-amber: #d97706`, `--hrc-amber-light: #fffbeb`
  - Purple / Exams & Grants: `--hrc-purple: #7c3aed`, `--hrc-purple-light: #f5f3ff`
  - Red / Danger & Late Overdue: `--hrc-red: #dc2626`, `--hrc-red-light: #fef2f2`
  - Slate / Neutral Borders & Backgrounds: `--hrc-card-bg: #ffffff`, `--hrc-card-border: #e2e8f0`
- **Dark Mode Support:** CSS custom variables automatically adapt under `.dark` root classes, preserving high contrast and readable text without eye fatigue.

## 2. Layout Structure of the Academic Center
```
+-------------------------------------------------------------------------+
| BREADCRUMBS: لوحة المعلم / المجموعات الدراسية / [Group Name]            |
| HEADER: المركز الأكاديمي لمجموعة: [Group Name] | مقرر: [Course Name]     |
| KPI STRIP: [عدد الجلسات] [عدد الواجبات] [بانتظار التصحيح] [عدد الامتحانات] |
+-------------------------------------------------------------------------+
| TABS: [ 1. الواجبات ]  [ 2. الامتحانات ]  [ 3. التسليمات ]  [ 4. النتائج ]   |
+-------------------------------------------------------------------------+
| TAB CONTENT AREA:                                                       |
|                                                                         |
| (1) Homework: Session Accordion -> Assignments -> Student Roster Table  |
| (2) Exams: Group Exams Grid -> Question Preview -> Attempts Roster      |
| (3) Submissions: Universal Cross-Session Filterable Submissions Stream  |
| (4) Results: Student Directory -> Avg Scores -> Profile Modal           |
+-------------------------------------------------------------------------+
```

## 3. Micro-Interactions & Responsiveness
1. **Interactive Image Lightbox:**
   - Supports keyboard navigation (Arrow keys), zoom buttons (`+`, `-`, `100%`), image counter (`1 / 3`), thumbnail bar, and fullscreen modal overlay.
2. **Next Student ("الطالب التالي") Workflow:**
   - Automatically jumps to the next submitted student awaiting grading without leaving the grading drawer, showing a celebration completion dialog when all are graded.
3. **Responsive Breakpoints:**
   - Desktop (`>= 1024px`): Full multi-column grids, comprehensive student roster tables with inline action buttons.
   - Tablet (`768px - 1023px`): 2-column KPI cards and adaptable tables.
   - Mobile (`320px - 767px`): Single-column cards, full-width touch targets, sticky modal footers.
