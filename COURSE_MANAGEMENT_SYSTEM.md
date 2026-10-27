# نظام إدارة كورسات الطالب - دليل التنفيذ

## 🎯 نظرة عامة

تم تطبيق نظام إدارة كورسات متقدم يسمح للطلاب بتسجيل كورسات واختيار مجموعاتهم مع تتبع تقدمهم عبر 12 حصة (سيشن).

---

## 📊 المكونات الرئيسية

### 1. نموذج EnrollmentCourse
**المسار:** `app/Models/EnrollmentCourse.php`

يتعامل مع كل كورس مفرد داخل التسجيل مع الحقول:
- `status`: حالة الكورس (inactive, active, completed)
- `current_session`: السيشن الحالي (0-12)
- `group_id`: معرف المجموعة المختارة
- `total_sessions`: إجمالي الحصص (عادة 12)

#### الوظائف الرئيسية:
```php
// الحصول على تسمية الحالة بالعربية
$enrollmentCourse->getStatusLabel(); // "غير نشط" / "نشط" / "مكتمل"

// حساب نسبة التقدم
$enrollmentCourse->getProgressPercentage(); // 0-100%

// التحقق من حالة الكورس
$enrollmentCourse->isCompleted();
$enrollmentCourse->isActive();
$enrollmentCourse->isInactive();

// تفعيل الكورس مع اختيار مجموعة
$enrollmentCourse->activateWithGroup($groupId);

// تغيير المجموعة (مع التحقق من التوافقية)
$enrollmentCourse->changeGroup($newGroupId);

// تحديث السيشن تلقائياً
$enrollmentCourse->incrementSession();

// تحويل الكورس إلى مكتمل
$enrollmentCourse->markAsCompleted();
```

---

## 🎨 واجهات المستخدم

### صفحة Student Enrollments
**المسار:** `app/Filament/Student/Pages/StudentEnrollments.php`

صفحة شاملة لعرض جميع تسجيلات الطالب مع:
- إحصائيات التسجيلات والكورسات والمبالغ المالية
- قائمة الكورسات المختارة لكل تسجيل
- عرض حالة كل كورس مع عداد السيشنات
- زر لتفعيل أو تغيير المجموعة

**الملف:** `resources/views/filament/student/pages/student-enrollments.blade.php`

### Livewire Component: CourseStatusButton
**المسار:** `app/Livewire/Student/CourseStatusButton.php`

يعرض زر الحالة مع modal لاختيار/تغيير المجموعة.

**المميزات:**
- عرض الزر بلون مختلف حسب الحالة
- نافذة منبثقة اختيار المجموعات
- فحص التوافقية (لا يمكن الانتقال لمجموعة متقدمة)
- معالجة الأخطاء والإشعارات

---

## 🔔 إدارة السيشنات

### Action: IncrementGroupSessions
**المسار:** `app/Actions/IncrementGroupSessions.php`

عند تسجيل معلم لسيشن جديد:

```php
use App\Actions\IncrementGroupSessions;
use App\Models\Group;

$group = Group::find($groupId);
IncrementGroupSessions::execute($group, 'ملاحظات السيشن');
```

**ما يحدث:**
1. ✅ يتم زيادة `completed_sessions` للمجموعة
2. ✅ جميع الطلاب النشطين يحصلون على تحديث `current_session`
3. ✅ عند الوصول للسيشن 12، الكورس يصير تلقائياً "مكتمل"
4. ✅ يتم إنشاء سجل `AttendanceSession`

### Action في جدول SessionSchedules
**المسار:** `app/Filament/Instructor/Resources/SessionSchedules/Tables/SessionSchedulesTable.php`

تم إضافة action "تسجيل السيشن" يمكن المعلم من:
- تسجيل السيشن مباشرة من الجدول
- تحديث جميع الطلاب النشطين تلقائياً
- تغيير حالة السيشن إلى "مكتمل"

```php
Action::make('record_session')
    ->label('تسجيل السيشن')
    ->icon('heroicon-o-check-circle')
    ->color('primary')
    ->requiresConfirmation()
    ->visible(fn($record) => $record->status === 'scheduled')
    ->action(function ($record) {
        IncrementGroupSessions::execute($record->group);
        $record->update(['status' => 'completed']);
    })
```

---

## 📱 عرض الكورسات في جميع الأماكن

### 1. في صفحة Student Enrollments
يعرض جدول كامل بجميع الكورسات مع:
- اسم الكورس والمعلم
- المجموعة المختارة
- السيشن الحالي مع شريط تقدم
- زر الحالة

### 2. في Dashboard Widget (MyCoursesWidget)
**المسار:** `app/Filament/Student/Widgets/MyCoursesWidget.php`

عرض مختصر من الكورسات النشطة مع:
- أسماء الكورسات
- المجموعات المختارة
- السيشن الحالي

---

## 🗄️ قاعدة البيانات

### جدول enrollment_courses (محدّث)
```sql
ALTER TABLE enrollment_courses ADD (
    status ENUM('inactive', 'active', 'completed') DEFAULT 'inactive',
    current_session INT DEFAULT 0,
    group_id BIGINT UNSIGNED NULLABLE,
    total_sessions INT DEFAULT 12
);
```

### الحقول:
| الحقل | النوع | الوصف |
|------|------|-------|
| id | ID | المعرف الفريد |
| enrollment_id | FK | معرف التسجيل |
| course_id | FK | معرف الكورس |
| course_price | decimal | سعر الكورس |
| status | enum | حالة الكورس |
| current_session | int | السيشن الحالي |
| group_id | FK | معرف المجموعة |
| total_sessions | int | إجمالي الحصص |

---

## 🔄 سير العمل الكامل

### للطالب:
```
1. الإدارة تضيف كورس للطالب
   ↓
2. الكورس يظهر بحالة "غير نشط"
   ↓
3. الطالب يضغط الزر → اختيار مجموعة
   ↓
4. الحالة تتحول إلى "نشط"
   ↓
5. المعلم يسجل كل سيشن (1-12)
   ↓
6. عند السيشن 12 → الحالة تصير "مكتمل"
   ↓
7. الكورس ينتقل للسجل النهائي
```

### للمعلم:
```
1. قبل بدء الحصة، يسجل السيشن في SessionSchedules
   ↓
2. يضغط "تسجيل السيشن"
   ↓
3. جميع الطلاب النشطين يحصلون على التحديث
   ↓
4. عند الحصة 12 → الكورسات تصير مكتملة
```

---

## 🚀 كيفية الاستخدام

### 1. في Admin Panel
```php
// إضافة كورس للطالب
$enrollment = Enrollment::find($enrollmentId);
$enrollment->enrollmentCourses()->create([
    'course_id' => $courseId,
    'course_price' => $price,
    'status' => 'inactive',
    'current_session' => 0,
    'total_sessions' => 12,
]);
```

### 2. في Student Panel
يمكن الطالب الوصول إلى:
- صفحة "تسجيلاتي" - عرض كامل الكورسات
- الضغط على الزر لاختيار/تغيير المجموعة
- رؤية السيشن الحالي والتقدم

### 3. في Instructor Panel
- عرض جدول SessionSchedules
- الضغط على "تسجيل السيشن"
- تأكيد التسجيل
- تحديث تلقائي لجميع الطلاب

---

## ⚠️ الشروط والتوافقيات

### عند تفعيل كورس:
- ✅ الطالب اختار مجموعة
- ✅ الحالة تصير "نشط"
- ✅ يظهر اسم المجموعة والمعلم

### عند تغيير المجموعة:
- ✅ فقط إذا كان الكورس "نشط" أو "غير نشط"
- ✅ المجموعة الجديدة يجب أن تكون في نفس السيشن أو أقل
- ❌ لا يمكن تغيير كورس "مكتمل"

### عند تسجيل السيشن:
- ✅ المجموعة تزيد `completed_sessions`
- ✅ جميع الطلاب النشطين يحصلون على التحديث
- ✅ عند السيشن 12: تحويل تلقائي إلى "مكتمل"

---

## 📝 الملفات المضافة/المعدلة

### ملفات جديدة:
- `app/Models/EnrollmentCourse.php` - نموذج الكورس المفرد
- `app/Actions/IncrementGroupSessions.php` - حركة تحديث السيشنات
- `app/Filament/Student/Pages/StudentEnrollments.php` - صفحة الكورسات
- `app/Livewire/Student/CourseStatusButton.php` - زر الحالة
- `app/Livewire/Student/EnrollmentCoursesManager.php` - مدير الكورسات
- `database/migrations/2026_01_10_000001_add_status_to_enrollment_courses.php` - Migration
- `resources/views/filament/student/pages/student-enrollments.blade.php` - View
- `resources/views/livewire/student/course-status-button.blade.php` - View
- `resources/views/livewire/student/enrollment-courses-manager.blade.php` - View

### ملفات معدلة:
- `app/Models/Enrollment.php` - إضافة علاقة `enrollmentCourses()`
- `app/Filament/Student/Widgets/MyCoursesWidget.php` - تحديث الـ widget
- `app/Filament/Instructor/Resources/SessionSchedules/Tables/SessionSchedulesTable.php` - إضافة action

---

## 🧪 الاختبار

تم إنشاء أمر اختبار:
```bash
php artisan test:course-system
```

يختبر:
- ✓ وجود نموذج EnrollmentCourse
- ✓ العلاقات بين الـ models
- ✓ الوظائف الأساسية

---

## 📞 ملاحظات مهمة

1. **الـ Migration:** تم تشغيلها بنجاح وإضافة جميع الحقول
2. **التوافقية:** النظام متوافق مع Filament و Livewire
3. **الأداء:** يتم eager loading لجميع العلاقات لتجنب N+1 queries
4. **الأمان:** فحص التحقق من الملكية والصلاحيات

---

## 🎓 الميزات الرئيسية

✅ **3 حالات للكورس:** غير نشط ↔ نشط ↔ مكتمل
✅ **عداد السيشنات:** يظهر في جميع الأماكن (0-12)
✅ **اختيار مجموعة ذكي:** يتحقق من التوافقية
✅ **تحديث تلقائي:** عند تسجيل معلم سيشن
✅ **واجهة سهلة:** زر واحد لتفعيل/تغيير المجموعة
✅ **سجل دائم:** الكورسات المكتملة محفوظة دائماً

---

**التاريخ:** 10 يناير 2026
**الإصدار:** 1.0
**الحالة:** ✅ مكتمل وجاهز للاستخدام
