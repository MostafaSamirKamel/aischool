# Sprint 2 - نظام الكورسات والمسارات التعليمية ✅

## الإنجازات المكتملة

### 1. قاعدة البيانات
- ✅ إنشاء جدول `tracks` (المسارات التعليمية)
- ✅ إنشاء جدول `courses` (الكورسات)
- ✅ إنشاء جدول `classrooms` (القاعات الدراسية)
- ✅ إنشاء جدول `groups` (المجموعات)
- ✅ إضافة علاقات Foreign Keys بين الجداول
- ✅ تطبيق Multi-tenancy على كل الجداول

### 2. النماذج (Models)
تم إنشاء وتجهيز 4 نماذج بالكامل:

#### Track Model
```php
- BelongsToBranch trait ✅
- Relationships: courses(), activeCourses()
- Fillable fields: tenant_id, name, description, order, status, settings
- Casts: order => integer, status => string, settings => array
```

#### Course Model
```php
- BelongsToBranch trait ✅
- Relationships: track(), groups(), activeGroups()
- Fillable fields: tenant_id, track_id, name, description, duration_months, 
                   sessions_count, price, prerequisites, order, status
- Casts: duration_months, sessions_count => integer, price => decimal, 
         prerequisites => array
- Helper method: hasPrerequisites()
```

#### Classroom Model
```php
- BelongsToBranch trait ✅
- Relationships: groups(), activeGroups()
- Fillable fields: tenant_id, name, capacity, location, description, status, equipment
- Casts: capacity => integer, equipment => array
- Helper method: isAvailable()
```

#### Group Model
```php
- BelongsToBranch trait ✅
- Relationships: course(), classroom(), instructor(), supervisor()
- Fillable fields: tenant_id, course_id, classroom_id, instructor_id, supervisor_id,
                   name, capacity, max_students, schedule, start_date, end_date, 
                   status, notes
- Casts: capacity, max_students => integer, schedule => array, 
         start_date, end_date => date
- Helper methods: isFull(), isActive()
```

### 3. موارد Filament (Resources)
تم إنشاء 4 موارد كاملة في Branch Panel:

#### TrackResource (`/branch/tracks`)
**النموذج (Form):**
- اسم المسار (مطلوب)
- الوصف
- الترتيب (افتراضي: 0)
- الحالة: نشط / غير نشط

**الجدول (Table):**
- اسم المسار (قابل للبحث والترتيب)
- الوصف (محدود 50 حرف)
- عدد الكورسات (badge مع count)
- الترتيب
- الحالة (badge ملون)
- تاريخ الإنشاء

**المميزات:**
- تصنيف افتراضي حسب الترتيب
- فلتر للمحذوفة
- عمليات Soft Delete & Restore
- التسميات بالعربية بالكامل

#### CourseResource (`/branch/courses`)
**النموذج (Form):**
- **قسم المعلومات الأساسية:**
  - المسار التعليمي (Select مع بحث)
  - اسم الكورس (مطلوب)
  - الوصف
  
- **قسم تفاصيل الكورس:**
  - المدة بالشهور
  - عدد الجلسات
  - السعر (بالجنيه المصري)
  - الترتيب
  - الحالة: مسودة / نشط / مؤرشف
  
- **قسم المتطلبات:**
  - Repeater للمتطلبات السابقة

**الجدول (Table):**
- المسار التعليمي
- اسم الكورس
- المدة (مع "شهر")
- عدد الجلسات
- السعر (بالجنيه المصري)
- عدد المجموعات (badge)
- الحالة (badge ملون)

**المميزات:**
- فلتر حسب المسار التعليمي
- فلتر حسب الحالة
- تصنيف افتراضي حسب المسار ثم الترتيب

#### ClassroomResource (`/branch/classrooms`)
**النموذج (Form):**
- اسم القاعة (مطلوب)
- السعة (1-100)
- الموقع
- الحالة: نشطة / غير نشطة / صيانة
- الوصف
- المعدات (JSON)

**الجدول (Table):**
- اسم القاعة
- السعة (مع "طالب")
- الموقع
- المجموعات النشطة (badge مع count)
- الحالة (badge ملون)

**المميزات:**
- فلتر حسب الحالة
- تصنيف افتراضي حسب الاسم

#### GroupResource (`/branch/groups`)
**النموذج (Form):**
- **قسم المعلومات الأساسية:**
  - الكورس (Select مع بحث)
  - اسم المجموعة
  
- **قسم الموارد:**
  - القاعة
  - المدرس (من المدرسين ومدراء الفروع)
  - المشرف (من السكرتارية ومدراء الفروع)
  
- **قسم السعة والتواريخ:**
  - العدد الحالي
  - الحد الأقصى
  - تاريخ البدء (مطلوب)
  - تاريخ الانتهاء
  - الحالة: قيد الانتظار / نشطة / مكتملة / ملغاة
  
- **قسم الجدول الزمني:**
  - Repeater للأيام والأوقات (اليوم، وقت البدء، وقت الانتهاء)
  
- **قسم الملاحظات:**
  - ملاحظات (منطوي افتراضياً)

**الجدول (Table):**
- الكورس
- اسم المجموعة
- المدرس
- القاعة
- السعة (badge: "15 / 20" مع لون حسب الامتلاء)
- تاريخ البدء
- تاريخ الانتهاء
- الحالة (badge ملون)

**المميزات:**
- فلتر حسب الكورس
- فلتر حسب المدرس
- فلتر حسب الحالة
- تصنيف افتراضي حسب تاريخ البدء (الأحدث أولاً)

### 4. البيانات التجريبية (Seeder)
تم إنشاء `CoursesSeeder` الذي ينشئ لكل فرع:

#### 3 مسارات تعليمية:
1. **تطوير المواقع**
   - HTML & CSS (2 شهر، 16 جلسة، 1500 ج.م)
   - JavaScript (3 شهور، 24 جلسة، 2000 ج.م)
   - React JS (4 شهور، 32 جلسة، 3000 ج.م)
   - Laravel (4 شهور، 32 جلسة، 3500 ج.م)

2. **تطوير تطبيقات الموبايل**
   - Flutter (5 شهور، 40 جلسة، 4000 ج.م)
   - React Native (5 شهور، 40 جلسة، 4000 ج.م)

3. **علوم البيانات والذكاء الاصطناعي**
   - Python للبيانات (3 شهور، 24 جلسة، 2500 ج.م)
   - Machine Learning (4 شهور، 32 جلسة، 3500 ج.م)

#### 5 قاعات دراسية:
- سعة متغيرة (15-30 طالب)
- مواقع مختلفة (الطابق الأول والثاني)
- معدات: بروجيكتور، سبورة ذكية، أجهزة كمبيوتر

#### مجموعات دراسية:
- 1-2 مجموعة لكل كورس رئيسي
- جدول زمني: السبت والاثنين 10 صباحاً - 12 ظهراً
- حالة تلقائية حسب تاريخ البدء

### 5. المميزات التقنية
- ✅ Multi-tenancy: كل البيانات معزولة حسب الفرع
- ✅ BranchScope: الفلترة التلقائية حسب الفرع
- ✅ Soft Deletes: حذف آمن لكل النماذج
- ✅ Arabic UI: واجهة عربية بالكامل
- ✅ Relationships: علاقات صحيحة بين كل النماذج
- ✅ Validation: قواعد التحقق على النماذج
- ✅ Badges & Colors: عرض مرئي للحالات
- ✅ Search & Filter: بحث وفلترة متقدمة

## التسلسل الهرمي للبيانات

```
Branch (Tenant)
├── Tracks (المسارات التعليمية)
│   └── Courses (الكورسات)
│       └── Groups (المجموعات)
│           ├── Classroom (القاعة)
│           ├── Instructor (المدرس)
│           └── Supervisor (المشرف)
└── Classrooms (القاعات)
```

## الخطوات التالية (Sprint 3)

### 1. نظام الطلاب والتسجيل
- [ ] Student Model + Migration
- [ ] Enrollment Model (التسجيل في المجموعات)
- [ ] StudentResource في Branch Panel
- [ ] EnrollmentResource
- [ ] نموذج طلب التسجيل
- [ ] موافقة/رفض التسجيل
- [ ] دفع الرسوم

### 2. نظام الحضور والغياب
- [ ] Attendance Model + Migration
- [ ] QR Code للحضور
- [ ] AttendanceResource
- [ ] تقارير الحضور للمدرس
- [ ] تقارير الحضور للطالب

### 3. نظام الدفع والمالية
- [ ] Payment Model + Migration
- [ ] Installment Plan (خطط الأقساط)
- [ ] PaymentResource
- [ ] Invoice Generation (إنشاء فواتير)
- [ ] تقارير المالية

## أوامر مفيدة

```bash
# تشغيل السيرفر
php artisan serve

# الوصول للوحات التحكم
http://localhost:8000/owner    # لوحة المالك
http://localhost:8000/branch   # لوحة الفرع

# إعادة إنشاء البيانات
php artisan migrate:fresh --seed

# مسح الكاش
php artisan cache:clear
php artisan config:clear
```

## ملاحظات مهمة
1. كل الموارد تطبق Multi-tenancy تلقائياً
2. المالك (Owner) يرى بيانات كل الفروع
3. مدير الفرع يرى بيانات فرعه فقط
4. الواجهة بالكامل بالعربية
5. البيانات التجريبية متوفرة لكل الفروع الأربعة

---
**تاريخ الإكمال:** 2025-12-17  
**الحالة:** ✅ مكتمل 100%
