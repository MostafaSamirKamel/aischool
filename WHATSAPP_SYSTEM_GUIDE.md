# 📱 نظام إرسال رسائل WhatsApp - دليل شامل

## 🎯 نظرة عامة

نظام متكامل لإرسال رسائل واتساب تلقائياً عبر **SOG WhatsApp Sender** (الخدمة الحكومية المصرية) أو **Ultramsg** كبديل.

---

## ✨ المميزات الرئيسية

### 1. **دعم متعدد المزودين (Multi-Provider)**
- ✅ **SOG** (الخدمة الحكومية المصرية) - https://sender.whatsapp.sog.eg
- ✅ **Ultramsg** (خدمة عالمية) - https://ultramsg.com
- ✅ **Log** (للتطوير والاختبار)

### 2. **أنواع الرسائل المدعومة**
- 📝 **رسائل نصية** (Text Messages)
- 🖼️ **صور مع نص** (Images with Caption)
- 📄 **مستندات** (Documents/PDFs)

### 3. **الإرسال التلقائي عبر Notifications**
يتم إرسال رسائل واتساب تلقائياً عند:
- 📊 **تسجيل حضور/غياب** طالب
- 📝 **إضافة درجة** امتحان
- 💰 **تأكيد دفع** قسط
- 📢 **إشعارات امتحانات** وواجبات
- 📬 **رسائل المجموعات**

### 4. **Rate Limiting (منع الإرسال الجماعي)**
- 5 رسائل/دقيقة لكل رقم
- 50 رسالة/ساعة لكل رقم
- حماية من Spam

### 5. **Database Logging**
- تسجيل جميع الرسائل المرسلة
- حالة الإرسال (نجح/فشل)
- Metadata إضافية
- إحصائيات مفصلة

### 6. **Queue Support (إرسال في الخلفية)**
- إرسال الرسائل عبر Laravel Queue
- عدم تأخير الواجهة الأمامية
- إدارة أفضل للموارد

### 7. **قوالب رسائل جاهزة**
10+ قالب جاهز بالعربية مع Emojis

---

## 📁 البنية الهيكلية

```
app/
├── Contracts/
│   └── WhatsAppProviderInterface.php      # Interface للمزودين
├── Services/
│   ├── WhatsAppService.php                # الخدمة الرئيسية
│   └── WhatsAppProviders/
│       ├── SogProvider.php                # مزود SOG
│       ├── UltramsgProvider.php           # مزود Ultramsg
│       └── LogProvider.php                # مزود Log
├── Channels/
│   └── WhatsAppChannel.php                # قناة WhatsApp للـ Notifications
├── Models/
│   └── WhatsAppLog.php                    # Model سجل الرسائل
├── Notifications/
│   ├── AttendanceNotification.php         # ✅ يدعم WhatsApp
│   ├── PaymentNotification.php            # ✅ يدعم WhatsApp
│   └── NewGradeNotification.php           # ✅ يدعم WhatsApp
└── config/
    └── whatsapp.php                       # ملف الإعدادات

database/
└── migrations/
    └── 2026_04_18_210052_create_whatsapp_logs_table.php
```

---

## ⚙️ التثبيت والإعداد

### 1. تشغيل Migration
```bash
php artisan migrate
```

### 2. إعداد متغيرات البيئة (.env)

#### ✅ باستخدام SOG (الخدمة الحكومية المصرية)
```env
WHATSAPP_DEFAULT_DRIVER=sog
WHATSAPP_QUEUE=true
WHATSAPP_LOG=true
WHATSAPP_RATE_LIMIT_PER_MINUTE=5
WHATSAPP_RATE_LIMIT_PER_HOUR=50

SOG_WHATSAPP_API_URL=https://sender.whatsapp.sog.eg/api
SOG_WHATSAPP_API_KEY=your_api_key_here
SOG_WHATSAPP_SENDER=201012345678
```

**📝 كيفية الحصول على بيانات SOG:**
1. سجل في: https://sender.whatsapp.sog.eg
2. احصل على **API Key** من لوحة التحكم
3. احصل على **Sender Number** (رقم المرسل المعتمد)

#### 🔄 بديل: Ultramsg
```env
WHATSAPP_DEFAULT_DRIVER=ultramsg

ULTRAMSG_INSTANCE_ID=instance12345
ULTRAMSG_TOKEN=your_token_here
```

**📝 كيفية الحصول على بيانات Ultramsg:**
1. سجل في: https://ultramsg.com
2. اربط رقم واتساب عبر QR Code
3. احصل على **Instance ID** و **Token**

#### 🧪 للتطوير والاختبار
```env
WHATSAPP_DEFAULT_DRIVER=log
```
سيتم تسجيل الرسائل في `storage/logs/laravel.log` فقط

### 3. إعداد Queue (للإرسال في الخلفية)
```bash
# في terminal منفصل
php artisan queue:work
```

أو في الخلفية (Linux):
```bash
nohup php artisan queue:work --queue=whatsapp &
```

### 4. التأكد من وجود رقم هاتف للمستخدمين
```sql
-- تأكد من وجود أرقام في جدول users
UPDATE users SET phone = '+201012345678' WHERE id = 1;
```

---

## 🚀 الاستخدام

### 1. **الإرسال التلقائي عبر Notifications**

عند تسجيل حضور أو إضافة درجة، **يتم الإرسال تلقائياً** عبر:
- ✅ Database notification
- ✅ Email
- ✅ SMS
- ✅ **WhatsApp** ← **جديد!**

**مثال:**
```php
use App\Notifications\AttendanceNotification;

// عند تسجيل غياب طالب
$parent = $student->parent->user;
$parent->notify(new AttendanceNotification($attendanceSession, $student, 'absent'));

// سيتم إرسال:
// 1. Database notification
// 2. Email
// 3. SMS
// 4. WhatsApp تلقائياً ✅
```

---

### 2. **الإرسال اليدوي عبر WhatsAppService**

#### أ) إرسال رسالة نصية
```php
use App\Services\WhatsAppService;

$whatsapp = app(WhatsAppService::class);

$whatsapp->send(
    '+201012345678',
    'مرحباً! هذه رسالة اختبارية من النظام'
);
```

#### ب) إرسال صورة
```php
$whatsapp->sendImage(
    '+201012345678',
    'شهادة التقدير الخاصة بك',
    'https://example.com/certificate.jpg'
);
```

#### ج) إرسال مستند
```php
$whatsapp->sendDocument(
    '+201012345678',
    'https://example.com/report.pdf',
    'تقرير_الطالب.pdf'
);
```

#### د) إرسال عبر قالب جاهز
```php
$whatsapp->sendTemplate(
    '+201012345678',
    'attendance_absent',
    [
        'student_name' => 'أحمد محمد',
        'date' => '2026-04-19',
        'group_name' => 'المجموعة الأولى',
        'session_title' => 'حصة الرياضيات',
    ]
);
```

---

### 3. **القوالب الجاهزة (Templates)**

#### 🔴 غياب طالب
```php
'attendance_absent' => '🔴 *تحديث حضور*' . "\n\n" .
    'عزيزي ولي الأمر، نفيدكم بأن الطالب/ة *{student_name}* كان غائباً/ة في تاريخ {date}' . "\n\n" .
    'المجموعة: {group_name}' . "\n" .
    'الحصة: {session_title}' . "\n\n" .
    'للاستفسار يرجى التواصل مع الإدارة.',
```

#### ✅ حضور طالب
```php
'attendance_present' => '✅ *تأكيد حضور*' . "\n\n" .
    'تم تسجيل حضور الطالب/ة *{student_name}* في تاريخ {date}' . "\n\n" .
    'المجموعة: {group_name}' . "\n" .
    'الحصة: {session_title}',
```

#### 💰 تأكيد دفع
```php
'payment_received' => '💰 *تأكيد دفع*' . "\n\n" .
    'تم استلام دفعة بمبلغ *{amount} جنيه* بنجاح' . "\n\n" .
    'رقم الدفع: {payment_id}' . "\n" .
    'التاريخ: {date}' . "\n" .
    'المتبقي: {remaining} جنيه' . "\n\n" .
    'شكراً لثقتكم بنا! 🙏',
```

#### 📝 درجة جديدة
```php
'new_grade' => '📝 *درجة جديدة*' . "\n\n" .
    'تم إضافة درجة جديدة للطالب/ة *{student_name}*' . "\n\n" .
    'الامتحان: {exam_title}' . "\n" .
    'الدرجة: {grade}/{max_grade}' . "\n" .
    'التقدير: {grade_letter}',
```

**جميع القوالب موجودة في:** `config/whatsapp.php`

---

## 🎯 سيناريوهات الاستخدام

### السيناريو 1: إشعار غياب طالب
**متى يُرسَل؟** عند تسجيل غياب في `AttendanceObserver`

**النص المرسل:**
```
🔴 تحديث حضور

الطالب/ة: أحمد محمد
الحالة: غائب 🔴
المجموعة: الصف الأول
التاريخ: 2026-04-19

للاستفسار يرجى التواصل مع الإدارة.
```

**المستلم:** ولي أمر الطالب (`parent.user.phone`)

---

### السيناريو 2: إشعار دفع قسط
**متى يُرسَل؟** عند تأكيد دفع في `PaymentObserver`

**النص المرسل:**
```
💰 تم تأكيد الدفع

المبلغ: 500 جنيه
طريقة الدفع: كاش
الحالة: completed
التاريخ: 2026-04-19

شكراً لثقتكم بنا! 🙏
```

**المستلم:** ولي أمر الطالب

---

### السيناريو 3: نتيجة امتحان
**متى يُرسَل؟** عند إضافة درجة امتحان

**النص المرسل:**
```
🌟 درجة امتحان جديدة

الامتحان: امتحان الرياضيات
الدرجة: 45/50
النسبة: 90%
النتيجة: ناجح ✅

استمر في التقدم! 💪
```

**المستلمون:** الطالب + ولي الأمر

---

## 📊 الإحصائيات والتقارير

### الحصول على إحصائيات
```php
use App\Services\WhatsAppService;

$whatsapp = app(WhatsAppService::class);
$stats = $whatsapp->getStats();

/*
[
    'total' => 1250,
    'sent' => 1180,
    'failed' => 70,
    'today' => 45,
    'by_provider' => [
        'SOG' => 1100,
        'Ultramsg' => 80,
        'Log' => 70,
    ],
]
*/
```

### الاستعلام من قاعدة البيانات
```php
use App\Models\WhatsAppLog;

// جميع الرسائل الناجحة
$sent = WhatsAppLog::sent()->get();

// الرسائل الفاشلة
$failed = WhatsAppLog::failed()->get();

// رسائل اليوم
$today = WhatsAppLog::today()->get();

// رسائل مزود معين
$sogMessages = WhatsAppLog::provider('SOG')->get();

// آخر 10 رسائل
$recent = WhatsAppLog::latest()->take(10)->get();
```

---

## 🔧 التخصيص المتقدم

### 1. إنشاء مزود جديد (Custom Provider)

إنشاء ملف `app/Services/WhatsAppProviders/MyProvider.php`:

```php
<?php

namespace App\Services\WhatsAppProviders;

use App\Contracts\WhatsAppProviderInterface;

class MyProvider implements WhatsAppProviderInterface
{
    public function send(string $to, string $message): bool
    {
        // Your custom logic here
        return true;
    }

    public function sendImage(string $to, string $message, string $imageUrl): bool
    {
        // Your custom logic here
        return true;
    }

    public function sendDocument(string $to, string $documentUrl, string $filename): bool
    {
        // Your custom logic here
        return true;
    }

    public function getName(): string
    {
        return 'MyCustomProvider';
    }

    public function isConfigured(): bool
    {
        return !empty(config('whatsapp.myprovider.api_key'));
    }
}
```

تحديث `WhatsAppService.php`:
```php
protected function createProvider(string $driver): WhatsAppProviderInterface
{
    return match ($driver) {
        'sog' => new SogProvider(),
        'ultramsg' => new UltramsgProvider(),
        'myprovider' => new MyProvider(), // ← جديد
        'log' => new LogProvider(),
        default => new LogProvider(),
    };
}
```

---

### 2. إضافة Metadata للرسائل
```php
$whatsapp->send($phone, $message, [
    'user_id' => auth()->id(),
    'module' => 'attendance',
    'action' => 'absent_notification',
    'extra_data' => ['key' => 'value'],
]);
```

---

### 3. إنشاء Notification جديد بدعم WhatsApp

```php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class MyCustomNotification extends Notification
{
    public function via($notifiable): array
    {
        return ['database', 'mail', \App\Channels\WhatsAppChannel::class];
    }

    public function toWhatsApp($notifiable): string
    {
        return "📢 *رسالة مخصصة*\n\n" .
               "المحتوى: {$this->content}\n\n" .
               "شكراً!";
    }
}
```

---

## 🧪 الاختبار

### 1. اختبار يدوي من Tinker
```bash
php artisan tinker
```

```php
$whatsapp = app(\App\Services\WhatsAppService::class);

// اختبار رسالة نصية
$whatsapp->send('+201012345678', 'رسالة اختبار من النظام');

// اختبار قالب
$whatsapp->sendTemplate('+201012345678', 'welcome', [
    'student_name' => 'أحمد محمد',
    'school_name' => 'مدرسة النور',
]);

// عرض الإحصائيات
$whatsapp->getStats();
```

### 2. اختبار Notification
```php
$user = User::find(1);
$user->notify(new \App\Notifications\AttendanceNotification($session, $student, 'absent'));
```

### 3. التحقق من السجل
```php
use App\Models\WhatsAppLog;

// آخر رسالة
$latest = WhatsAppLog::latest()->first();
dd($latest->toArray());
```

---

## ❓ الأسئلة الشائعة (FAQ)

### س: كيف أغير المزود من SOG إلى Ultramsg؟
```env
# في ملف .env
WHATSAPP_DEFAULT_DRIVER=ultramsg

# وأضف بيانات Ultramsg
ULTRAMSG_INSTANCE_ID=your_instance
ULTRAMSG_TOKEN=your_token
```

### س: كيف أعطل إرسال WhatsApp مؤقتاً؟
```env
WHATSAPP_DEFAULT_DRIVER=log
```

### س: الرسائل لا تُرسل، ماذا أفعل؟

1. **تحقق من السجلات:**
```bash
tail -f storage/logs/laravel.log
```

2. **تحقق من الـ Queue:**
```bash
php artisan queue:work
```

3. **تحقق من بيانات API:**
```php
php artisan tinker
config('whatsapp.sog.api_key'); // يجب أن لا يكون فارغاً
```

4. **اختبار يدوي:**
```php
$whatsapp = app(\App\Services\WhatsAppService::class);
$result = $whatsapp->send('+201012345678', 'test');
dd($result); // يجب أن يكون true
```

### س: كيف أضيف قالب رسالة جديد؟

في `config/whatsapp.php`:
```php
'templates' => [
    // ... القوالب الموجودة
    
    'my_custom_template' => '📢 *عنوان مخصص*' . "\n\n" .
        'المحتوى: {content}' . "\n" .
        'التاريخ: {date}',
],
```

الاستخدام:
```php
$whatsapp->sendTemplate('+20...', 'my_custom_template', [
    'content' => 'نص مخصص',
    'date' => now()->format('Y-m-d'),
]);
```

---

## 🔒 الأمان

### 1. Rate Limiting
- يمنع إرسال أكثر من **5 رسائل/دقيقة** للرقم الواحد
- يمنع إرسال أكثر من **50 رسالة/ساعة** للرقم الواحد

### 2. تطبيع الأرقام
جميع الأرقام يتم تطبيعها تلقائياً:
- `01012345678` → `+201012345678`
- `00201012345678` → `+201012345678`

### 3. التحقق من الإعدادات
قبل الإرسال، يتم التحقق من:
- وجود `API Key`
- وجود `Sender Number`
- وجود رقم هاتف للمستقبل

---

## 📈 الأداء

### نصائح للأداء الأمثل:

1. **استخدم Queue دائماً:**
```env
WHATSAPP_QUEUE=true
```

2. **شغل Queue Workers كافية:**
```bash
php artisan queue:work --queue=whatsapp --tries=3
```

3. **نظف السجلات القديمة:**
```php
// حذف رسائل أقدم من 90 يوم
WhatsAppLog::where('created_at', '<', now()->subDays(90))->delete();
```

4. **استخدم Database Queue Driver:**
```env
QUEUE_CONNECTION=database
```

---

## 🎓 الخلاصة

### ✅ ما تم تنفيذه:
1. ✅ نظام كامل لإرسال رسائل WhatsApp
2. ✅ دعم **SOG** (حكومي مصري) + **Ultramsg** (عالمي)
3. ✅ إرسال تلقائي عبر Notifications
4. ✅ قوالب جاهزة بالعربية (10+ قالب)
5. ✅ Rate Limiting + Queue Support
6. ✅ Database Logging + Statistics
7. ✅ دعم الصور والمستندات

### 📝 الملفات المهمة:
- `config/whatsapp.php` - الإعدادات
- `app/Services/WhatsAppService.php` - الخدمة الرئيسية
- `app/Channels/WhatsAppChannel.php` - قناة Notifications
- `app/Models/WhatsAppLog.php` - Model السجل

### 🚀 للبدء:
1. شغل Migration: `php artisan migrate`
2. حدّث `.env` ببيانات SOG أو Ultramsg
3. شغل Queue: `php artisan queue:work`
4. اختبر: `php artisan tinker`

---

## 📞 الدعم الفني

للاستفسارات أو المشاكل، راجع:
- 📖 `storage/logs/laravel.log`
- 📊 `whatsapp_logs` table
- 🧪 `php artisan tinker`

**تم بنجاح! 🎉**
