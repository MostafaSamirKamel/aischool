# Report 14: Exam Grading, Retake Grants, and Highest Score Rule

## 1. Approved Retake Governance Model
- **Grant Authority:** Independent retake grants must be explicitly issued by an authorized employee (`ExamRetakeGrant`).
- **Single Use:** Each grant allows exactly one additional attempt.
- **Historical Preservation:** Previous attempts and answers are strictly preserved in the database.
- **Official Score Rule:** The student's official grade for the exam is the **highest eligible completed attempt score**.

---

## 2. Retake Execution Test Case
1. **Initial Attempt:**
   - Student: Student ID 204 (يوسف أحمد محمود)
   - Exam: Exam ID 110 (اختبار قصير 1)
   - Initial Score: **8.00 / 20.00 (40.0%)** — FAIL
2. **Authorized Retake Grant:**
   - Grant ID: 1
   - Reason: *"إعادة مرخصة بعد مراجعة المفاهيم الأساسية"*
   - Validity Window: Active for 48 hours.
3. **Second Attempt:**
   - Retake Score: **16.00 / 20.00 (80.0%)** — PASS
4. **Official Record Verification:**
   - Official score evaluated: `max(8.00, 16.00) = 16.00`.
   - Academic records and reports publish **16.00 / 20.00**.

> [!TIP]
> Negative test also executed: A retake resulting in a lower score (e.g., initial 14, retake 11) leaves the official grade intact at 14.00.

---

## 3. Test Result Format
- **Test ID:** EXM-RTK-01
- **Module:** Academic Retake Engine
- **Role:** Branch Manager / Instructor
- **Route:** `/admin/exam-retake-grants`, `/student/exams`
- **Initial State:** Student completed initial attempt.
- **Actual Action Executed:** Issued retake grant, performed second attempt, validated official grade aggregation.
- **Expected Result:** Both attempts preserved; highest score becomes official.
- **Actual Result:** Official score updated to 16.00; zero data loss of attempt 1.
- **Status:** **PASS**