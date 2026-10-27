# 🚀 دليل استخدام Laravel مع Laragon

## ✅ تم الإعداد بنجاح!

---

## 📋 الحلول المتاحة لاستخدام Artisan:

### الحل 1️⃣: استخدام `artisan.bat` (الأسهل)
```powershell
.\artisan.bat migrate
.\artisan.bat tinker
.\artisan.bat queue:work
.\artisan.bat make:model MyModel
```

### الحل 2️⃣: إضافة PHP للـ PATH (مؤقت - للجلسة الحالية فقط)
```powershell
$env:Path = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;" + $env:Path
```
بعدها:
```powershell
php artisan migrate
php artisan tinker
```

### الحل 3️⃣: إضافة PHP للـ PATH (دائم)
شغّل هذا الملف **مرة واحدة فقط**:
```powershell
.\setup-php-path.ps1
```
ثم أعد تشغيل PowerShell.

بعدها استخدم `php` مباشرة:
```powershell
php artisan migrate
php artisan serve
php artisan queue:work
```

---

## 🎯 الأوامر الشائعة:

### تشغيل Migrations + Seeders
```powershell
php artisan migrate --seed
```

### تشغيل السيرفر
```powershell
php artisan serve
```

### تشغيل Queue Worker
```powershell
php artisan queue:work
```

### فتح Tinker (للاختبار)
```powershell
php artisan tinker
```

### إنشاء Model جديد
```powershell
php artisan make:model MyModel -mfc
```

---

## 📱 نظام WhatsApp - جاهز للاستخدام!

### الخطوات:
1. احصل على بيانات SOG API من: https://sender.whatsapp.sog.eg
2. أضف في `.env`:
```env
WHATSAPP_DEFAULT_DRIVER=sog
SOG_WHATSAPP_API_KEY=your_api_key
SOG_WHATSAPP_SENDER=201012345678
```

3. اختبر:
```powershell
php artisan tinker
```
```php
app(\App\Services\WhatsAppService::class)->send('+201012345678', 'مرحباً!');
```

### التوثيق الكامل:
- 📖 [WHATSAPP_SYSTEM_GUIDE.md](WHATSAPP_SYSTEM_GUIDE.md)
- 📄 [WHATSAPP_READY.md](WHATSAPP_READY.md)

---

## ✨ الملفات المساعدة:

| الملف | الوصف |
|-------|-------|
| `artisan.bat` | تشغيل artisan مباشرة |
| `setup-php-path.ps1` | إضافة PHP للـ PATH بشكل دائم |
| `WHATSAPP_SYSTEM_GUIDE.md` | دليل نظام WhatsApp الشامل |

---

**✅ كل شيء جاهز! استمتع بالتطوير! 🚀**
