# Report 10: Instructor Homework Grading, Image Zoom, and Feedback

## 1. Grading Workflow Execution
The Lead Instructor performed grading operations through the Instructor Academic Center:
1. **Submission Inspection:** Instructor opened each submission, reviewed student text, and viewed the attached PNG files.
2. **Image Zoom & Preview:** Verified modal preview for submitted homework images.
3. **Grade Attribution:** Numeric grades entered (out of 20.00).
4. **Constructive Feedback:** Written feedback provided in Arabic (e.g., *"إجابة نموذجية ممتازة وتنسيق رائع للكود"*).
5. **Immediate Publication:** Grades transitioned to `corrected` (`is_graded = 1`) and published immediately.

---

## 2. Score Range and Academic Alert Audit
- **High Performers (> 50%):** Received scores between 15.00 and 20.00. No academic alert triggered.
- **Threshold Performer (Exactly 50%):** Received score 10.00 / 20.00. Verified that exactly 50% **does not** trigger an underachievement alert.
- **Underperforming Cases (< 50%):** Received scores below 10.00. System correctly flagged submissions for academic follow-up without creating unwanted manual case entries.

---

## 3. Test Result Format
- **Test ID:** HMK-GRD-01
- **Module:** Instructor Grading & Feedback
- **Role:** Lead Instructor
- **Route:** `/instructor/groups/{id}/academic-center?tab=submissions`
- **Initial State:** 87 submitted student homeworks.
- **Actual Action Executed:** Entered grades, feedback, and saved correction.
- **Expected Result:** Grades persisted; student sees updated score in Student Portal immediately.
- **Actual Result:** All eligible submissions graded; official scores updated.
- **Status:** **PASS**