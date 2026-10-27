# تقرير الأسباب الجذرية والتشخيص الأمني التفصيلي
## AI SCHOOL — SECURITY FINDINGS & ROOT CAUSES REPORT
**التاريخ والوقت:** 2026-10-20T23:25:30+03:00  
**المستوى الأمني:** Critical Remediation (P0 / P1)  
**المستند:** مصفوفة تشخيص الثغرات الأمنية SEC-01 إلى SEC-04

---

## 1. ثغرة SEC-01: قصور التحقق من الصلاحيات في استدعاءات Livewire المباشرة

### أ. التشخيص الفني والسبب الجذري (Root Cause)
تعتمد مكتبة Livewire على تبادل حالة المكونات وإجراءات الميثودز عبر اتصالات HTTP POST متكررة تحمل اسم الميثود والمعاملات الممررة له (`updates.payload.method`, `params`).
في المكون `AcademicFollowUpCenter.php`، تم تصميم الميثودز الحساسة بالاعتماد الضمني على أن واجهة المستخدم الرسومية (UI) لن تعرض للطالب أو الموظف إلا الحالات المصرح له بها، لكن تم إغفال تطبيق التحقق الأمني من ملكية المجموعات (Server-Side Group Ownership Validation) لحظة تنفيذ الميثود.

### ب. دوال الكود المتأثرة:
1. `openStudentProfile(int $studentId)`
2. `openFollowUpModal(...)`
3. `saveFollowUpTask()`
4. `openContactLogModal(int $taskId)`
5. `saveContactLog()`
6. `openHistoryModal(int $taskId)`
7. `saveReassignment()`

### ج. سيناريو الاستغلال العملي (Exploit Vector):
- موظفة السكرتارية (سارة) مكلفة فقط بالمجموعة (أ) في فرع القاهرة.
- موظفة السكرتارية (منى) مكلفة بالمجموعة (ب) في نفس الفرع.
- تقوم سارة باعتراض أو تكرار طلب شبكي واستبدال `$taskId` العائد لمهمة تابعة للمجموعة (ب).
- قبل الإصلاح: كان الميثود يستعلم `StudentFollowUpTask::findOrFail($taskId)` وينفذ العملية بنجاح لأن المهمة تنتمي لنفس الـ `tenant_id`، متجاهلاً شرط أن سارة غير معينة للمجموعة (ب).

---

## 2. ثغرة SEC-02: انكشاف البيانات الأكاديمية الشاملة للطالب (Student Profile Data Exposure)

### أ. التشخيص الفني والسبب الجذري:
في `AcademicFollowUpService::getStudentProfile()`، كان الغرض الأساسي تقديم ملف موحد يجمع غياب وواجبات وامتحانات وأقساط الطالب.
لكن السبب الجذري للخلل تمثل في:
1. غياب فحص ما إذا كان الطالب المقيد في عدة مواد دراسية يتشارك مع الموظف المشاهد في مجموعة مصرح بها.
2. عدم تقييد استعلامات العلاقات الأكاديمية (`attendanceRecords`, `submissions`, `examAttempts`, `tasks`) بمعرفات المجموعات المصرح بها للموظف (`authorizedGroupIds`).
3. تمرير كائنات Eloquent غير مقيدة بالكامل (`student`, `submissions`, `examAttempts`) إلى Livewire، مما يؤدي إلى قيام Livewire بـ Serialization لكافة الحقول والعلاقات وإرسالها في الاستجابة الشبكية JSON.

### ب. الأثر الأمني:
خرق خصوصية الطالب الأكاديمية؛ حيث تستطيع سكرتيرة مادة الحاسب الآلي الإطلاع على درجاته وواجباته في مواد اللغات أو البرمجة المتقدمة التي لا تقع ضمن اختصاصها الإشرافي.

---

## 3. ثغرة SEC-03: تجاوز صلاحية أرقام الهواتف (Phone Number Permission Bypass)

### أ. التشخيص الفني والسبب الجذري:
تم رصد خللين منفصلين:
1. **خلل في سياسة الصلاحيات الأساسية (`AcademicFollowUpService::canViewPhone`):**
   كانت الدالة تمنح حق الاطلاع التلقائي لكل من يحمل دور `secretary`:
   ```php
   // قبل الإصلاح:
   return $user->is_super_admin
       || $user->hasRole('admin')
       || $user->hasRole('branch_manager')
       || $user->hasRole('secretary') // ثغرة تجاوز!
       || $user->can('view_student_contact_numbers')
   ```
   وهذا خرق للسياسة المعتمدة التي تشترط منح صلاحية مستقلة `view_student_contact_numbers` أو `view_student_phone`.
2. **خلل في ملء خواص Livewire العامة:**
   في دوال `openFollowUpModal` و `openContactLogModal`، كان يتم جلب رقم ولي الأمر ورقم الطالب وتخزينهما في الخواص العامة:
   `$this->activeParentPhone = $service->resolveParentPhone($student);`
   دون فحص دالة `canViewPhone`، مما ينقل الأرقام الحقيقية في الـ Livewire Component State عبر الشبكة حتى لو لم تكن ظاهرة للموظف في الواجهة.

---

## 4. ثغرة SEC-04: تجاوز الصلاحيات المعتمدة على الدور (Role-Based Access Bypass)

### أ. التشخيص الفني والسبب الجذري:
في Filament، تتحكم دالتا `shouldRegisterNavigation()` و `mount()` في ظهور الرابط والدخول إلى الصفحة.
كان الكود يعتمد على فحص أسماء الأدوار بدلاً من الصلاحيات المستقلة:
```php
// قبل الإصلاح:
$user->hasAnyRole(['admin', 'branch_manager', 'supervisor', 'secretary', 'registration'])
```
هذا الأسلوب تسبب في:
1. إذا قام مدير النظام بإلغاء صلاحية `view_daily_follow_up_dashboard` عن سكرتيرة أو مشرف، ظل بإمكانهما فتح الصفحة بالكامل عبر الرابط المباشر لأن شرط الدور ما زال محققاً.
2. تداخل الأدوار مع الصلاحيات المستقلة، وهو ما يخالف المعايير القياسية لمنظومة Spatie Permission المعتمدة في النظام.

---

## 5. معالجة الثغرات الإضافية (Task Integrity & Race Conditions)
بالإضافة لما تم رصده في التقرير المستقل، تم تشخيص وعلاج:
- **إعادة التكليف غير المؤهلة (Ineligible Reassignment):** التأكد من أن الموظف المحال إليه المهمة يتبع نفس الفرع الأكاديمي ونشط وليس مجرد أي حساب مسجل في جدول `users`.
- **التكرار العرضي للمهام والاتصالات (Duplicate Submission / Double-Clicking):** حماية زر الحفظ بقفل زمني ومنع تكرار إنشاء الحالات المتطابقة لنفس الطالب والمصدر خلال ثوانٍ معدودة، واستخدام العمليات الآمنة `DB::transaction`.
