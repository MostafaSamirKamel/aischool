# دليل إعداد Cron Job من cPanel

## 📋 الخطوات التفصيلية

### 1️⃣ تسجيل الدخول إلى cPanel
- افتح متصفح وادخل على: `yourdomain.com/cpanel`
- أدخل username و password الخاص بالاستضافة

### 2️⃣ البحث عن Cron Jobs
- في صفحة cPanel الرئيسية
- ابحث عن **"Cron Jobs"** في شريط البحث
- أو ابحث في قسم **"Advanced"**
- اضغط على أيقونة **Cron Jobs**

### 3️⃣ إعداد Cron Job جديد

في صفحة Cron Jobs:

#### أ. تحديد البريد الإلكتروني (اختياري):
```
Cron Email: your-email@example.com
```
سيرسل لك إشعارات عند تشغيل أو فشل المهمة.

#### ب. إضافة Cron Job جديد:

**Common Settings:** اختر `Once Per Minute (* * * * *)`

أو ضع القيم يدوياً:

```
Minute:   *
Hour:     *
Day:      *
Month:    *
Weekday:  *
```

**Command:** (المهم جداً):

**إذا كان الموقع في public_html:**
```bash
cd /home/username/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

**إذا كان في subdomain:**
```bash
cd /home/username/public_html/subdomain && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

**إذا كان في مجلد خاص:**
```bash
cd /home/username/domains/yourdomain.com/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

#### ج. معلومات مهمة:

- **استبدل `username`** باسم المستخدم الخاص بالاستضافة
- **استبدل المسار** بمسار المشروع الفعلي على السيرفر
- `/usr/bin/php` هو مسار PHP (قد يختلف حسب السيرفر)

### 4️⃣ التحقق من مسار PHP

لمعرفة مسار PHP الصحيح على السيرفر:

1. اذهب إلى **Terminal** في cPanel
2. اكتب:
```bash
which php
```
أو
```bash
which php8.2
```

النتيجة ستكون مثل:
- `/usr/bin/php`
- `/usr/local/bin/php`
- `/opt/cpanel/ea-php82/root/usr/bin/php`

استخدم المسار الذي يظهر في الـ Command.

### 5️⃣ أمثلة Commands حسب نوع الاستضافة:

#### Shared Hosting (استضافة مشتركة):
```bash
cd /home/username/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

#### VPS أو Dedicated Server:
```bash
cd /var/www/html/your-project && php artisan schedule:run >> /dev/null 2>&1
```

#### إذا كنت تستخدم PHP version محدد:
```bash
cd /home/username/public_html && /opt/cpanel/ea-php82/root/usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

### 6️⃣ حفظ Cron Job
- اضغط زر **"Add New Cron Job"**
- ستظهر رسالة نجاح

### 7️⃣ التحقق من التشغيل

الـ Cron Job سيعمل **كل دقيقة** ويفحص المهام المجدولة.

**لاحظ:** Laravel Scheduler سيشغل المهام المجدولة في أوقاتها المحددة فقط:
- `installments:update-status` سيشتغل الساعة 00:00 فقط
- `exams:send-reminders` سيشتغل الساعة 08:00 فقط

## 🔍 استكشاف الأخطاء

### المشكلة 1: Cron Job لا يعمل

**الحل:**
1. تأكد من المسار صحيح
2. تحقق من صلاحيات الملفات:
```bash
chmod -R 755 /home/username/public_html
chmod -R 775 storage bootstrap/cache
```

### المشكلة 2: Permission Denied

**الحل:**
```bash
cd /home/username/public_html
chown -R username:username .
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
```

### المشكلة 3: مسار PHP خاطئ

**الحل:** جرب هذه المسارات:
- `/usr/bin/php`
- `/usr/bin/php8.2`
- `/usr/local/bin/php`
- `/opt/cpanel/ea-php82/root/usr/bin/php`

### المشكلة 4: لا توجد نتائج

**لحفظ Output في ملف للفحص:**
```bash
cd /home/username/public_html && /usr/bin/php artisan schedule:run >> /home/username/cron.log 2>&1
```

ثم افحص الملف `cron.log` من File Manager.

## 📧 تفعيل إشعارات البريد الإلكتروني

في أعلى صفحة Cron Jobs:

```
Cron Email: your-email@example.com
```

سيرسل لك:
- إشعار عند كل تشغيل (يمكن أن يكون مزعج)
- رسائل الأخطاء فقط

**لتعطيل الإشعارات:** أضف في نهاية Command:
```bash
>> /dev/null 2>&1
```

**لحفظ Output فقط:**
```bash
>> /home/username/cron-output.log 2>&1
```

**لحفظ Errors فقط:**
```bash
2>> /home/username/cron-errors.log
```

## ✅ التحقق من نجاح التشغيل

بعد 24 ساعة من الإعداد:

1. افتح phpMyAdmin من cPanel
2. اذهب لجدول `installments`
3. افحص عمود `status`
4. الأقساط المتأخرة يجب أن تكون `overdue`

## 🎯 ملخص Command النهائي

```bash
* * * * * cd /home/username/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

هذا هو كل ما تحتاجه! ✅

## 📱 معلومات إضافية

- **التكرار:** كل دقيقة (Laravel سيحدد متى يشغل كل command)
- **التكلفة:** مجاني (جزء من خدمة الاستضافة)
- **الصيانة:** لا تحتاج تدخل بعد الإعداد
- **الموثوقية:** عالية جداً

## 🔄 بدائل إذا لم يعمل Cron

إذا كانت الاستضافة لا تدعم Cron Jobs:

1. استخدم خدمة خارجية مثل:
   - **cron-job.org** (مجاني)
   - **EasyCron** (مجاني/مدفوع)
   - **Webcron** (مدفوع)

2. اعمل endpoint في routes/web.php:
```php
Route::get('/run-schedule', function () {
    Artisan::call('schedule:run');
    return 'Scheduled tasks executed!';
})->middleware('throttle:1,1');
```

ثم أضف الرابط في خدمة Cron خارجية:
```
https://yoursite.com/run-schedule
```
