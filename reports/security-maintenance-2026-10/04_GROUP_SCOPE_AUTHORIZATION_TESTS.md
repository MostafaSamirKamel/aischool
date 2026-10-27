# تقرير اختبارات عزل المجموعات داخل نفس الفرع الأكاديمي
## AI SCHOOL — GROUP-SCOPE AUTHORIZATION & SAME-BRANCH ISOLATION TESTS
**التاريخ والوقت:** 2026-10-20T23:26:30+03:00  
**المسؤول:** Quality Assurance Lead & Security Engineer  
**ملف الاختبار المعتمد:** `tests/Feature/LivewireFollowUpCenterSecurityTest.php`

---

## 1. فلسفة الاختبار ونموذج التهديد

في المدارس الكبرى، يضم الفرع الواحد (Tenant) عدة مجموعات دراسية ومواد متعددة، ويعمل في نفس الفرع أكثر من موظف سكرتارية ومتابعة.
- **الفرضية الأمنية المرفوضة:** الاكتفاء بأن المستخدم والمجموعة يتبعان نفس الفرع (`tenant_id`).
- **المعيار الأمني الصارم المعتمد:** لا يجوز لموظف المتابعة (سارة) الاطلاع على بيانات أو تسجيل اتصالات أو تعديل مهام المجموعة (ب) حتى لو كانت تنتمي لنفس فرعها الجغرافي، إلا بتعيين إشرافي صريح نشط في جدول `supervision_group_assignments`.

---

## 2. مصفوفة اختبارات العزل داخل نفس الفرع (Same-Branch Isolation Matrix)

| كود الاختبار | اسم الاختبار | الإجراء المختبر | النتيجة المتوقعة | النتيجة الفعلية | الحالة |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **SEC-GRP-01** | `test_01_sara_cannot_open_student_profile_for_unassigned_group_b_student` | `openStudentProfile(studentB)` | منع فتح النافذة، وعدم ملء `$activeStudentProfile`، وإرجاع null من السيرفس | لم تفتح النافذة، القيمة null، إشعار "غير مصرح" | **ناجح (PASS)** |
| **SEC-GRP-02** | `test_02_sara_cannot_open_followup_modal_for_unassigned_group_b_student` | `openFollowUpModal(studentB, ..., groupB)` | منع فتح نافذة إنشاء المهمة وعدم تفعيل الطالب | `showFollowUpModal = false`، الطالب غير مفعل | **ناجح (PASS)** |
| **SEC-GRP-03** | `test_03_sara_cannot_save_followup_task_for_unassigned_group_b_student` | `saveFollowUpTask` بمعرفات المجموعة (ب) | رفض التنفيذ بـ HTTP 403 Forbidden، وعدم زيادة سجلات المهام | رفض الطلب بكود 403، عدد المهام لم يتغير | **ناجح (PASS)** |
| **SEC-GRP-04** | `test_04_sara_cannot_open_contact_log_modal_for_unassigned_group_b_task` | `openContactLogModal(taskB)` | منع فتح نافذة رصد التواصل لمهمة مجموعة أخرى | `showContactLogModal = false`، المهمة غير مفعلة | **ناجح (PASS)** |
| **SEC-GRP-05** | `test_05_sara_cannot_save_contact_log_for_unassigned_group_b_task` | `saveContactLog` لمعرف مهمة في المجموعة (ب) | رفض العملية بـ HTTP 403 Forbidden، وعدم تسجيل أي اتصال في الداتابيز | رفض الطلب بكود 403، عدد الاتصالات لم يتغير | **ناجح (PASS)** |
| **SEC-GRP-06** | `test_06_sara_cannot_open_history_modal_for_unassigned_group_b_task` | `openHistoryModal(taskB)` | حجب السجل الزمني لمهمة المجموعة الأخرى ومنع تسريب الملاحظات | لم تفتح النافذة، السجل فارغ، الملاحظات لم تظهر | **ناجح (PASS)** |
| **SEC-GRP-07** | `test_07_sara_cannot_save_reassignment_for_unassigned_group_b_task` | `saveReassignment` على مهمة تابعة للمجموعة (ب) | رفض إعادة التكليف بـ HTTP 403 Forbidden، وبقاء الموظف الأصلي دون تغيير | رفض الطلب بكود 403، الموظف المكلف بقي منى | **ناجح (PASS)** |

---

## 3. اختبار الطالب المشترك بين مجموعتين (Multi-Course Scoping)

تم بناء الاختبار `test_08_multi_course_student_sara_sees_group_a_data_but_scratch_group_b_data_is_masked`:
- الطالب **أحمد** مسجل في:
  1. كورس بايثون (المجموعة أ) - مكلفة بها سارة.
  2. كورس سكراتش (المجموعة ب) - مكلفة بها منى.
- سجل الطالب يحتوي على:
  - حضور في بايثون، وغياب وملاحظات في سكراتش.
  - تسليم واجب في سكراتش بدرجة 18/20.
- **النتيجة المحققة:**
  - عند فتح سارة لملف أحمد، ظهرت له المجموعة (أ) فقط.
  - غياب كورس سكراتش تم حجبه بالكامل.
  - واجب كورس سكراتش تم حجبه بالكامل.
  - ظهرت لسارة فقط بيانات مادة بايثون المصرح لها بها.

---

## 4. اختبارات العزل بين الفروع (Cross-Tenant Isolation)
- الاختبار `test_11_reassign_task_to_ineligible_cross_branch_employee_is_rejected`:
  - محاولة سارة (فرع القاهرة) إعادة تكليف مهمة لموظف من (فرع الإسكندرية).
  - تم رفض العملية فورياً بكود استجابة `422 Unprocessable Entity` مع إشعار خطأ يوضح عدم انتماء الموظف لنفس الفرع.
- تم تأكيد حماية قواعد البيانات المتعددة لجميع الفروع دون أي تداخل في السجلات.
