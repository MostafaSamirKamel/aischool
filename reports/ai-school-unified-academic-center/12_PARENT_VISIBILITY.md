# 12 — PARENT RESULTS VISIBILITY & MOBILE EXPERIENCE

## 1. Parent Access Architecture
- **Route:** `/parent/student-reports`
- **Class:** `App\Filament\Parent\Pages\StudentReports`
- **Security:** Parents can strictly only access records of students linked to their user account via the `student_parents` table. Attempts to query or inspect foreign student data are blocked server-side.

## 2. Instant Homework Grade Reflection
- When an instructor grades homework and clicks **"تم التصحيح"**:
  - The official grade publishes immediately.
  - The authorized parent instantly sees the score, max score, submission punctuality, and instructor feedback in their portal.
  - Eliminates administrative delays and keeps parents directly informed.

## 3. Strict Exam Publication Enforcement
- Exam results are masked from parent views unless the exam has been explicitly published (`published_at IS NOT NULL`).
- An in-progress or graded exam that has not yet been authorized for release displays:
  - `الاختبار قيد المراجعة والاعتماد الأكاديمي`
  - Numeric score and percentage remain hidden until publication.

## 4. Mobile Responsiveness
- Parent views are optimized for mobile smartphone screens:
  - Sticky child selector dropdown.
  - Card-based progress summaries with clear Arabic badges.
  - Accessible touch buttons for downloading reports or contacting the school.
