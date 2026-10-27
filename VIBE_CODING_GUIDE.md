# 🤖 دليل التطوير باستخدام Vibe Coding
# Development Guide with Vibe Coding (GitHub Copilot + VS Code)

هذا الدليل يشرح كيفية استخدام GitHub Copilot بشكل فعّال في VS Code لتسريع تطوير نظام إدارة مدرسة البرمجة.

---

## 🎯 فلسفة Vibe Coding

**Vibe Coding** = التطوير السريع باستخدام:
- ✅ **GitHub Copilot** للاقتراحات الذكية
- ✅ **Templates واضحة** لتوجيه الـ AI
- ✅ **Context كافٍ** في التعليقات
- ✅ **مراجعة بشرية** دقيقة للكود المُولّد

---

## 🛠️ الإعداد | Setup

### 1. تثبيت GitHub Copilot

1. افتح VS Code
2. اذهب لـ Extensions (`Ctrl+Shift+X`)
3. ابحث عن "GitHub Copilot"
4. اضغط Install
5. سجل دخول بحساب GitHub الخاص بك

### 2. تفعيل Copilot للـ PHP

تأكد من أن Copilot مُفعّل لملفات PHP:

1. `Ctrl+Shift+P`
2. اكتب: `Preferences: Open User Settings (JSON)`
3. أضف:

```json
{
  "github.copilot.enable": {
    "*": true,
    "php": true,
    "blade": true,
    "markdown": true
  }
}
```

---

## 📝 كيفية استخدام القوالب | Using Templates

### الطريقة 1: Inline Comments (الأسرع)

**مثال - إنشاء Model:**

```php
<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

// Create Student model for Programming School System
// Multi-tenant (uses BelongsToBranch trait)
// Fields: tenant_id, name, phone, email, parent_name, parent_phone, parent_email, qr_code, barcode, status
// Relationships: branch (BelongsTo), enrollments (HasMany), attendances (HasMany), payments (HasMany)
// Casts: status as string
// Fillable: all except id
// Soft deletes

class Student extends Model
{
    // اضغط Enter هنا - Copilot سيكمل تلقائيًا
```

**Copilot سيولّد:**

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'name',
        'phone',
        'email',
        'parent_name',
        'parent_phone',
        'parent_email',
        'qr_code',
        'barcode',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
```

---

### الطريقة 2: Multi-line Comment Block (الأدق)

انسخ القالب من `COPILOT_PROMPTS_TEMPLATE.md`:

```php
<?php

/*
Create a Filament 4 Resource for Student in Branch Panel.

Context:
- Multi-tenant system (branch scoped)
- Current panel: BranchPanel
- User roles: BranchManager, Secretary, Instructor

Requirements:
- Table with columns:
  * Name (searchable, sortable)
  * Phone (searchable)
  * Email
  * Status (badge: active=success, inactive=danger, suspended=warning)
  * QR Code (download action)
  * Created at (date)

- Filters:
  * Status (active/inactive/suspended)
  * Date range (created_at)

- Form with:
  * Name (required)
  * Phone (required, tel)
  * Email (email)
  * Parent name
  * Parent phone (tel)
  * Parent email (email)
  * Status (select: active/inactive/suspended)

- Actions:
  * View
  * Edit (BranchManager, Secretary)
  * Delete (BranchManager only)
  * Generate QR Code (custom action)

- Query scoping: automatic via BranchScope
- Authorization: use StudentPolicy

Generate complete Resource class.
*/

namespace App\Filament\Branch\Resources;

use App\Models\Student;
use Filament\Resources\Resource;

class StudentResource extends Resource
{
    // اضغط Enter هنا - Copilot سيكمل كل الـ Resource
```

---

## 🎨 أمثلة عملية | Practical Examples

### مثال 1: إنشاء Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Create students table for Programming School System
// Columns: id, tenant_id (foreign to tenants), name, phone, email, parent_name, parent_phone, parent_email, qr_code (unique), barcode (unique), status (enum), timestamps, soft deletes
// Indexes: tenant_id, qr_code, barcode, status

return new class extends Migration
{
    public function up(): void
    {
        // Copilot سيكمل هنا
```

---

### مثال 2: إنشاء Policy

```php
<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

// Policy for Student model
// Owner: full access to all branches
// BranchManager: full access to own branch students
// Secretary: create and view own branch students
// Instructor: view only own group students (implemented later)
// Student/Parent: no access via admin panel

class StudentPolicy
{
    // Copilot سيكمل كل الـ methods
```

---

### مثال 3: إنشاء Service Class

```php
<?php

namespace App\Services;

use App\Models\Student;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;

// QrCodeService for generating and printing student QR codes
// Methods:
// - generateQrCode(Student $student): string - generates QR code and saves to storage
// - generateBarcode(Student $student): string - generates barcode
// - printQrCard(Student $student): PDF - generates printable card with student info + QR

class QrCodeService
{
    // Copilot سيكمل كل الـ class
```

---

## 🚀 سير العمل الموصى به | Recommended Workflow

### 1. **التخطيط** (5 دقائق)

اقرأ المتطلبات من:
- `ARCHITECTURE_PLAN.md` - لمعرفة الهيكل
- `Programming School System Details.pdf` - للمتطلبات التفصيلية

### 2. **كتابة الـ Prompt** (2 دقيقة)

انسخ القالب المناسب من `COPILOT_PROMPTS_TEMPLATE.md` وعدّله حسب الحاجة.

### 3. **توليد الكود** (30 ثانية)

الصق الـ Prompt كتعليق في الملف واترك Copilot يكمل.

### 4. **المراجعة والتعديل** (3 دقائق)

- ✅ تأكد من صحة الـ namespaces
- ✅ تأكد من استخدام الـ Traits الصحيحة
- ✅ تأكد من الـ Authorization (Policies)
- ✅ أضف Arabic labels
- ✅ عدّل الـ validation rules إذا لزم

### 5. **الاختبار** (5 دقائق)

اكتب Feature Test أو اختبر يدويًا في المتصفح.

---

## 💡 نصائح لنتائج أفضل | Tips for Better Results

### ✅ **افعل:**

1. **كن محددًا جدًا** في الـ Prompts
   ```php
   // ✅ جيد
   // Create Student model with tenant_id, name, phone, email, soft deletes, BelongsToBranch trait
   
   // ❌ سيء
   // Create Student model
   ```

2. **اذكر السياق دائمًا**
   ```php
   // Multi-tenant system (branch scoped)
   // Role-based access: Owner, BranchManager, Secretary
   ```

3. **اطلب Tests**
   ```php
   // Also generate Feature test covering CRUD operations
   ```

4. **استخدم أمثلة**
   ```php
   // Example: Student::create(['name' => 'أحمد', 'tenant_id' => 'branch_1'])
   ```

### ❌ **لا تفعل:**

1. **لا تقبل الكود أعمى** - راجع دائمًا
2. **لا تنسَ الـ Authorization** - Policies مهمة جدًا
3. **لا تعتمد 100% على Copilot** - استخدم عقلك
4. **لا تكتب Prompts غامضة** - كن واضحًا

---

## 🎯 Prompts جاهزة للاستخدام الفوري

### Prompt 1: إنشاء Model كامل

```php
// Create [MODEL_NAME] model for Programming School System
// Multi-tenant (uses BelongsToBranch trait)
// Table: [table_name]
// Columns: [list all columns with types]
// Relationships: [list relationships with types]
// Casts: [list casts]
// Fillable: all except id
// Soft deletes: [yes/no]
// Special methods: [list any custom methods needed]
```

---

### Prompt 2: إنشاء Filament Resource

```php
/*
Create Filament Resource for [MODEL_NAME] in [PANEL_NAME] Panel

Table columns:
- [COLUMN1]: [type], [searchable/sortable]
- [COLUMN2]: ...

Filters:
- [FILTER1]: [type]
- [FILTER2]: ...

Form fields:
- [FIELD1]: [type], [validation]
- [FIELD2]: ...

Actions:
- [ACTION1]: [who can perform]
- [ACTION2]: ...

Authorization: use [MODEL_NAME]Policy
Query scoping: [automatic via BranchScope / withoutBranchScope for Owner]
*/
```

---

### Prompt 3: إنشاء Migration

```php
// Create [table_name] table migration
// Columns:
// - id (primary key)
// - tenant_id (foreign key to tenants table, indexed)
// - [COLUMN1]: [type], [nullable/unique/default]
// - [COLUMN2]: ...
// - timestamps
// - soft deletes
// Indexes: [list indexes]
// Foreign keys: [list with cascade behavior]
```

---

### Prompt 4: إنشاء Policy

```php
// Policy for [MODEL_NAME]
// Owner: [describe permissions]
// BranchManager: [describe permissions]
// [ROLE3]: [describe permissions]
// Special rules: [any additional checks, e.g., can't delete if has children]
// Branch scoping: automatic check for non-Owner users
```

---

### Prompt 5: إنشاء Feature Test

```php
/*
Feature Test for [FEATURE_NAME]

Scenarios:
1. [SCENARIO1] - as [ROLE] - expected: [OUTCOME]
2. [SCENARIO2] - as [ROLE] - expected: [OUTCOME]
3. Unauthorized access - as [ROLE] - expected: 403
4. Cross-branch access - as BranchManager - expected: 404/403

Setup:
- Create test branches
- Create test users with roles
- Create test data

Use RefreshDatabase trait
*/
```

---

## 🧪 اختبار Copilot | Testing Copilot

جرب هذا الـ Prompt للتأكد من أن Copilot شغال صح:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Create a simple TestModel with name, email, status fields
// Fillable: name, email, status
// Casts: status as string

class TestModel extends Model
{
    // إذا Copilot أكمل الكود صح، يبقى جاهز!
```

---

## 📊 مقارنة الأداء | Performance Comparison

| الطريقة | الوقت | الدقة | الملاحظات |
|---------|------|-------|-----------|
| **كتابة يدوية** | 30 دقيقة | 95% | بطيء لكن دقيق |
| **Copilot بدون Prompts** | 10 دقائق | 60% | سريع لكن غير دقيق |
| **Copilot + Prompts واضحة** | 5 دقائق | 90% | ⭐ **الأفضل** |
| **Copy/Paste من مشاريع قديمة** | 15 دقيقة | 70% | يحتاج تعديل كثير |

---

## 🎓 تمارين عملية | Practical Exercises

### تمرين 1: إنشاء Course Model

استخدم Copilot لإنشاء:
1. Migration لـ courses table
2. Course Model مع BelongsToBranch
3. CoursePolicy
4. CourseResource في Branch Panel
5. Feature Test

**الوقت المتوقع:** 15 دقيقة

---

### تمرين 2: إنشاء Attendance System

استخدم Copilot لإنشاء:
1. Attendance Model + Migration
2. AttendancePolicy
3. Session Model + Migration
4. AttendanceResource مع filters
5. Custom action: "تسجيل حضور"

**الوقت المتوقع:** 30 دقيقة

---

## 🔥 Advanced Tips

### 1. استخدام Copilot Chat

اضغط `Ctrl+I` لفتح Copilot Chat:

```
@workspace How do I implement QR code scanning for attendance in Laravel?
```

### 2. توليد Documentation

```php
/**
 * Generate this docblock for me
 */
public function calculateInstructorSalary(Instructor $instructor, int $month, int $year)
{
    // Copilot سيولد الـ docblock كامل
```

### 3. Refactoring

اختار الكود واضغط `Ctrl+I`:

```
Refactor this method to use a Service class
```

---

## 📚 موارد إضافية | Additional Resources

- [Filament Documentation](https://filamentphp.com/docs)
- [Laravel Documentation](https://laravel.com/docs)
- [GitHub Copilot Tips](https://github.com/features/copilot)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)

---

## 🎯 الخلاصة | Summary

**Vibe Coding = سرعة + دقة + جودة**

باستخدام:
- ✅ GitHub Copilot الذكي
- ✅ Prompts واضحة من `COPILOT_PROMPTS_TEMPLATE.md`
- ✅ مراجعة بشرية دقيقة
- ✅ Tests شاملة

يمكنك تطوير النظام بسرعة **3-5x** مقارنة بالكتابة اليدوية!

---

**آخر تحديث:** 2025-12-18  
**الحالة:** ✅ جاهز للاستخدام

**Happy Coding with Vibe! 🚀🤖**
