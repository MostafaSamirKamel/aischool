# Report 21: Instructor Panel and Academic Operations

## 1. Verified Instructor Workflows
Using account `01091812925` (Lead Instructor) in the Instructor Portal:

| Workflow Step | Screen / Component | Action Executed | Observed UI Behavior | Result |
|---|---|---|---|---|
| **1. Portal Login** | `/instructor/login` | Entered phone & password | Redirected to Instructor Dashboard | **PASS** |
| **2. Group Selection** | `/instructor/groups` | Selected Group A (Python Junior) | Opened Group Academic Center | **PASS** |
| **3. Attendance Entry** | Group Academic Center | Marked Session 1 attendance | Statuses saved; summaries updated | **PASS** |
| **4. Homework Assignment**| `tab=homework` | Created "واجب 1: المتغيرات" | Published immediately; visible to group | **PASS** |
| **5. Submission Review** | `tab=submissions` | Opened student submission | Rendered text and thumbnail image | **PASS** |
| **6. Image Zoom Modal** | Submissions Viewer | Clicked attached student PNG | Expanded high-resolution preview | **PASS** |
| **7. Grading & Feedback**| Correction Form | Entered 18.00/20.00 & feedback | Clicked "تم التصحيح"; saved | **PASS** |
| **8. Next Student Flow** | Submissions Viewer | Clicked "التالي" | Navigated to next submission seamlessly | **PASS** |
| **9. Exam Question Review**| `tab=exams` | Inspected Quiz questions | 15 questions and answers displayed | **PASS** |
| **10. Salary Visibility**| `/instructor/salary` | Checked session earnings | 9 completed sessions @ 150 EGP displayed | **PASS** |

---

## 2. Test Result Format
- **Test ID:** INS-OPS-01
- **Module:** Instructor Academic Portal
- **Role:** Lead Instructor
- **Route:** `/instructor/*`
- **Initial State:** Assigned instructor for Groups A, B, C.
- **Actual Action Executed:** 10 end-to-end teaching and grading workflows executed.
- **Expected Result:** Academic center tabs functional; image preview operational; grading updates official scores.
- **Actual Result:** Complete academic cycle verified with live component testing.
- **Status:** **PASS**