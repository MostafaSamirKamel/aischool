# 12 — Instructor Comments vs. Formal Appeals

## 1. Distinction: Informal Comment vs. Formal Appeal
Instructors who receive a published monthly evaluation have two distinct channels to respond:

| Channel | Intent | Status Impact | Reviewer |
| :--- | :--- | :--- | :--- |
| **Informal Comment (`comment`)** | Acknowledge receipt, provide context, or clarify operational nuances without disputing scores. | Leaves evaluation in `published` status. | Educational Supervisor |
| **Formal Appeal (`appeal`)** | Formally contest an evaluation score, dispute alleged negligence, or claim unfair assessment. | Changes evaluation status to `under_appeal`. | Management (`super_admin` / Branch Manager) |

## 2. Formal Appeal Workflow
1. **Submission:**
   - Instructor opens their published evaluation in `/instructor/instructor-my-evaluations`.
   - Instructor clicks **"تقديم اعتراض رسمي للإدارة"** (Submit Formal Appeal to Management).
   - Form requires:
     - Specific criteria being contested.
     - Justification and evidence.
     - Supporting URLs or document links.
   - The system creates a record in `instructor_evaluation_appeals` with `type = 'appeal'` and `status = 'pending_management'`.
2. **Supervisor Response Window:**
   - Supervisor is notified of the appeal and can provide their context notes in the appeal record (`supervisor_response`).
3. **Escalation to Management:**
   - The appeal appears in the Admin Panel at `/admin/admin-instructor-evaluation-appeals`.
   - The supervisor CANNOT resolve the appeal; only Management can issue a final binding decision.
