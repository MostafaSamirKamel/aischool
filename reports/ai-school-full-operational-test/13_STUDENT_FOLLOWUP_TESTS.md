# 13 — Student Academic Follow-Up Center & Alert Workflows

## 1. Academic Alert Detection
Upon publishing Student C's failing score (6/20 = 30% < 50%), the system automatically registered an **Academic Alert** (`AcademicAlert`) for low academic performance.

## 2. Follow-Up Task Lifecycle Testing
Acted as the Follow-Up Specialist (`registration.cairo.1@aischool.test`).
- **Follow-Up Task Created:**
  - Title: "متابعة تدني درجات الطالب في اختبار الذكاء الاصطناعي"
  - Student: `يوسف أحمد` (`Student ID: 109`)
  - Status: `StudentFollowUpTask::STATUS_NEW` ('new' / جديدة)

## 3. Strict 2-Status Transition Verification
> **Business Rule:** AI School follow-up tasks strictly enforce two statuses: `جديدة` ('new') and `تم التواصل` ('contacted'). An unanswered call must NOT transition the task to Contacted!

| Call Attempt | Contact Status | Notes Recorded | Resulting Task Status | Verified Rule |
|---|---|---|---|---|
| **Attempt 1** | `unanswered` (لم يتم الرد) | "تم الاتصال بولي الأمر ولم يرد على الهاتف" | **`new` (جديدة)** | **PASS: Status preserved as new** |
| **Attempt 2** | `answered` (تم الرد بنجاح) | "تم التحدث مع ولي الأمر والاتفاق على خطة تقوية" | **`contacted` (تم التواصل)** | **PASS: Status transitioned cleanly** |

## 4. Reporting & Audit
- Follow-up log persisted in `student_follow_up_call_logs`.
- Daily Follow-Up Center dashboard updated: Open tasks: 0, Contacted tasks: 1.
