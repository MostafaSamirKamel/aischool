# تحديث السيرفر - Fix Graded Column Issue

قم بتنفيذ هذه الأوامر على السيرفر لحل مشكلة عمود graded:

```bash
# 1. الانتقال لمجلد المشروع
cd /path/to/your/project

# 2. سحب آخر التحديثات
git pull origin main

# 3. تشغيل migration الجديدة
php artisan migrate

# 4. تنظيف الكاش
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 5. إعادة تحميل opcache (إذا كان مفعل)
php artisan optimize:clear
```

## الملفات التي تم تعديلها:

1. **database/migrations/2025_12_31_222802_add_graded_column_to_assignment_submissions_table.php**
   - إضافة عمود `graded` boolean

2. **resources/views/filament/student/pages/my-assignments.blade.php**
   - تغيير من `->where('graded', true)` إلى `->where('status', 'graded')`

## ملاحظة مهمة:
الكود الآن يستخدم `status = 'graded'` بدلاً من `graded = true`، وهذا هو الصحيح لأن الجدول يستخدم عمود `status` من نوع enum.
