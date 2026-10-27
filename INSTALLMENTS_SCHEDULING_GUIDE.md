# جدولة تحديث حالة الأقساط

## الإعداد

تم إضافة جدولة تلقائية لتحديث حالة الأقساط يومياً في الساعة 12:00 صباحاً (منتصف الليل).

### ملف الجدولة
تم التعديل في: `routes/console.php`

```php
Schedule::command('installments:update-status')
    ->daily()
    ->at('00:00')
    ->description('تحديث حالة الأقساط وتحديد المتأخرة منها');
```

## تفعيل الجدولة

### 1. على السيرفر المحلي (Local Development)

شغل Laravel Scheduler في terminal منفصل:

```bash
php artisan schedule:work
```

هذا الأمر سيبقى شغال ويفحص الجدولة كل دقيقة.

### 2. على السيرفر (Production)

#### أ. على Linux/Ubuntu Server:

1. افتح crontab:
```bash
crontab -e
```

2. أضف السطر التالي:
```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

استبدل `/path/to/your/project` بمسار المشروع الفعلي.

#### ب. على Windows Server:

1. افتح **Task Scheduler**
2. اختر **Create Basic Task**
3. اسم المهمة: "Laravel Scheduler"
4. Trigger: **Daily** في أي وقت
5. Action: **Start a program**
   - Program: `C:\php\php.exe` (مسار PHP)
   - Arguments: `artisan schedule:run`
   - Start in: `D:\My Work\Laravel\Ai School` (مسار المشروع)
6. في الـ Settings، فعّل: **Run task as soon as possible after a scheduled start is missed**

#### ج. على Shared Hosting:

من لوحة تحكم cPanel:
1. اذهب إلى **Cron Jobs**
2. أضف cron job جديد:
   - Minute: `*`
   - Hour: `*`
   - Day: `*`
   - Month: `*`
   - Weekday: `*`
   - Command: `cd /home/username/public_html && php artisan schedule:run`

## التحقق من الجدولة

### عرض جميع المهام المجدولة:
```bash
php artisan schedule:list
```

### تشغيل Command يدوياً للاختبار:
```bash
php artisan installments:update-status
```

### تشغيل جميع المهام المجدولة يدوياً:
```bash
php artisan schedule:run
```

## ماذا يفعل Command؟

عند التشغيل اليومي، الـ Command:
1. ✅ يفحص جميع الأقساط ذات حالة `pending` أو `partial`
2. ✅ يحدث حالة الأقساط التي تجاوزت تاريخ الاستحقاق إلى `overdue`
3. ✅ يعرض إحصائيات: عدد الأقساط المتأخرة وإجمالي المبالغ

## ملاحظات مهمة

### لماذا 12:00 صباحاً؟
- لضمان تحديث الحالات في بداية اليوم الجديد
- لعرض البيانات الصحيحة للطلاب وأولياء الأمور عند دخولهم

### تخصيص الوقت:
يمكنك تغيير وقت التشغيل حسب الحاجة:

```php
// كل يوم الساعة 1 صباحاً
->dailyAt('01:00')

// كل يوم الساعة 6 صباحاً
->dailyAt('06:00')

// مرتين يومياً (صباحاً ومساءً)
->twiceDaily(1, 13)  // 1 AM و 1 PM

// كل 6 ساعات
->everySixHours()

// فقط في أيام الأسبوع
->weekdays()->at('00:00')
```

### Logging التشغيل:
لتسجيل نتائج التشغيل:

```php
Schedule::command('installments:update-status')
    ->daily()
    ->at('00:00')
    ->appendOutputTo(storage_path('logs/installments-update.log'));
```

### إرسال إشعار عند الفشل:
```php
Schedule::command('installments:update-status')
    ->daily()
    ->at('00:00')
    ->emailOutputOnFailure('admin@example.com');
```

## التحقق من التشغيل

تحقق من ملف `storage/logs/laravel.log` لرؤية سجلات التشغيل.

أو استخدم:
```bash
tail -f storage/logs/laravel.log
```

## استكشاف الأخطاء

### المشكلة: الجدولة لا تعمل
**الحل:** تأكد من أن cron job يعمل:
```bash
# Linux
service cron status

# أو
systemctl status cron
```

### المشكلة: Command لا يعمل يدوياً
**الحل:** تحقق من الأخطاء:
```bash
php artisan installments:update-status -v
```

### المشكلة: لا توجد أقساط يتم تحديثها
**تحقق من:**
1. هل يوجد أقساط في قاعدة البيانات؟
2. هل تواريخ الاستحقاق صحيحة؟
3. شغل Command مباشرة وراقب النتائج

## مثال على Output:

```
Updating installments status...
Updated 5 installment(s) status.
Total overdue installments: 12
Total overdue amount: 15,450.00 EGP
```
