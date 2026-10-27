# نظام Calendar View - تقويم الأحداث

## 📅 نظرة عامة

تم إنشاء نظام Calendar View متكامل باستخدام **FullCalendar.js** لعرض جميع الأنشطة والفعاليات بشكل تقويمي تفاعلي في جميع لوحات التحكم.

---

## 🎯 المميزات

### ✨ FullCalendar Integration
- **العرض التفاعلي**: تقويم كامل مع دعم اللغة العربية (RTL)
- **طرق عرض متعددة**: شهري، أسبوعي، يومي، قائمة
- **التنقل السلس**: أزرار السابق/التالي/اليوم
- **الألوان المميزة**: كل نوع حدث له لون خاص
- **التفاصيل عند الضغط**: عرض معلومات الحدث في نافذة منبثقة

### 🎨 أنواع الأحداث

#### 📊 الجلسات (Sessions)
- **اللون**: أخضر `#10b981`
- **الأيقونة**: 📊
- **البيانات**: المجموعة، الوقت، الحالة، المدرس

#### 📝 الامتحانات (Exams)  
- **اللون**: أحمر `#ef4444`
- **الأيقونة**: 📝
- **البيانات**: العنوان، المدة، الدرجة الكاملة، التوقيت

#### 📚 الواجبات (Assignments)
- **اللون**: برتقالي `#f59e0b`
- **الأيقونة**: 📚
- **البيانات**: العنوان، الموعد النهائي، الدرجة الكاملة

---

## 📂 الهيكل التقني

### 1️⃣ Instructor Calendar
**الموقع**: `app/Filament/Instructor/Widgets/InstructorCalendarWidget.php`

**يعرض**:
- جلسات المدرس (من جدوله)
- امتحانات مجموعاته
- واجبات مجموعاته

**المميزات**:
- 4 طرق عرض (شهر، أسبوع، يوم، قائمة)
- تصفية تلقائية للمدرس الحالي
- عرض 3 أشهر (شهر سابق + الحالي + شهرين قادمين)

---

### 2️⃣ Student Calendar
**الموقع**: `app/Filament/Student/Widgets/StudentCalendarWidget.php`

**يعرض**:
- جلسات الطالب مع حالة الحضور:
  - ✅ حاضر (أخضر)
  - ❌ غائب (أحمر)  
  - ⏰ متأخر (برتقالي)
  - 📊 لم يتم التسجيل (أزرق)
- امتحانات الطالب مع حالة المحاولة:
  - ✅ مصحح
  - 📝 لم يبدأ
- واجبات الطالب مع حالة التسليم:
  - ✅ مصحح (أخضر)
  - ⏳ مسلم (أزرق)
  - 📚 لم يُسلم (برتقالي)
  - ❌ متأخر (أحمر)

**المميزات**:
- أيقونات ديناميكية حسب الحالة
- ألوان تعبر عن الأداء
- تفاصيل الدرجات عند الضغط

---

### 3️⃣ Parent Calendar  
**الموقع**: `app/Filament/Parent/Widgets/ParentCalendarWidget.php`

**يعرض**:
- جميع أحداث الأبناء في تقويم واحد
- كل حدث يحمل اسم الابن في العنوان
- نفس أيقونات وألوان calendar الطالب

**المميزات**:
- عرض متعدد الأبناء
- تصفية تلقائية لأبناء ولي الأمر
- معلومات تفصيلية لكل ابن

---

### 4️⃣ Admin Calendar
**الموقع**: `app/Filament/Admin/Widgets/AdminCalendarWidget.php`

**يعرض**:
- **Super Admin**: كل الأحداث في جميع الفروع
- **Branch Manager**: أحداث فرعه فقط

**المميزات**:
- نظرة شاملة على النظام
- 4 طرق عرض (شهر، أسبوع، يوم، قائمة شهرية)
- معلومات الفرع في التفاصيل

---

### 5️⃣ Supervisor Calendar
**الموقع**: `app/Filament/Supervisor/Widgets/SupervisorCalendarWidget.php`

**يعرض**:
- أحداث المجموعات التي يشرف عليها فقط
- جلسات، امتحانات، واجبات

**المميزات**:
- تصفية تلقائية للمجموعات
- معلومات المدرس والمجموعة
- 3 طرق عرض (شهر، أسبوع، قائمة)

---

## 🎨 Blade Views

جميع الـ Views في:
- `resources/views/filament/instructor/widgets/calendar-widget.blade.php`
- `resources/views/filament/student/widgets/calendar-widget.blade.php`
- `resources/views/filament/parent/widgets/calendar-widget.blade.php`
- `resources/views/filament/admin/widgets/calendar-widget.blade.php`
- `resources/views/filament/supervisor/widgets/calendar-widget.blade.php`

### 🎨 التخصيصات

**ألوان الأزرار لكل Panel**:
- **Instructor**: أزرق `#6366f1`
- **Student**: أزرق `#6366f1`
- **Parent**: بنفسجي `#8b5cf6`
- **Admin**: أحمر `#dc2626`
- **Supervisor**: سماوي `#0891b2`

**مميزات CSS**:
- التصميم الإسلامي (RTL)
- اليوم الحالي مميز بلون أصفر فاتح
- التقويم responsive كامل
- أزرار hover effect

---

## 📊 البيانات المعروضة

### Session Event
```php
[
    'title' => '📊 جلسة: اسم المجموعة',
    'start' => '2025-12-28 10:00:00',
    'backgroundColor' => '#10b981',
    'extendedProps' => [
        'type' => 'session',
        'group' => 'المجموعة',
        'instructor' => 'المدرس',
        'status' => 'completed/pending',
    ]
]
```

### Exam Event
```php
[
    'title' => '📝 امتحان: عنوان الامتحان',
    'start' => '2025-12-30 14:00:00',
    'end' => '2025-12-30 16:00:00',
    'backgroundColor' => '#ef4444',
    'extendedProps' => [
        'type' => 'exam',
        'duration' => 120,
        'total_marks' => 100,
        'score' => 85, // للطالب فقط
    ]
]
```

### Assignment Event
```php
[
    'title' => '📚 واجب: عنوان الواجب',
    'start' => '2025-12-31',
    'allDay' => true,
    'backgroundColor' => '#f59e0b',
    'extendedProps' => [
        'type' => 'assignment',
        'max_score' => 50,
        'score' => 45, // للطالب فقط
        'status' => 'submitted/graded/missing',
    ]
]
```

---

## 🔧 FullCalendar Configuration

### Localization
```javascript
locale: 'ar',
direction: 'rtl',
buttonText: {
    today: 'اليوم',
    month: 'شهر',
    week: 'أسبوع',
    day: 'يوم',
    list: 'قائمة'
}
```

### Header Toolbar
```javascript
headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
}
```

### Event Interaction
```javascript
eventClick: function(info) {
    // عرض تفاصيل الحدث
    alert(event.title + '\n\n' + details);
}
```

---

## 📈 إحصائيات

### ✅ Widgets المنشأة
- 5 Calendar Widgets (واحد لكل Panel)
- 5 Blade Views مخصصة
- **الحجم الإجمالي**: ~2,000 سطر كود

### 📦 External Library
- **FullCalendar**: v6.1.10
- **Arabic Locale**: مدمج
- **CDN**: jsdelivr

### ⚡ الأداء
- **Lazy Loading**: يتم تحميل التقويم عند فتح الصفحة
- **Date Range**: 3 أشهر فقط (تقليل الاستعلامات)
- **wire:ignore**: منع Livewire من إعادة تحميل التقويم

---

## 🎯 حالات الاستخدام

### 👨‍🏫 المدرس
1. يفتح لوحته → يرى Calendar Widget في الأعلى
2. يرى جلساته القادمة، امتحاناته، وواجباته
3. يضغط على حدث → يرى التفاصيل
4. يتنقل بين الأشهر لرؤية الجدول المستقبلي

### 👨‍🎓 الطالب
1. يفتح لوحته → يرى جدوله الدراسي
2. يرى أيقونات ملونة حسب حالة كل حدث:
   - ✅ مكتمل ومُسلم
   - ⏳ قيد الانتظار
   - ❌ متأخر أو غائب
3. يضغط على امتحان → يرى درجته إذا صُححت
4. يضغط على واجب → يرى حالة التسليم

### 👨‍👩‍👦 ولي الأمر
1. يفتح لوحته → يرى تقويم شامل لكل أبنائه
2. كل حدث يحمل اسم الابن
3. يتابع الحضور والغياب
4. يتابع الدرجات والواجبات

### 👨‍💼 الإداري
1. Super Admin: يرى كل الأحداث في جميع الفروع
2. Branch Manager: يرى أحداث فرعه فقط
3. يرى اسم الفرع في تفاصيل كل حدث
4. نظرة شاملة لتخطيط الأنشطة

### 👁️ المشرف
1. يرى أحداث المجموعات التي يشرف عليها
2. يتابع الجلسات والامتحانات
3. يراقب الواجبات والمواعيد النهائية

---

## 🚀 التحسينات المستقبلية

### 🔔 اقتراحات
1. **Drag & Drop**: تغيير مواعيد الأحداث بالسحب
2. **Quick Actions**: إنشاء حدث جديد من التقويم مباشرة
3. **Filters**: تصفية حسب نوع الحدث (جلسات فقط، امتحانات فقط)
4. **Export**: تصدير التقويم إلى PDF أو iCal
5. **Reminders**: ربط مع نظام الإشعارات لإرسال تذكيرات
6. **Print View**: طباعة التقويم الشهري
7. **Color Customization**: السماح للمستخدمين باختيار الألوان
8. **Sync**: مزامنة مع Google Calendar أو Outlook

---

## 📋 الخلاصة

✅ تم إنشاء **نظام Calendar View متكامل** في 5 Panels  
✅ استخدام **FullCalendar.js v6** مع دعم العربية  
✅ عرض **3 أنواع أحداث**: جلسات، امتحانات، واجبات  
✅ **أيقونات وألوان ديناميكية** حسب الحالة  
✅ **تفاعلي بالكامل** مع event click handlers  
✅ **responsive** ويدعم RTL  

**النظام جاهز للاستخدام الفوري!** 🎉
