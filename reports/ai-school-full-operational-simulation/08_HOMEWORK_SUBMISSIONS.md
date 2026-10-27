# AI SCHOOL — HOMEWORK LIFECYCLE AND SUBMISSION TESTING
**Document ID:** `08_HOMEWORK_SUBMISSIONS.md`  
**Execution Date:** 2026-10-25  
**Audit Scope:** Assignment Authoring, Multi-Image Uploads, Due Dates, Late Submissions, Instructor Grading Rubrics  

---

## 1. Executive Summary

This report validates the end-to-end homework assignment and submission subsystem. In accordance with the prompt's instructions, actual homework tasks were authored, submissions uploaded with multiple attachments, and grades published to student and parent dashboards.

---

## 2. Operational Test Results Matrix

| Test ID | Operation | Real Input Tested | Expected Outcome | Actual Database State | Status |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **HW-01** | Assignment Authoring | Python OOP Assignment (Max: 20 marks) | Assignment created with status `published` | Row in `assignments` | **PASS** |
| **HW-02** | Student Submission | Text answer + 2 solution image attachments | Attachments stored in storage, submission logged | Row in `assignment_submissions` | **PASS** |
| **HW-03** | Multi-Image Storage | Verify attachments JSON array | 2 valid relative file paths stored | `images` array populated | **PASS** |
| **HW-04** | Late Submission Check | Submit after `due_date` | Submission recorded with `is_late` flag | Flag evaluated accurately | **PASS** |
| **HW-05** | Instructor Grading | Instructor assigns 19/20 + detailed feedback | Score persisted, status becomes `graded` | `score = 19`, `status = 'graded'` | **PASS** |
| **HW-06** | Student Portal View | Student logs in to `/student` | Graded score and feedback visible | Visible on student portal | **PASS** |
| **HW-07** | Parent Portal View | Parent logs in to `/parent` | Child's homework score visible | Visible on parent portal | **PASS** |

---

## 3. Storage and File Attachment Safety

- **Storage Structure:** Homework attachments are stored under `storage/app/public/attachments/homework/`.
- **MIME Safety:** File extensions are validated (`png`, `jpg`, `jpeg`, `pdf`).
- **File Limit:** Multi-image uploads are constrained by size and count to prevent server storage abuse.

---

## 4. Verification Verdict

**STATUS: PASS**  
The homework lifecycle is fully functional from initial teacher authoring through multi-image student submission, evaluation, and portal publication.
