# نظام تتبع جلسات الحضور التلقائي

## نظرة عامة
تم تطبيق نظام تلقائي لتتبع جلسات الحضور وتحديث حالة الكورسات بناءً على عدد الجلسات المكتملة.

## كيفية العمل

### 1. تسجيل جلسة حضور جديدة
عندما يقوم المدرس بإنشاء جلسة حضور جديدة (`AttendanceSession`):

- يتم تسجيل الجلسة على مستوى المجموعة (`group_id`)
- يتم تسجيل الجلسة على مستوى الكورس (`course_id`)
- يتم حفظ تاريخ الجلسة وملاحظاتها

### 2. التحديث التلقائي للكورسات
عند إنشاء جلسة حضور، يقوم النظام تلقائياً بـ:

1. **البحث عن جميع التسجيلات النشطة** (`EnrollmentCourse`) للطلاب في هذه المجموعة والكورس
2. **زيادة عداد الجلسات** (`current_session`) لكل تسجيل بمقدار 1
3. **التحقق من الاكتمال**: إذا وصل `current_session` إلى `total_sessions`
4. **تحديث الحالة تلقائياً**: تحويل حالة الكورس من `active` إلى `completed`

### 3. التراجع عن الجلسة (Soft Delete)
عند حذف جلسة حضور:

- يتم تقليل عداد الجلسات (`current_session`) لجميع الطلاب المتأثرين
- إذا كان الكورس مكتملاً (`completed`) ولكن العداد أصبح أقل من الإجمالي، يتم إرجاع الحالة إلى `active`

## الملفات المعنية

### 1. Observer الجديد
**الملف:** `app/Observers/AttendanceSessionObserver.php`

```php
// عند إنشاء جلسة
public function created(AttendanceSession $session)
{
    // زيادة current_session لكل طالب في المجموعة
    // والتحقق من اكتمال الكورس
}

// عند حذف جلسة
public function deleted(AttendanceSession $session)
{
    // تقليل current_session
    // وإرجاع الحالة إلى active إذا لزم الأمر
}
```

### 2. نموذج EnrollmentCourse
**الملف:** `app/Models/EnrollmentCourse.php`

**الطرق المهمة:**

```php
// زيادة الجلسة والتحقق من الاكتمال
public function incrementSession(): void

// التحقق من جاهزية الاكتمال
public function shouldBeCompleted(): bool

// وضع علامة مكتمل
public function markAsCompleted(): void
```

### 3. نموذج AttendanceSession
**الملف:** `app/Models/AttendanceSession.php`

الحقول المهمة:
- `group_id`: رقم المجموعة
- `course_id`: رقم الكورس
- `attendance_date`: تاريخ الجلسة
- `recorded_by`: المدرس الذي سجل الجلسة

## حالات الكورس

### inactive (غير نشط)
- الطالب مسجل في الكورس لكن لم يتم تفعيله بعد
- لم يتم تعيين مجموعة

### active (نشط)
- الكورس قيد الدراسة
- `current_session < total_sessions`
- تم تعيين المجموعة

### completed (مكتمل)
- اكتمل عدد الجلسات المطلوبة
- `current_session >= total_sessions`
- يتم تحويله تلقائياً عند الوصول للعدد المطلوب

## مثال على التدفق

### المثال 1: كورس بـ 10 جلسات

```
الحالة الأولية:
- total_sessions: 10
- current_session: 0
- status: active

بعد 5 جلسات:
- current_session: 5
- status: active (50%)

بعد 10 جلسات:
- current_session: 10
- status: completed ✓ (تحول تلقائياً)
```

### المثال 2: حذف جلسة

```
قبل الحذف:
- current_session: 10
- status: completed

بعد حذف جلسة:
- current_session: 9
- status: active (تم الإرجاع تلقائياً)
```

## السجلات والتتبع

يقوم النظام بتسجيل جميع العمليات في logs:

```php
\Log::info('Session incremented for enrollment course', [
    'enrollment_id' => ...,
    'course_id' => ...,
    'current_session' => ...,
    'total_sessions' => ...,
    'status' => ...,
]);
```

يمكن مراجعة السجلات في: `storage/logs/laravel.log`

## تفعيل النظام

النظام مفعّل تلقائياً عبر:
- `app/Providers/AppServiceProvider.php`
- تسجيل Observer في `boot()` method

## الفوائد

1. ✅ **تلقائي بالكامل**: لا يحتاج المدرس لتحديث حالة الكورس يدوياً
2. ✅ **دقيق**: يتتبع كل جلسة حضور بشكل دقيق
3. ✅ **متسق**: نفس المنطق يطبق على جميع الطلاب
4. ✅ **قابل للتتبع**: جميع العمليات مسجلة في logs
5. ✅ **قابل للتراجع**: حذف الجلسة يعيد الحالة السابقة

## ملاحظات مهمة

⚠️ **تنبيهات:**
- فقط الجلسات المرتبطة بـ `group_id` و `course_id` تؤثر على العدادات
- فقط الكورسات بحالة `active` يتم تحديثها عند إضافة جلسة جديدة
- حذف جلسة يؤثر على جميع الكورسات (حتى المكتملة) لإرجاع الحالة الصحيحة

## الاختبار

لاختبار النظام:

1. أنشئ جلسة حضور لمجموعة معينة
2. تحقق من زيادة `current_session` في جدول `enrollment_courses`
3. عند الوصول للعدد الكلي، تحقق من تغيير `status` إلى `completed`
4. احذف جلسة وتحقق من تقليل العداد

```sql
-- التحقق من حالة الكورسات بعد إضافة جلسة
SELECT 
    ec.id,
    ec.enrollment_id,
    ec.course_id,
    ec.current_session,
    ec.total_sessions,
    ec.status,
    (ec.current_session * 100.0 / ec.total_sessions) as progress_percentage
FROM enrollment_courses ec
WHERE ec.group_id = [GROUP_ID] AND ec.course_id = [COURSE_ID];
```

## الدعم والصيانة

في حالة وجود مشاكل:
1. تحقق من logs: `storage/logs/laravel.log`
2. تأكد من أن `group_id` و `course_id` محددان في جلسة الحضور
3. تأكد من أن الطلاب مسجلين في نفس المجموعة والكورس
4. تحقق من أن حالة الكورس `active` قبل إضافة الجلسة

## التطوير المستقبلي

يمكن تطوير النظام لـ:
- [ ] إشعارات تلقائية عند اكتمال الكورس
- [ ] تقارير تفصيلية عن تقدم الطلاب
- [ ] منع حذف الجلسات بعد فترة معينة
- [ ] إضافة مراجعة إدارية قبل وضع علامة مكتمل
