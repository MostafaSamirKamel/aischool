# تقرير أمان استدعاءات وحزم بيانات Livewire
## AI SCHOOL — LIVEWIRE SECURITY & PAYLOAD PROTECTION REPORT
**التاريخ والوقت:** 2026-10-20T23:27:00+03:00  
**المسؤول:** Filament & Livewire Specialist / Security Auditor  
**النطاق:** حماية نقاط نهاية Livewire لصفحة `AcademicFollowUpCenter`

---

## 1. نموذج أمان Livewire في AI School

تعتمد معمارية Livewire على ترقية خصائص المكون (De-serialization & Hydration) وتنفيذ الميثودز المستدعاة من المتصفح.
لقد تم فرض القواعد الصارمة التالية لحماية دورة حياة المكون:
1. **عدم الوثوق بالـ Public Properties:** أي خاصية عامة يمكن للمهاجم التلاعب بها عبر أداة فحص الشبكة (F12 / Burp Suite) مثل `activeStudentId` أو `activeTaskId` أو `activeGroupId`.
2. **التحقق اللحظي قبل القراءة والكتابة (Re-validation on Mutation):** يتم إعادة فحص إذن الوصول فوراً داخل ميثود التنفيذ `saveFollowUpTask`, `saveContactLog`, `saveReassignment` قبل التعامل مع قاعدة البيانات.
3. **منع الـ Hydration للبيانات غير المصرح بها:** في حال فشل التصريح في دوال الفتح `openStudentProfile`, `openFollowUpModal` يتم إسقاط العملية فوراً وتعيين المتغيرات إلى `null` لمنع إرسال أي تفاصيل حساسة في كائن الـ JSON العائد للمتصفح.

---

## 2. جدول نتائج اختبارات الحماية المباشرة للـ Payload

| الإجراء المستهدف | مدخلات التلاعب بالطلب | الحماية المطبقة | السلوك المرصود في استجابة Livewire |
| :--- | :--- | :--- | :--- |
| `openStudentProfile` | إرسال `studentId` لطالب في مجموعة غير مخصصة للموظف | استدعاء `isStudentAuthorized` والتوقف الفوري | لا يتم فتح المودال، `$activeStudentProfile` يساوي `null`، ولا يوجد أي كائن طالب مسرب في شجرة الـ JSON |
| `openFollowUpModal` | إرسال `studentId` و `groupId` خارج نطاق الموظف | فحص `isStudentAuthorized` و `isGroupAuthorized` | يتم إسقاط الطلب، ولا يتم استرجاع أرقام الهاتف أو تفعيل حالة الطالب |
| `saveFollowUpTask` | تعديل الـ Component State وإرسال `activeGroupId` غير مخصص | فحص الصلاحية في بداية الدالة وإطلاق `abort(403)` | رد فوري بـ HTTP 403 Forbidden، وتراجع كامل (No Database Mutation) |
| `openContactLogModal` | إرسال `taskId` لمهمة تابعة لسكرتيرة أخرى في نفس الفرع | استدعاء `isTaskAuthorized` | حجب النافذة، وعدم ملء أي معلومات عن الطالب أو ولي الأمر |
| `saveContactLog` | استدعاء مباشر لحفظ تواصل على `taskId` غير مصرح | فحص `isTaskAuthorized` وإطلاق `abort(403)` | رفض الطلب بـ 403 Forbidden، وعدم إنشاء أي سجل في `student_follow_up_contacts` |
| `openHistoryModal` | إرسال `taskId` للاطلاع على ملاحظات وسجل محاولات تواصل أخرى | فحص `isTaskAuthorized` | `$timelineContacts` تظل مصفوفة فارغة، والنافذة مغلقة |
| `saveReassignment` | استدعاء مباشر لإعادة تكليف مهمة غير مملوكة | فحص `isTaskAuthorized` والتحقق من أهلية الموظف | رفض العملية بـ 403 Forbidden، وبقاء الموظف الأصلي |

---

## 3. حماية تقليل البيانات (Data Minimization in Livewire State)

أحد أهم التحسينات الأمنية التي تم تطبيقها هو إلغاء تمرير كائنات Eloquent غير المقيدة داخل الـ Livewire State:
- **سابقاً:** كانت دالة `getStudentProfile` ترجع:
  - كائن `Student` كامل بكافة علاقاته.
  - مجموعة كائنات `submissions` بكامل أعمدة جدول الواجبات والمدربين.
  - مجموعة كائنات `examAttempts` بكامل أعمدة المحاولات.
  - مجموعة كائنات `tasks` بكامل تاريخ التكليفات.
  مما تسبب في تضخم حجم الـ Livewire Payload وتسريب بيانات إضافية في الـ HTTP Response.
- **حالياً:** تم تحويل كافة المخرجات إلى هياكل بيانات خفيفة ومحددة (Clean DTOs / Plain PHP Arrays):
  - الحضور: مصفوفة مدمجة تحتوي فقط على (`id`, `session_title`, `date`, `group_name`, `course_name`, `status`, `notes`).
  - الواجبات: مصفوفة مدمجة تحتوي فقط على (`id`, `title`, `group_name`, `course_name`, `score`, `max_score`, `status`, `submitted_at`).
  - الامتحانات: مصفوفة مدمجة تحتوي فقط على (`id`, `title`, `group_name`, `course_name`, `score`, `total_score`, `percentage`, `passed`, `date`).
  - الهواتف: محجوبة تلقائياً بـ `— (محجوب)` ما لم يمتلك الموظف الصلاحية المستقلة.

تم التحقق من حزمة الـ JSON الصادرة والتأكد من خلوها تماماً من أي تسريب غير مصرح به.
