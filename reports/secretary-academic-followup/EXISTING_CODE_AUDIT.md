# AI SCHOOL — SECRETARY ACADEMIC FOLLOW-UP CENTER
## 01 — EXISTING CODE AUDIT REPORT (تقرير الفحص الشامل للكود الحالي)

**تاريخ التدقيق:** 20 أكتوبر 2026  
**النظام:** AI School Laravel Management System  
**البيئة:** Filament 3 / Livewire 3 / Laravel 12 / PHP 8.2+  
**الهدف:** فحص البنية التحتية القائمة للامتحانات، الواجبات، علاقات الطلاب بأولياء الأمور، مهام المتابعة، والصلاحيات؛ لضمان التوسعة الآمنة دون المساس بالمنطق الأكاديمي أو المالي القائم.

---

### 1. ملخص الفحص الهندسي والقرارات المعمارية

| العنصر المفحوص | الحالة قبل التطوير | القصور / الفجوة المكتشفة | القرار الهندسي والتوسعة |
|---|---|---|---|
| **سجل محاولات الامتحانات (`ExamAttempt`)** | يعرض فقط الطلاب الذين خاضوا الامتحان (`whereNotNull('completed_at')`). | غير كافٍ كقاعدة بيانات لمتابعة المجموعة، لأن الطالب المتأخر أو الذي لم يحل نهائياً لا يملك سجلاً! | بناء كشف الطلاب المؤهلين للمجموعة أولاً (`Eligible Roster`) ثم عمل الربط (`Left Join / Eager Load`) مع محاولاتهم. |
| **سجل تسليم الواجبات (`AssignmentSubmission`)** | يدعم `isPublished()` و `isDraft()` ودرجات مسودة لم تعتمد. | كشف الدرجات المسودة للسكرتيرة يؤدي لتسريب نتائج غير معتمدة من المعلم. | عزل وحجب الدرجات المسودة تماماً عن السكرتيرة، وإظهار حالة `بانتظار اعتماد الدرجة` حصراً. |
| **مهام المتابعة (`StudentFollowUpTask`)** | يدعم `outcome_notes` كملاحظة ختامية واحدة. | لا يوجد سجل زمني تعاقبي (`Chronological Timeline`) لكل مكالمة أو تواصل منفصل مع ولي الأمر. | إضافة جدول ونموذج تعاقبي جديد `StudentFollowUpContact` مرتبط بمهمة المتابعة دون حذف أو تعديل المهام السابقة. |
| **صلاحية المعينين في المتابعة (`assignee_id`)** | يقتصر على الأدوار الإدارية والإشرافية فقط (`admin`, `supervisor`). | السكرتيرة (`secretary`) غير متاحة في القائمة المنسدلة لتعيين مهام المتابعة. | تحديث `StudentFollowUpTaskResource` وإضافة دور `secretary` للمعينين المصرح لهم. |
| **أرقام الهواتف (الطالب vs ولي الأمر)** | تداخل في بعض الواجهات بين `phone` و `phone_2` والولي. | خلط رقم الطالب برقم ولي الأمر أو اعتبارهما رقماً واحداً. | عزل دقيق لرقم ولي الأمر الأساسي (`StudentParent.is_primary -> User.phone`) ورقم الطالب (`Student.phone`) في عمودين منفصلين ومستقلين تماماً. |
| **التحويلات بين المجموعات (`StudentGroupTransfer`)** | الطالب المنقول بعد استحقاق واجب قديم كان يُفترض فيه التأخر إذا تم الاعتماد على `group_id` الحالي. | اتهام الطالب المنقول ظلماً بأنه متأخر في واجبات تم إعلانها قبل تاريخ نقله. | مراعاة تاريخ النقل الفعلي `transferred_at` ومقارنته بتاريخ استحقاق الواجب `due_date` وتاريخ الامتحان `exam_date`. |

---

### 2. فحص نماذج الامتحانات (Examination Components)

#### النماذج المفحوصة:
- `app/Models/Exam.php`
- `app/Models/ExamAttempt.php`
- `app/Filament/Admin/Resources/Exams/ExamResource.php`
- `app/Filament/Admin/Resources/Exams/RelationManagers/AttemptsRelationManager.php`

#### الملاحظات التقنية:
1. يمتلك جدول `exams` الحقول: `id`, `tenant_id`, `course_id`, `group_id`, `instructor_id`, `title`, `exam_date`, `duration`, `total_marks`, `passing_marks`, `status`.
2. حالات الامتحان المعتمدة في الـ enum: `scheduled`, `in_progress`, `completed`, `cancelled`.
3. طريقة حساب انتهاء الامتحان الرسمية:
   - في حال وجود `duration`، ينتهي الامتحان بعد: `exam_date + duration minutes`.
   - يتم التحقق من عدم وسم الامتحان بأنه متأخر إذا كان موعده مستقبلياً (`exam_date > now()`).
4. درجة الصفر:
   - الطالب الذي يحل الامتحان ويحصل رسمياً على 0 يحفظ في قاعدة البيانات كـ `score = 0.00`.
   - تم ضمان عدم تحويل الصفر إلى شرطة (`—`) أو قيمة خالية، بل يظهر: `0 / total_marks`.

---

### 3. فحص نماذج الواجبات (Assignment Components)

#### النماذج المفحوصة:
- `app/Models/Assignment.php`
- `app/Models/AssignmentSubmission.php`
- `app/Services/HomeworkService.php`
- `app/Filament/Instructor/Resources/Assignments/Pages/ManageAssignmentSubmissions.php`

#### الملاحظات التقنية:
1. جدول `assignments` يحتوي على `max_score` و `due_date` (نوعه `date`).
2. لحساب الموعد النهائي الفعلي للواجب دون ظلم الطالب في منتصف اليوم، يتم احتساب نهاية اليوم المحدد: `Carbon::parse($assignment->due_date)->endOfDay()`.
3. جدول `assignment_submissions` يفرق بين حالتين جوهريتين للدرجة:
   - الدرجة المعتمدة والمنشورة: `published_at !== null && score !== null` (`isPublished() === true`).
   - درجة المسودة غير المعتمدة: `published_at === null && score !== null` (`isDraft() === true`).
4. قاعدة السرية للسكرتيرة:
   - يمنع قطعياً إظهار الدرجة المسودة (مثلاً 9.5) في واجهة السكرتيرة، ويتم عرض عبارة `بانتظار اعتماد الدرجة`.
   - لا يتم نشر أي درجة إلا بعد موافقة المعلم رسمياً.

---

### 4. فحص نظام المتابعة وتتبع التواصل (Follow-Up Components)

#### النماذج المفحوصة:
- `app/Models/StudentFollowUpTask.php`
- `app/Services/StudentFollowUpService.php`
- `app/Filament/Admin/Resources/StudentFollowUpTaskResource.php`

#### التوسعة المنفذة:
1. جدول `student_follow_up_tasks`:
   - تم تعزيزه بحقول مرجعية للربط الأكاديمي:
     - `source_type`: نوع المصدر (`exam` أو `homework` أو `attendance`).
     - `source_id`: المعرف الرقمي الصريح للامتحان أو الواجب.
     - `academic_status`: الحالة الأكاديمية للطالب لحظة فتح المتابعة.
2. جدول ونموذج `student_follow_up_contacts`:
   - تم إنشاؤه عبر الهجرة `2026_10_20_000004_create_student_follow_up_contacts_table.php`.
   - يحفظ تفاصيل كل اتصال منفصلة تماماً مع ختم زمني (`contacted_at`), هوية الموظف (`contacted_by`), طريقة الاتصال (`contact_method`), نتيجة التواصل (`contact_result`), والملاحظات (`notes`).
   - لا يتم إغلاق المهمة تلقائياً بمجرد تسجيل مكالمة، بل يتطلب قراراً صريحاً من السكرتيرة.

---

### 5. فحص علاقات الطلاب وأولياء الأمور (Student & Parent Relationships)

#### النماذج المفحوصة:
- `app/Models/Student.php`
- `app/Models/StudentParent.php`
- `app/Models/User.php`

#### القواعد المعتمدة لاستخراج الهواتف:
1. **هاتف ولي الأمر:**
   - الأولوية الأولى: الولي المحدد كـ `is_primary = true` في جدول `student_parents`.
   - الأولوية الثانية: أي ولي أمر مرتبط بالطالب ومصرح به في نفس الفرع.
   - في حال عدم وجود أي ولي أمر مسجل: تظهر القيمة الدقيقة `"غير مسجل"` دون أي افتراضات خيالية.
   - يؤخذ الهاتف من جدول `users.phone` المرتبط بحساب ولي الأمر.
2. **هاتف الطالب:**
   - يؤخذ مباشرة من حقل `students.phone`.
   - كبديل موثوق: يؤخذ من `user.phone` لحساب الطالب نفسه.
   - يتم التعامل مع الهاتف كسلسلة نصية (`string`) للحفاظ التام على الصفر الأولي في الأرقام المصرية (مثل `010...` و `011...`).

---

### 6. التحقق من العزل وتعدد الفروع (Multi-Tenant & Branch Isolation)

1. تم التأكد من أن جميع الاستعلامات خاضعة لنطاق الفرع `TenantScope` و `BelongsToBranch`.
2. تمنع السكرتيرة التابعة لفرع (مثل فرع القاهرة) من استعراض مجموعات أو طلاب أو هواتف فرع آخر (مثل فرع دمنهور).
3. لا يتم استخدام أي `withoutGlobalScopes()` بدون إعادة تطبيق فلتر الـ `tenant_id` بدقة لحماية بيانات الطلاب وأولياء الأمور من أي تسريب.

---

**خاتمة التدقيق:**
النظام جاهز معمارياً للتوسعة، وقد تم تطبيق جميع الإضافات بإحكام توافقي تراكمي 100% دون كسر أي شاشات أو خدمات قديمة.
