# حل مشكلة Cache Path على السيرفر
## Fix Laravel Cache Path Error on Server

## المشكلة / Problem
```
InvalidArgumentException
Please provide a valid cache path.
```

## الحل السريع / Quick Fix

### الطريقة 1: استخدام SSH (Method 1: Using SSH)

```bash
# 1. اتصل بالسيرفر عن طريق SSH
# Connect to your server via SSH

# 2. انتقل لمجلد المشروع
# Navigate to project directory
cd /path/to/your/project

# 3. أنشئ المجلدات المطلوبة
# Create required directories
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# 4. اضبط الصلاحيات
# Set permissions
chmod -R 775 storage bootstrap/cache

# 5. امسح الكاش
# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### الطريقة 2: استخدام السكريبت المتوفر (Method 2: Using Provided Script)

```bash
# ارفع ملف fix_cache_path_error.sh للسيرفر ثم نفذ
# Upload fix_cache_path_error.sh to server then execute:
bash fix_cache_path_error.sh
```

### الطريقة 3: عن طريق PHP (Method 3: Using PHP)

```bash
# ارفع ملف fix_cache_path.php للسيرفر ثم نفذ
# Upload fix_cache_path.php to server then execute:
php fix_cache_path.php
```

أو افتح الملف من المتصفح:
Or open from browser:
```
http://yourdomain.com/fix_cache_path.php
```

## المجلدات المطلوبة / Required Directories

```
storage/
├── framework/
│   ├── cache/
│   │   └── data/
│   ├── sessions/
│   ├── views/
│   └── testing/
├── logs/
└── app/

bootstrap/
└── cache/
```

## الصلاحيات المطلوبة / Required Permissions

- **Directories**: 775 (rwxrwxr-x)
- **Files**: 664 (rw-rw-r--)

## للسيرفرات المشتركة cPanel / For cPanel Shared Hosting

1. افتح File Manager من cPanel
2. انتقل لمجلد المشروع
3. أنشئ المجلدات المطلوبة
4. اضبط الصلاحيات (Right Click > Change Permissions)
   - storage: 755
   - bootstrap/cache: 755
5. ارفع ملف fix_cache_path.php وشغله من المتصفح

**English:**
1. Open File Manager from cPanel
2. Navigate to project folder
3. Create required directories
4. Set permissions (Right Click > Change Permissions)
   - storage: 755
   - bootstrap/cache: 755
5. Upload fix_cache_path.php and run from browser

## التحقق من الإصلاح / Verify Fix

```bash
# تحقق من وجود المجلدات
# Check if directories exist
ls -la storage/framework/

# تحقق من الصلاحيات
# Check permissions
ls -la storage/

# تحقق من إمكانية الكتابة
# Test write permissions
php -r "var_dump(is_writable('storage/framework/views'));"
```

## مشاكل إضافية محتملة / Additional Possible Issues

### 1. SELinux (للسيرفرات Linux)

```bash
# إذا كان SELinux مفعل
# If SELinux is enabled
sudo setsebool -P httpd_unified 1
sudo chcon -R -t httpd_sys_rw_content_t storage/ bootstrap/cache/
```

### 2. ملف .env غير موجود / Missing .env File

```bash
# انسخ ملف .env.example
# Copy .env.example
cp .env.example .env

# اضبط الإعدادات
# Configure settings
nano .env
```

### 3. مشاكل الصلاحيات المستمرة / Persistent Permission Issues

```bash
# إذا كنت تستخدم سيرفر خاص
# If using VPS/dedicated server
sudo chown -R www-data:www-data storage bootstrap/cache
# أو / or
sudo chown -R apache:apache storage bootstrap/cache
# أو استخدم اسم المستخدم الخاص بك / or use your username
sudo chown -R yourusername:yourusername storage bootstrap/cache
```

## بعد الإصلاح / After Fix

```bash
# امسح كل الكاش
# Clear all caches
php artisan optimize:clear

# أعد تحسين الأداء (للإنتاج فقط)
# Optimize for production (production only)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## الملفات المساعدة / Helper Files

1. **fix_cache_path_error.sh** - سكريبت Bash للسيرفرات مع SSH
   Bash script for servers with SSH access
   
2. **fix_cache_path.php** - سكريپت PHP للسيرفرات المشتركة
   PHP script for shared hosting

3. **fix_server_permissions.sh** - سكريبت شامل للصلاحيات
   Comprehensive permissions script

## جهات الاتصال للدعم / Support Contacts

إذا استمرت المشكلة:
If the problem persists:

1. تحقق من سجلات الأخطاء / Check error logs:
   - `/storage/logs/laravel.log`
   - Server error logs (Apache/Nginx)

2. تواصل مع مزود الاستضافة / Contact hosting provider

3. تحقق من إصدار PHP (يجب أن يكون 8.1 أو أحدث)
   Check PHP version (must be 8.1 or higher)

---

## ملاحظات مهمة / Important Notes

⚠️ **لا تنسى حذف ملفات الإصلاح بعد الاستخدام**
**Don't forget to delete fix files after use**

```bash
rm fix_cache_path.php
rm fix_cache_path_error.sh
```

✅ **تأكد من عمل نسخة احتياطية قبل أي تغييرات**
**Make sure to backup before any changes**

```bash
tar -czf backup-$(date +%Y%m%d).tar.gz storage/ bootstrap/cache/ .env
```
