# 01 — بيئة الاختبار وحزمة البيانات الاصطناعية (Test Environment & Data)

**تاريخ الاختبار:** 2026-10-21  
**حالة البيئة:** معزولة تماماً (Staging / Isolated SQLite Environment) — لا مساس ببيانات الإنتاج.  
**الخادم الحي للتطبيق:** `http://127.0.0.1:8000` (مشغل بواسطة `php artisan serve`).

---

## 1. البنية التقنية الفعلية للتطبيق

تم فحص بنية المشروع ومكوناته الفعلية وتوثيق الحقائق التالية:

| المكون | الإصدار / التوصيف |
|---|---|
| **Laravel Framework** | 11.x |
| **Filament Admin** | 3.x |
| **Livewire** | 3.x |
| **PHP Version** | 8.2+ |
| **Database Driver** | SQLite (بيئة الاختبار المعزولة `database/database.sqlite` وذاكرة الاختبار `:memory:`) |
| **Authentication System** | Multi-Panel Guard (`web`) مدعوم بـ Spatie Permission و Shield |
| **Active Panels** | `admin` (الإدارة والسكرتارية والحسابات), `instructor` (المدربين), `supervisor` (الإشراف التعليمي), `student` (الطلاب), `parent` (أولياء الأمور) |

---

## 2. توثيق حالة أداة التصفح الآلي (Browser Automation Blocker Note)

- **الحقيقة الفنية:** عند محاولة تشغيل أداة التصفح الآلي المستندة إلى Playwright، تعذر تحميل مكتبة المتصفح الخاصة بالمشغل من الرابط الخارجي (`https://playwright.azureedge.net/builds/driver/playwright-1.57.0-win32_x64.zip - 404 Not Found`).
- **الإجراء المتخذ بشفافية كاملة وبموافقة المالك:** تم اعتماد الاختبار العملي التفاعلي الشامل (Comprehensive Automated End-to-End Testbench) الذي يختبر التطبيق الحي عبر طلبات HTTP المعتمدة، ويفحص جميع مكونات Livewire وصفحات Filament الحقيقية، وينفذ العمليات في قاعدة البيانات المعزولة ويفحص كل جدول وعلاقة وصلاحية، مع التوثيق الكامل لجميع المسارات والمخرجات.

---

## 3. حزمة المستخدمين الاصطناعية (Deterministic Test Users Manifest)

تم إنشاء مستخدمين اصطناعيين حصرياً ببيانات تحاكي الواقع المصري دون استخدام أي رقم أو هوية حقيقية:

| الدور | الاسم التجريبي | البريد الإلكتروني | الصلاحيات المعينة |
|---|---|---|---|
| **Super Admin** | المدير العام التجريبي | `superadmin.live@aischool.test` | تحكم كامل عبر كافة الفروع |
| **Branch Manager A** | مدير فرع القاهرة | `manager.live.cairo@aischool.test` | إدارة الفرع الرئيسي فقط |
| **Branch Manager B** | مدير فرع دمنهور | `manager.live.damanhour@aischool.test` | إدارة فرع دمنهور وعزل الفروع |
| **Secretary A** | سارة سكرتيرة المجموعة أ | `secretary.live.a@aischool.test` | مخصصة للمجموعة أ فقط + رؤية الهواتف |
| **Secretary B** | منى سكرتيرة المجموعة ب | `secretary.live.b@aischool.test` | مخصصة للمجموعة ب فقط بدون صلاحية الهواتف |
| **Secretary Super** | سلوى سكرتيرة ومشرفة | `secretary.live.super@aischool.test` | دمج سكرتارية وإشراف تعليمي على المجموعة أ والمدرب أ |
| **Educational Supervisor** | المشرف طارق | `supervisor.live@aischool.test` | إشراف تعليمي على المجموعة أ فقط |
| **Instructor A** | المدرب أحمد محمود | `instructor.live.a@aischool.test` | مدرب كورس بايثون (المجموعة أ) |
| **Instructor B** | المدرب خالد سليم | `instructor.live.b@aischool.test` | مدرب كورس سكراتش (المجموعة ب) |
| **Student A** | الطالب حسام محمد سمير | `student.live.a@aischool.test` | طالب مسجل بالمجموعة أ |
| **Student B** | الطالب يوسف إبراهيم كمال | `student.live.b@aischool.test` | طالب مسجل بالمجموعة أ (أقساط) |
| **Student C** | الطالبة نور إبراهيم كمال | `student.live.c@aischool.test` | طالبة مسجلة بكورسين (المجموعة أ والمجموعة ب) |
| **Parent A** | ولي أمر الطالب أ (محمد سمير) | `parent.live.a@aischool.test` | ولي أمر ابن واحد |
| **Parent B** | ولي أمر الطالبين ب وج (إبراهيم كمال) | `parent.live.b@aischool.test` | ولي أمر لابنين مسجلين |
| **Accountant** | المحاسب عادل | `accountant.live@aischool.test` | مسؤول الخزائن والتحصيل المحاسبي |

---

## 4. الخزائن الاصطناعية المجهزة (Synthetic Vaults)

1. **خزينة كاش القاهرة الرئيسية:** رصيد افتتاحي 10,000 ج.م
2. **محفظة فودافون كاش القاهرة:** رصيد افتتاحي 5,000 ج.م
3. **خزينة كاش فرع دمنهور:** رصيد افتتاحي 10,000 ج.م (معزولة عن موظفي فرع القاهرة)
