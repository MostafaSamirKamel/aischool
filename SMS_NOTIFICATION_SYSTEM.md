# نظام الرسائل النصية (SMS Notification System) 📱

## نظرة عامة

تم تطوير نظام متكامل للرسائل النصية SMS يدعم إرسال الإشعارات التلقائية لأولياء الأمور والطلاب عبر **5 مزودين مختلفين** مع دعم كامل للغة العربية والأرقام المصرية.

---

## ✨ المميزات الرئيسية

### 1. **دعم متعدد المزودين (Multi-Driver Support)**
- ✅ **Twilio** - مزود عالمي (دول العالم)
- ✅ **Vonage/Nexmo** - مزود عالمي (دول العالم)
- ✅ **Vodafone Egypt** - مزود محلي (مصر فقط)
- ✅ **Log Driver** - للاختبار (يحفظ في logs بدون إرسال فعلي)

### 2. **Rate Limiting (منع الإرسال الجماعي)**
- حد أقصى **10 رسائل/دقيقة**
- حد أقصى **100 رسالة/ساعة**
- قابل للتخصيص من لوحة الإعدادات

### 3. **قوالب رسائل جاهزة (SMS Templates)**
```php
'attendance_absent' => 'عزيزي ولي الأمر، نفيدكم بأن الطالب/ة {student_name} كان غائباً/ة اليوم {date}'
'attendance_late' => 'إشعار تأخير الطالب {student_name} في تاريخ {date}'
'exam_reminder' => 'تذكير: امتحان {exam_title} في {exam_date} الساعة {exam_time}'
'payment_received' => 'تم استلام دفعة بمبلغ {amount} جنيه بنجاح'
'new_grade' => 'تم إضافة درجة جديدة للطالب {student_name} في {exam_title}'
```

### 4. **Database Logging**
- حفظ سجل كامل لكل رسالة
- حالة الإرسال (مرسل/فاشل/قيد الانتظار)
- المزود المستخدم
- وقت الإرسال
- Metadata إضافية

### 5. **Queue Support**
- إرسال الرسائل في الخلفية
- عدم تأخير العمليات الأساسية
- معالجة آمنة للأخطاء

### 6. **Phone Normalization**
- تحويل تلقائي للأرقام المصرية إلى صيغة E.164
- `01012345678` → `+201012345678`
- دعم جميع شبكات مصر (Vodafone, Orange, Etisalat, WE)

---

## 📁 البنية الهيكلية

```
app/
├── Services/
│   └── SmsService.php                     # خدمة إرسال SMS الرئيسية
├── Channels/
│   └── SmsChannel.php                     # قناة SMS للـ Notifications
├── Models/
│   └── SmsLog.php                         # Model سجل الرسائل
├── Notifications/
│   ├── AttendanceNotification.php         # إشعار الحضور (مع SMS)
│   ├── NewGradeNotification.php           # إشعار الدرجة (مع SMS)
│   ├── PaymentNotification.php            # إشعار الدفع (مع SMS)
│   ├── NewExamNotification.php            # إشعار امتحان (مع SMS)
│   └── NewAssignmentNotification.php      # إشعار واجب (مع SMS)
└── Filament/
    └── Admin/
        ├── Pages/
        │   └── SmsSettings.php            # صفحة إعدادات SMS
        ├── Resources/
        │   └── SmsLogResource.php         # إدارة سجل الرسائل
        └── Widgets/
            └── SmsStatsWidget.php         # إحصائيات SMS

config/
└── sms.php                                # ملف إعدادات SMS

database/
└── migrations/
    └── 2025_12_28_000036_create_sms_logs_table.php

resources/
└── views/
    └── filament/
        └── admin/
            └── pages/
                └── sms-settings.blade.php  # واجهة صفحة الإعدادات
```

---

## ⚙️ التثبيت والإعداد

### 1. تشغيل Migration
```bash
php artisan migrate
```

### 2. إضافة متغيرات البيئة (.env)
```env
# SMS Configuration
SMS_DEFAULT_DRIVER=log
SMS_QUEUE=true
SMS_LOG=true
SMS_RATE_LIMIT_PER_MINUTE=10
SMS_RATE_LIMIT_PER_HOUR=100

# Twilio SMS (Global)
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=+1234567890

# Vonage/Nexmo SMS (Global)
VONAGE_KEY=your_vonage_key
VONAGE_SECRET=your_vonage_secret
VONAGE_FROM=YourSchool

# Vodafone Egypt SMS
VODAFONE_USERNAME=your_username
VODAFONE_PASSWORD=your_password
VODAFONE_SENDER=YourSchool
```

### 3. إعداد Queue (للإرسال في الخلفية)
```bash
# في terminal منفصل
php artisan queue:work
```

### 4. التأكد من وجود رقم هاتف للمستخدمين
```sql
-- تأكد من وجود أرقام في جدول users
UPDATE users SET phone = '+201012345678' WHERE id = 1;
```

---

## 🚀 الاستخدام

### 1. **الإرسال التلقائي عبر Notifications**
عند إضافة غياب أو درجة أو دفعة، يتم الإرسال تلقائياً عبر:
- Database notification ✅
- Email ✅
- SMS ✅

**مثال:**
```php
use App\Notifications\AttendanceNotification;

// عند تسجيل غياب طالب
$parent = $student->parent->user;
$parent->notify(new AttendanceNotification($attendanceSession, $student, 'absent'));

// سيتم إرسال:
// 1. Database notification
// 2. Email
// 3. SMS إلى رقم ولي الأمر تلقائياً
```

### 2. **الإرسال اليدوي عبر SmsService**
```php
use App\Services\SmsService;

$smsService = app(SmsService::class);

// إرسال رسالة عادية
$smsService->send('+201012345678', 'مرحباً! هذه رسالة اختبار');

// إرسال بواسطة template
$smsService->sendTemplate('+201012345678', 'attendance_absent', [
    'student_name' => 'أحمد محمد',
    'date' => '2025-12-28',
]);
```

### 3. **الإحصائيات والتقارير**
```php
use App\Services\SmsService;

$stats = app(SmsService::class)->getStats();

/*
Array [
    'total' => 150,
    'sent' => 142,
    'failed' => 8,
    'today' => 25,
    'success_rate' => 94.7
]
*/
```

---

## 🎯 سيناريوهات الاستخدام

### السيناريو 1: إشعار غياب طالب
**متى يُرسَل؟** عند تسجيل غياب في AttendanceObserver

**النص المرسل:**
```
تحديث حضور: أحمد محمد - الحالة: غائب - المجموعة: الصف الأول - التاريخ: 2025-12-28
```

**المستقبل:** ولي أمر الطالب (parent.user.phone)

---

### السيناريو 2: إشعار درجة جديدة
**متى يُرسَل؟** عند إضافة درجة في ExamAttemptObserver

**النص المرسل:**
```
درجة جديدة: امتحان الرياضيات - الدرجة: 45/50 (90.0%)
```

**المستقبل:** ولي أمر الطالب + الطالب نفسه

---

### السيناريو 3: تأكيد دفعة
**متى يُرسَل؟** عند تأكيد دفعة في PaymentObserver

**النص المرسل:**
```
تم تأكيد دفع بمبلغ 500 جنيه - الحالة: completed
```

**المستقبل:** ولي أمر الطالب

---

### السيناريو 4: تذكير امتحان
**متى يُرسَل؟** عند إضافة امتحان جديد في ExamObserver

**النص المرسل:**
```
امتحان جديد: امتحان العلوم - المجموعة: الصف الثاني - الموعد: 2025-12-30 10:00 - المدة: 60 دقيقة
```

**المستقبل:** جميع طلاب المجموعة + أولياء أمورهم

---

### السيناريو 5: واجب جديد
**متى يُرسَل؟** عند إضافة واجب في AssignmentObserver

**النص المرسل:**
```
واجب جديد: واجب الفيزياء - المجموعة: الصف الثالث - موعد التسليم: 2025-12-29
```

**المستقبل:** جميع طلاب المجموعة

---

## 📊 لوحة التحكم (Admin Panel)

### 1. **صفحة إعدادات SMS** (`/admin/sms-settings`)
- اختيار المزود الافتراضي
- تفعيل/تعطيل Queue
- تعديل Rate Limiting
- إدخال بيانات المزودين (API Keys)
- زر "اختبار الإرسال" (يرسل رسالة للمدير المسجل دخوله)

### 2. **صفحة سجل الرسائل** (`/admin/sms-logs`)
- عرض جميع الرسائل المرسلة
- فلترة حسب (الحالة - المزود - التاريخ)
- بحث برقم الهاتف
- عرض تفاصيل كل رسالة
- حذف رسائل قديمة

### 3. **Widget الإحصائيات** (في Dashboard)
- إجمالي الرسائل
- رسائل مرسلة (مع معدل النجاح)
- رسائل فاشلة
- رسائل اليوم
- رسم بياني لآخر 7 أيام

---

## 🔧 التخصيص المتقدم

### إضافة Template جديد
```php
// في config/sms.php
'templates' => [
    'custom_template' => 'النص المخصص مع {variable1} و {variable2}',
],

// الاستخدام
$smsService->sendTemplate($phone, 'custom_template', [
    'variable1' => 'قيمة 1',
    'variable2' => 'قيمة 2',
]);
```

### تغيير Rate Limit برمجياً
```php
// في config/sms.php
'rate_limit' => [
    'per_minute' => env('SMS_RATE_LIMIT_PER_MINUTE', 20), // زيادة إلى 20
    'per_hour' => env('SMS_RATE_LIMIT_PER_HOUR', 200),   // زيادة إلى 200
],
```

### إضافة Metadata للرسائل
```php
$smsService->send($phone, $message, [
    'user_id' => auth()->id(),
    'module' => 'attendance',
    'action' => 'absent_notification',
]);
```

---

## 🛡️ الأمان والخصوصية

1. **API Keys محمية**: جميع المفاتيح في .env ولا تُحفظ في قاعدة البيانات
2. **Rate Limiting**: منع إساءة استخدام النظام بإرسال آلاف الرسائل
3. **Phone Validation**: التأكد من صحة الأرقام قبل الإرسال
4. **Logging**: سجل كامل لكل عملية للمراجعة

---

## 📈 الإحصائيات المتاحة

### في الـ Widget
- إجمالي الرسائل (مع رسم بياني لآخر 7 أيام)
- معدل النجاح (Success Rate)
- رسائل اليوم
- رسائل فاشلة (مع تنبيه)

### في صفحة الإعدادات
- 4 بطاقات إحصائية:
  - إجمالي الرسائل
  - رسائل مرسلة
  - رسائل فاشلة
  - رسائل اليوم

---

## 🧪 الاختبار

### 1. اختبار من لوحة الإعدادات
- افتح `/admin/sms-settings`
- اضغط "اختبار الإرسال"
- ستصلك رسالة على رقمك المسجل

### 2. اختبار يدوي من Tinker
```bash
php artisan tinker
```
```php
$smsService = app(\App\Services\SmsService::class);
$smsService->send('+201012345678', 'رسالة اختبار');
```

### 3. اختبار Notification
```php
$user = User::find(1);
$user->notify(new \App\Notifications\AttendanceNotification($session, $student, 'absent'));
```

---

## ❓ استكشاف الأخطاء

### خطأ: "Rate limit exceeded"
**الحل:** انتظر دقيقة أو زد الحد في الإعدادات

### خطأ: "Invalid phone number"
**الحل:** تأكد من الصيغة (+20xxxxxxxxxx)

### خطأ: "Driver not configured"
**الحل:** أدخل API Keys في .env

### رسائل لا تُرسَل
**الحل:** 
1. تأكد من Queue يعمل: `php artisan queue:work`
2. تأكد من Driver صحيح في .env
3. راجع logs: `storage/logs/laravel.log`

---

## 🎓 ملخص سريع

| الميزة | الحالة |
|--------|--------|
| 5 Notification Classes محدثة | ✅ |
| SmsService متكامل | ✅ |
| SmsChannel مخصص | ✅ |
| SmsLog Model + Migration | ✅ |
| Admin Settings Page | ✅ |
| SMS Logs Resource | ✅ |
| Stats Widget | ✅ |
| 5 SMS Templates | ✅ |
| Rate Limiting | ✅ |
| Queue Support | ✅ |
| Phone Normalization | ✅ |
| Database Logging | ✅ |

---

## 📞 الدعم الفني

للحصول على مساعدة في إعداد النظام:
1. راجع هذا الملف أولاً
2. تحقق من logs
3. جرب اختبار الإرسال من لوحة الإعدادات
4. تأكد من صحة API Keys

---

**تم تطوير هذا النظام بواسطة:** AI School System  
**التاريخ:** 28 ديسمبر 2025  
**الإصدار:** 1.0.0
