# 🗺️ دليل الوصول - أين تجد كل شيء

## 📚 التوثيق والمستندات

### ملفات التوثيق الرئيسية:
- 📄 **[COURSE_MANAGEMENT_SYSTEM.md](COURSE_MANAGEMENT_SYSTEM.md)** - التوثيق الشامل (300 سطر)
- 📄 **[COURSE_SYSTEM_COMPLETE.md](COURSE_SYSTEM_COMPLETE.md)** - ملخص الإنجازات
- 📄 **[ENROLLMENTS_MULTIPLE_GROUPS.md](ENROLLMENTS_MULTIPLE_GROUPS.md)** - نظام المجموعات المتعددة

---

## 💻 الوصول للنظام في المتصفح

### للطالب:
1. سجل دخول من لوحة الطالب: `/student`
2. في القائمة الجانبية أو العلوية، ابحث عن: **"تسجيلاتي"** 📋
3. الرابط المباشر: `/student/student-enrollments`

**ما سيظهر لك:**
- 📊 إحصائيات شاملة (عدد التسجيلات، الكورسات، المبالغ)
- 📝 قائمة بجميع تسجيلاتك
- 🎓 الكورسات المختارة في كل تسجيل
- 🔘 زر الحالة لكل كورس (غير نشط / نشط / مكتمل)
- 📈 عداد السيشنات (0-12) مع شريط تقدم
- ⚙️ إمكانية اختيار أو تغيير المجموعة

### للمعلم:
1. سجل دخول من لوحة المعلم: `/instructor`
2. افتح: **"جداول السيشنات"** (SessionSchedules)
3. قبل الحصة، اضغط: **"تسجيل السيشن"**
4. سيتم تحديث جميع الطلاب النشطين تلقائياً

### للإدارة:
1. سجل دخول من لوحة الإدارة: `/admin`
2. يمكن الوصول لكل الموارد والتقارير

---

## 📁 البنية البرمجية

### النماذج (Models):
```
app/Models/EnrollmentCourse.php
├── الحقول الرئيسية:
│   ├── status (inactive/active/completed)
│   ├── current_session (0-12)
│   ├── group_id (المجموعة المختارة)
│   └── total_sessions (عادة 12)
├── 11 وظيفة للإدارة
└── 3 علاقات (Relations)
```

### الصفحات (Pages):
```
app/Filament/Student/Pages/StudentEnrollments.php
├── واجهة شاملة للطالب
├── عرض الإحصائيات
├── جدول التسجيلات
└── الكورسات مع الحالات
```

### المكونات (Components):
```
app/Livewire/Student/
├── CourseStatusButton.php (زر الحالة)
├── EnrollmentCoursesManager.php (إدارة الكورسات)
└── Views في: resources/views/livewire/student/
```

### الإجراءات (Actions):
```
app/Actions/
└── IncrementGroupSessions.php
    ├── تحديث السيشنات تلقائياً
    ├── زيادة completed_sessions للمجموعة
    └── تحويل الكورس لـ"مكتمل" عند 12/12
```

---

## 🗄️ قاعدة البيانات

### الجداول المعدلة:
- `enrollment_courses` - تمت إضافة حقول الحالة والسيشنات
- `groups` - يحتوي على `completed_sessions`
- `attendance_sessions` - سجل السيشنات

### Migration:
```bash
database/migrations/2026_01_10_add_status_to_enrollment_courses_table.php
```

**لتشغيلها:**
```bash
php artisan migrate
```

---

## 🧪 الاختبار

### أوامر الاختبار:
```bash
# اختبار النموذج
php artisan test:course-system

# عرض بيانات الطالب
php artisan test:student-data

# اختبار التسجيلات
php artisan test:enrollment-courses
```

---

## 🔧 ملفات العرض (Views)

### Blade Templates:
```
resources/views/
├── filament/student/pages/
│   └── student-enrollments.blade.php (الصفحة الرئيسية)
├── livewire/student/
│   ├── course-status-button.blade.php (زر الحالة)
│   └── enrollment-courses-manager.blade.php (مدير الكورسات)
└── filament/student/layouts/
    └── student-page-styles.blade.php (الأنماط)
```

---

## 📊 سير العمل الكامل

### 1. الطالب يسجل في كورسات:
```
التسجيل → الكورس يصير بحالة "غير نشط" → العداد: 0/12
```

### 2. الطالب يفعّل الكورس:
```
يفتح "تسجيلاتي" → يضغط الزر "تفعيل" → يختار مجموعة
→ الحالة تصير "نشط" → العداد يظهر
```

### 3. المعلم يسجل السيشنات:
```
يفتح SessionSchedules → "تسجيل السيشن"
→ جميع الطلاب النشطين يتحدثون تلقائياً
→ العداد يزيد: 1/12, 2/12, ... 12/12
```

### 4. إكمال الكورس:
```
عند الوصول لـ 12/12 → الحالة تصير "مكتمل" تلقائياً
→ الطالب لا يستطيع التعديل → الكورس منتهي ✅
```

---

## 🛠️ أوامر مفيدة

### تنظيف الكاش:
```bash
php artisan optimize:clear
php artisan filament:optimize-clear
```

### إعادة بناء النظام:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### عرض الروابط المتاحة:
```bash
php artisan route:list | Select-String -Pattern "student"
```

---

## 🎯 الميزات الرئيسية

### ✅ ما تم إنجازه:
- [x] نموذج EnrollmentCourse مع 11 وظيفة
- [x] صفحة StudentEnrollments شاملة
- [x] نظام 3-حالات (غير نشط → نشط → مكتمل)
- [x] عداد السيشنات (0-12) مع شريط تقدم
- [x] اختيار المجموعة الذكي
- [x] تحديث تلقائي عند تسجيل السيشن
- [x] فحص التوافقية للمجموعات
- [x] معالجة الأخطاء والإشعارات
- [x] التوثيق الشامل

### 📦 الملفات المنجزة:
- **19 ملف إجمالي**:
  - 1 Model (EnrollmentCourse)
  - 1 Migration
  - 1 Page (StudentEnrollments)
  - 2 Livewire Components
  - 1 Action
  - 3 Views/Templates
  - 1 Test Command
  - 3 ملفات معدلة
  - 6 ملفات توثيق

---

## 🎨 الواجهة

### الألوان حسب الحالة:
- 🟡 **غير نشط**: أصفر (لم يبدأ الطالب بعد)
- 🟢 **نشط**: أخضر (الطالب يحضر السيشنات)
- ⚪ **مكتمل**: رمادي (انتهى الكورس)

### الأيقونات:
- 📖 الكورس
- 👨‍🏫 المعلم
- 👥 المجموعة
- ⏱️ السيشن
- 📊 شريط التقدم
- 💰 السعر

---

## 📞 دعم إضافي

### إذا كانت الصفحة لا تظهر:
1. نظف الكاش: `php artisan optimize:clear`
2. أعد تشغيل الـ server
3. تحقق من الصلاحيات
4. تأكد من وجود بيانات طالب في الحساب

### إذا كانت البيانات لا تظهر:
1. تحقق من وجود تسجيلات نشطة
2. تأكد من وجود كورسات مرتبطة
3. فحص الـ migration: `php artisan migrate:status`

---

## 🚀 الوصول السريع

| المستخدم | الرابط | الصفحة |
|---------|--------|--------|
| الطالب | `/student/student-enrollments` | تسجيلاتي |
| المعلم | `/instructor/session-schedules` | جداول السيشنات |
| الإدارة | `/admin` | لوحة الإدارة |

---

✨ **النظام جاهز ويعمل بكفاءة!**
