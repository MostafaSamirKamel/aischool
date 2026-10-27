# إصلاح مشكلة Component و Payment Query على السيرفر

## المشاكل المحلولة
1. ❌ `Unable to find component: [App\Filament\Parent\Widgets\ChildrenSelectorWidget]`
2. ❌ `Column not found: student_id in payments table`

## الحل
قم بتشغيل هذه الأوامر على السيرفر:

```bash
# الانتقال إلى مجلد المشروع
cd /path/to/your/project

# سحب آخر التحديثات من Git (commit 2c8f6b1)
git pull origin main

# إعادة بناء autoloader
composer dump-autoload -o

# مسح جميع الـ caches
php artisan optimize:clear

# مسح cache الـ Livewire بشكل منفصل
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# إعادة بناء الـ cache للإنتاج (اختياري)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# إعادة تشغيل queue workers إذا كانت مفعلة
php artisan queue:restart
```

## ملاحظات
- تأكد من أن الملفات التالية موجودة على السيرفر:
  - `app/Filament/Parent/Widgets/ChildrenSelectorWidget.php`
  - `app/Providers/Filament/ParentPanelProvider.php`
- تأكد من صلاحيات الملفات (755 للمجلدات، 644 للملفات)
- بعد التنفيذ، جرب فتح لوحة الأهل مرة أخرى

## تحقق من النتيجة
بعد تنفيذ الأوامر، افتح:
https://aischool.almobtakeralaraby.com.eg/parent

يجب أن تعمل لوحة الأهل بدون أخطاء.
