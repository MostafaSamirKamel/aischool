# 02 — ALL ACADEMIC PAGES & INTERFACES INVENTORY

## 1. Inventory of Academic Screens Across Panels

### A. Instructor Panel (`App\Filament\Instructor\Pages`)
1. **Unified Academic Center (مركز مراجعة واعتماد الواجبات والامتحانات الشامل):**
   - **Route / URL:** `/instructor/homework-review-center`
   - **Class:** [HomeworkReviewCenter](file:///d:/ai_school_new-main%20%281%29/ai_school_new-main/app/Filament/Instructor/Pages/HomeworkReviewCenter.php)
   - **View:** [homework-review-center.blade.php](file:///d:/ai_school_new-main%20%281%29/ai_school_new-main/resources/views/filament/instructor/pages/homework-review-center.blade.php)
   - **Four Core Tabs:**
     - `activeTab = 'homework'`: Session-based homework workflow, unlinked assignments, student roster, single-student deadline reopening, group extensions, resubmission grants.
     - `activeTab = 'exams'`: Group exams, optional session links, attempts roster, retake grants, publication toggle, question review.
     - `activeTab = 'submissions'`: Unified list of all submissions across all group sessions with multi-criteria filtering (session, assignment, status) and quick answer viewing.
     - `activeTab = 'results'`: Student-centric results directory, completion percentages, average homework/exam scores, `< 50%` alert indicators, and detailed student academic profile modal.
   - **Integrated Modals (9 Modals):**
     - Create/Edit Homework Modal
     - Homework Deadline Extension Modal (Group / Student)
     - Homework Resubmission Grant Modal
     - Published Grade Correction Modal (with audit logging)
     - Create/Edit Exam Modal (multi-step)
     - Exam Retake Grant Modal
     - Student Exam Attempts Modal
     - Answer Lightbox & Zoom Viewer Modal
     - Student Full Academic Profile Modal

2. **Instructor Groups Management:**
   - **Route:** `/instructor/groups`
   - **Class:** `App\Filament\Instructor\Resources\Groups\GroupResource`
   - **Integration:** Directly links into `/instructor/homework-review-center?group={id}` for instant academic access.

### B. Student Panel (`App\Filament\Student\Pages` & PWA)
1. **Student My Exams (`MyExams`):**
   - **Route:** `/student/my-exams`
   - **Class:** `App\Filament\Student\Pages\MyExams`
   - **Functionality:** Lists upcoming, in-progress, and completed exams. Detects active `ExamRetakeGrant` records and displays `إعادة الاختبار (مصرح بها)`. Masks scores for unpublished exams (`published_at IS NULL`).
2. **Student Take Exam (`TakeExam`):**
   - **Route:** `/student/take-exam/{exam}`
   - **Class:** `App\Filament\Student\Pages\TakeExam`
   - **Functionality:** Server-authoritative timer, auto-saving answers, resilience to network disconnection, resumption of active attempt, atomic consumption of retake grant upon attempt creation.
3. **Student Exam Result (`ExamResult`):**
   - **Route:** `/student/exam-result/{attempt}`
   - **View:** `resources/views/filament/student/pages/exam-result.blade.php`
   - **Functionality:** Obey exam publication policy; hides numeric grades and question breakdowns until instructor/admin publishes the exam.
4. **Student Homework & Assignments:**
   - **Route:** `/student/assignments`
   - **Functionality:** Submissions view, image uploads, resubmission grant awareness, instant display of official published grades and instructor comments upon correction.

### C. Parent Panel (`App\Filament\Parent\Pages`)
1. **Parent Student Reports (`StudentReports`):**
   - **Route:** `/parent/student-reports`
   - **Class:** `App\Filament\Parent\Pages\StudentReports`
   - **View:** `resources/views/filament/parent/pages/student-reports.blade.php`
   - **Functionality:** Displays linked children's homework grades immediately upon instructor grading ("تم التصحيح"), and displays exam results strictly when published (`whereNotNull('published_at')`).

### D. Educational Supervision & Admin (`App\Filament\Admin`)
1. **Supervision Reports & Academic Follow-up:**
   - **Route:** `/admin/supervision-reports`
   - **Functionality:** Tracks attendance, homework completion, exam performance, low-grade alerts (`< 50%`), and contact records without generating fake follow-up tasks.
