# 02 — Approved Business Rules & Specification Matrix

## 1. Role Scopes and Multi-Branch Boundaries

| Role Code | Scope Boundaries | Permissions |
| :--- | :--- | :--- |
| `branch_educational_supervisor` | All active groups, instructors, and sessions within their assigned branch (`tenant_id`). | Full supervisor panel access, observation creation, completion approval, evaluation drafting & publishing, report drafting & publishing. |
| `secretary` / Limited Supervisor | Strictly restricted to groups explicitly assigned in `supervision_group_assignments`. Zero access to unassigned groups. | View/manage assigned groups and linked instructors. No branch-wide observation closing or report publishing unless explicitly permitted. |
| `instructor` | Own classes, assignments, assigned observations, published evaluations, and appeal submission. | Cannot approve observation closure (`مكتملة`), cannot see draft evaluations, cannot modify evaluation scores. |
| `super_admin` / Management | Global (or branch manager scoped to branch). | Review published supervision reports, request clarifications/revisions, adjudicate formal evaluation appeals (final decision). |

## 2. Observation Lifecycle Rules
- **Status Enum:** `new` (`جديدة`), `waiting_supervisor_review` (`بانتظار مراجعة المشرف`), `completed` (`مكتملة`).
- **Instructor Action:** When an instructor addresses an observation, status moves strictly to `waiting_supervisor_review`. Instructors CANNOT set status to `completed`.
- **Supervisor Action:** Only supervisors can transition an observation from `waiting_supervisor_review` to `completed` (accepted) or return it to `new` (rejected/correction needed).
- **Audit Requirement:** Every status transition automatically creates a record in `supervisory_observation_histories` capturing `user_id`, `from_status`, `to_status`, `action`, `notes`, and timestamp.

## 3. Instructor Evaluation Rules
- **Rating Criteria (6 Core Axes):**
  1. `commitment_score`: Punctuality, attendance recording, syllabus compliance.
  2. `delivery_score`: Pedagogical clarity, student engagement, interactive delivery.
  3. `student_interaction_score`: Rapport, patience, responsive classroom management.
  4. `homework_followup_score`: Homework review speed, constructive feedback quality.
  5. `exam_execution_score`: Exam readiness, grading timeliness, integrity.
  6. `administrative_compliance_score`: Platform adherence, communication with administration.
- **Scale:** 1 to 5 integers.
- **Unobserved Rule:** Any criterion flagged `*_unobserved = true` is strictly EXCLUDED from the arithmetic calculation of `overall_rating`. For instance, if 5 criteria are rated 4 and 1 is unobserved, the average is 20/5 = 4.00, not 20/6.
- **Draft Isolation:** Evaluations remain in `draft` status until explicitly published by the supervisor. Instructors CANNOT view draft evaluations.
- **Instructor Feedback:**
  - *Informal Comment:* Instructors can add explanatory notes to a published evaluation.
  - *Formal Appeal (`appeal`):* Moves the evaluation to `under_appeal`. Only Management (`super_admin` / Branch Manager) can resolve the appeal with a binding status: `accepted`, `rejected`, or `returned_for_clarification`.

## 4. Supervisory Reporting Rules
- **Report Types:** `weekly` (`SR-WK-YYYY-WW-XXXX`) and `monthly` (`SR-MO-YYYY-MM-XXXX`).
- **Idempotency:** Requesting report draft generation for an existing period retrieves the existing draft without duplicate record creation.
- **Management Review:** Management can request clarifications or revisions. Revisions unlock the report for supervisor updates and create immutable version snapshots in `supervision_report_versions`.

## 5. Academic Data Integrity Rule (Attendance vs. Absence)
- **Unrecorded Attendance Sessions:** When an attendance session has not yet been registered by an instructor, students in that session are categorized under **Unrecorded Attendance** (`sessions_missing_attendance`).
- **Absence Integrity:** Students are NEVER automatically flagged or counted as `absent` due to an instructor's failure to submit attendance. Absence is strictly confirmed student absence from submitted records.
