# 11 — Monthly Evaluation Workflow & Draft Isolation

## 1. Lifecycle Overview
Evaluations follow a strict two-stage lifecycle: **Draft** and **Published**.

```
    [Supervisor Prepares Evaluation]
                  │
                  ▼
         ┌─────────────────┐
         │      DRAFT      │◄─── HIDDEN from Instructor
         │     (مسودة)     │     Zero visibility in /instructor
         └────────┬────────┘
                  │
        Supervisor Clicks "نشر التقييم"
        (Explicit Publication)
                  │
                  ▼
         ┌─────────────────┐
         │    PUBLISHED    │◄─── VISIBLE to Instructor
         │     (منشور)     │     Instructor can comment or appeal
         └─────────────────┘
```

## 2. Draft Isolation Rule
- While an evaluation is in `draft` status, it is strictly visible ONLY to supervisors and administrators.
- When an instructor logs into their panel (`/instructor/instructor-my-evaluations`), Eloquent queries automatically append:
  ```php
  $query->where('status', '!=', InstructorEvaluation::STATUS_DRAFT);
  ```
- This prevents incomplete, preliminary, or contentious draft feedback from leaking to instructors before supervisor sign-off.

## 3. Versioning on Publication
When a supervisor publishes or re-evaluates an instructor:
1. `InstructorEvaluationVersion::create([ ... ])` snapshots the exact criteria scores, unobserved flags, overall rating, qualitative comments, and publishing supervisor ID.
2. The version number (`version_number`) increments sequentially.
3. This guarantees an immutable historical record if modifications or appeals occur later.
