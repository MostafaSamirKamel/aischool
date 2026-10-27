# إشعارات اكتمال الكورسات

## نظرة عامة
عند اكتمال الطالب لجميع جلسات الكورس، يتم إرسال إشعارات تلقائية للطالب وأولياء الأمور.

## المستلمون

### 1. الطالب
- يستلم إشعار في لوحة التحكم (Database Notification)
- يستلم بريد إلكتروني (Email Notification)

### 2. أولياء الأمور
- جميع أولياء أمور الطالب يستلمون نفس الإشعارات

## قنوات الإشعار

### Database Notification
يظهر في:
- قائمة الإشعارات في Panel
- أيقونة الإشعارات في الـ Header

محتوى الإشعار:
```json
{
  "type": "course_completed",
  "title": "اكتمال الكورس",
  "message": "تهانينا! لقد أكملت كورس [اسم الكورس]",
  "enrollment_course_id": 123,
  "course_id": 45,
  "course_name": "البرمجة بلغة Python",
  "total_sessions": 10,
  "icon": "heroicon-o-academic-cap",
  "color": "success"
}
```

### Email Notification
البريد الإلكتروني يحتوي على:
- ✅ الموضوع: "🎉 تهانينا! لقد أكملت الكورس"
- ✅ تحية شخصية باسم الطالب
- ✅ اسم الكورس المكتمل
- ✅ عدد الجلسات المكتملة
- ✅ رابط للذهاب إلى لوحة تحكم الطالب
- ✅ رسالة تهنئة

## الكود المسؤول

### Notification Class
**الملف:** `app/Notifications/CourseCompletedNotification.php`

```php
public function __construct(
    public EnrollmentCourse $enrollmentCourse
)

public function via(object $notifiable): array
{
    return ['database', 'mail'];
}

public function toMail(object $notifiable): MailMessage
{
    // بناء رسالة البريد الإلكتروني
}

public function toArray(object $notifiable): array
{
    // بيانات Database Notification
}
```

### Trigger Point
**الملف:** `app/Models/EnrollmentCourse.php`

```php
public function markAsCompleted(): void
{
    $this->update([
        'status' => 'completed',
        'current_session' => $this->total_sessions,
    ]);

    // Send to student
    $this->enrollment->student->user->notify(
        new CourseCompletedNotification($this)
    );

    // Send to parents
    foreach ($this->enrollment->student->parents as $parent) {
        $parent->notify(
            new CourseCompletedNotification($this)
        );
    }
}
```

## متى يتم الإرسال؟

الإشعار يُرسل تلقائياً عندما:
1. يتم إنشاء جلسة حضور جديدة (`AttendanceSession`)
2. يصل `current_session` إلى `total_sessions`
3. يتم استدعاء `markAsCompleted()` في `EnrollmentCourse`
4. تُرسل الإشعارات فوراً للطالب وأولياء الأمور

## التدفق الكامل

```
إنشاء جلسة حضور جديدة
    ↓
AttendanceSessionObserver::created()
    ↓
EnrollmentCourse::incrementSession()
    ↓
هل current_session >= total_sessions ؟
    ↓ نعم
EnrollmentCourse::markAsCompleted()
    ↓
إرسال CourseCompletedNotification
    ↓
├─→ للطالب (Database + Email)
└─→ لأولياء الأمور (Database + Email)
```

## عرض الإشعارات

### في Filament Panel
الإشعارات تظهر في:
- أيقونة الجرس في الـ Header
- صفحة الإشعارات `/notifications`

### في البريد الإلكتروني
- يتم إرسال بريد إلى `$user->email`
- التصميم يستخدم Laravel Mail Templates
- يمكن تخصيص التصميم في `resources/views/vendor/mail`

## تخصيص الإشعارات

### تغيير محتوى البريد
عدّل في `app/Notifications/CourseCompletedNotification.php`:

```php
public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->subject('موضوعك المخصص')
        ->greeting('تحية مخصصة')
        ->line('المحتوى...')
        // ...
}
```

### إضافة قنوات أخرى
يمكن إضافة SMS أو Push Notifications:

```php
public function via(object $notifiable): array
{
    return ['database', 'mail', 'sms', 'fcm'];
}
```

### إيقاف إشعار معين
لإيقاف البريد الإلكتروني:

```php
public function via(object $notifiable): array
{
    return ['database']; // فقط database
}
```

## Queue Processing

الإشعار يستخدم Queue (ShouldQueue):

```php
class CourseCompletedNotification extends Notification implements ShouldQueue
```

**تشغيل Queue Worker:**
```bash
php artisan queue:work
```

**أو في Development:**
```bash
php artisan queue:listen
```

**في Production (Supervisor):**
```ini
[program:laravel-worker]
command=php /path/to/artisan queue:work --sleep=3 --tries=3
```

## المراقبة والتتبع

### عرض الإشعارات المرسلة
```sql
-- جميع إشعارات اكتمال الكورسات
SELECT * FROM notifications 
WHERE type = 'App\\Notifications\\CourseCompletedNotification'
ORDER BY created_at DESC;

-- الإشعارات غير المقروءة
SELECT * FROM notifications 
WHERE type = 'App\\Notifications\\CourseCompletedNotification'
AND read_at IS NULL;
```

### Logs
```php
// في EnrollmentCourse::markAsCompleted()
\Log::info('Course completed notification sent', [
    'enrollment_course_id' => $this->id,
    'student_id' => $this->enrollment->student_id,
    'course_id' => $this->course_id,
]);
```

## الاختبار

### اختبار يدوي
1. أنشئ enrollment course بـ 9 جلسات من 10
2. أنشئ جلسة حضور للمجموعة والكورس
3. تحقق من:
   - تغيير status إلى completed
   - وصول إشعار في Database
   - وصول بريد إلكتروني

### اختبار تلقائي
```php
test('notification sent when course completes', function () {
    Notification::fake();
    
    // Create enrollment course at 9/10 sessions
    $enrollmentCourse = EnrollmentCourse::create([...]);
    
    // Create final session
    AttendanceSession::create([...]);
    
    // Assert notification sent
    Notification::assertSentTo(
        $enrollmentCourse->enrollment->student->user,
        CourseCompletedNotification::class
    );
});
```

## الأخطاء الشائعة

### 1. البريد لا يُرسل
✅ **الحل:**
- تحقق من إعدادات `.env` (MAIL_*)
- شغل queue worker
- تحقق من logs

### 2. الإشعار لا يظهر في Panel
✅ **الحل:**
- تحقق من أن `database` في `via()`
- تحقق من جدول `notifications`
- امسح cache

### 3. إشعارات مكررة
✅ **الحل:**
- تأكد من عدم استدعاء `markAsCompleted()` أكثر من مرة
- استخدم conditional check

## التطوير المستقبلي

يمكن إضافة:
- [ ] إشعار للمشرف
- [ ] إشعار للمدرس
- [ ] إشعار SMS
- [ ] Push Notification للموبايل
- [ ] إحصائيات عن الإشعارات المرسلة
- [ ] إمكانية تخصيص الإشعارات من Settings
