# معمارية نظام إدارة مدرسة البرمجة
# Programming School Management System Architecture

## 📋 نظرة عامة | Overview

نظام متعدد الفروع (Multi-Tenant) لإدارة مدرسة برمجة باستخدام:
- **Laravel 12** - Backend Framework
- **Filament 4** - Admin Panel & Resources
- **Multi-Panel Architecture** - Owner + Branch + Student Portal
- **Multi-Tenancy** - فصل بيانات كل فرع

---

## 🏗️ هيكل النظام | System Architecture

### 1. Multi-Panel Structure

```
┌─────────────────────────────────────────────┐
│           Owner/Admin Panel                 │
│  - إدارة جميع الفروع                       │
│  - التقارير المالية الموحدة               │
│  - تحويل الأموال بين الفروع               │
│  - إدارة المستخدمين على مستوى النظام      │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│           Branch Panel                      │
│  - مدير الفرع + سكرتارية + مدربين         │
│  - بيانات فرع واحد فقط                    │
│  - إدارة الطلاب والكورسات والحضور         │
│  - عرض الحسابات المالية (بدون تعديل)      │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│      Student/Parent Portal (خارج Filament) │
│  - تسجيل دخول بالموبايل                   │
│  - متابعة التقدم والحضور                  │
│  - رفع التسليمات                           │
│  - التقييمات                               │
└─────────────────────────────────────────────┘
```

### 2. Multi-Tenancy Strategy

**القرار الهندسي:**
```php
// Option A: Database per Branch (أقوى عزل)
tenant_abc.students
tenant_xyz.students

// Option B: Single DB + tenant_id (أسهل تجميعيًا)
students.tenant_id = 'branch_abc'
```

**التوصية:** Single DB + tenant_id
- سهولة التقارير الموحدة للـ Owner
- سهولة النسخ الاحتياطي
- أداء أفضل للتقارير المجمعة
- فصل منطقي كافٍ عبر Scopes & Policies

---

## 👥 الأدوار والصلاحيات | Roles & Permissions

### الأدوار الأساسية:

1. **Owner / Admin** 🔑
   - الوصول الكامل لجميع الفروع
   - إدارة المالية العامة
   - تحويل الأموال
   - إضافة/حذف الفروع

2. **Branch Manager** 👔
   - إدارة فرع واحد فقط
   - إدارة الطلاب والكورسات
   - إدارة الموظفين (مدربين/سكرتارية)
   - **عرض فقط** للحسابات المالية

3. **Instructor / Teacher** 👨‍🏫
   - إدارة جروباته فقط
   - تسجيل الحضور
   - رفع المواد
   - تقييم الطلاب

4. **Secretary** 📋
   - تسجيل الطلاب
   - إدارة المدفوعات
   - طباعة QR للطلاب
   - جدولة السيشنات

5. **Student** 🎓
   - دخول من Portal منفصل
   - متابعة التقدم
   - رفع التسليمات

6. **Parent** 👪
   - متابعة تقدم الطالب
   - عرض المدفوعات
   - تقييم المدرب/الفرع

---

## 📦 الموديلات الأساسية | Core Models

### Sprint 1-2: البنية الأساسية
```
Branch (الفروع)
├── id
├── name
├── code (unique)
├── type (physical/online)
├── status
└── settings (JSON)

User (المستخدمين)
├── id
├── branch_id (nullable for Owner)
├── name, email, password
├── role
└── timestamps
```

### Sprint 2: الكورسات والتراكات
```
Track (المسار التعليمي)
├── id, branch_id
├── name, description
└── order

Course (الكورس)
├── id, branch_id, track_id
├── name, description
├── duration_months
├── price
├── prerequisites (JSON)
└── status

Group (الجروب/المجموعة)
├── id, branch_id, course_id
├── instructor_id (nullable)
├── supervisor_id (nullable)
├── classroom_id
├── capacity, max_students
├── schedule (JSON: أيام وأوقات)
├── status (active/completed/cancelled)
└── timestamps

Classroom (القاعة)
├── id, branch_id
├── name, capacity
└── status
```

### Sprint 3: الطلاب
```
Student (الطالب)
├── id, branch_id
├── name, phone, email
├── parent_phone, parent_email
├── qr_code (unique)
├── barcode (unique)
└── status

StudentEnrollment (اشتراك الطالب)
├── id, student_id, course_id, group_id
├── enrolled_at
├── status (active/completed/suspended)
├── payment_plan (full/level/monthly)
└── progress (JSON)
```

### Sprint 4: الحضور والسيشنات
```
Session (الجلسة/المحاضرة)
├── id, group_id, branch_id
├── title, description
├── scheduled_at
├── started_at, ended_at
├── instructor_id
└── status

Attendance (الحضور)
├── id, session_id, student_id
├── status (present/absent/late)
├── scanned_at
├── method (qr/manual)
└── notes
```

### Sprint 5-6: المالية
```
Payment (المدفوعات)
├── id, student_id, branch_id
├── amount, paid_amount
├── payment_plan
├── due_date, paid_at
├── method (cash/card/online)
└── status

CashboxTransaction (حركة الخزنة)
├── id, branch_id, cashbox_id
├── type (income/expense/transfer)
├── amount
├── category
├── related_type (Payment/Expense/Salary)
├── related_id
└── created_by

Expense (المصروفات)
├── id, branch_id
├── category
├── amount
├── description
├── approved_by
└── paid_at

InstructorPayment (أجور المدربين)
├── id, instructor_id, branch_id
├── month, year
├── sessions_count
├── amount_due
├── withdrawals (JSON)
├── bonuses, deductions
└── status
```

### Sprint 7: المواد والتقييمات
```
Material (المواد التعليمية)
├── id, course_id, group_id
├── title, type (video/pdf/link)
├── file_path
└── uploaded_by

Submission (التسليم)
├── id, student_id, material_id
├── file_path
├── submitted_at
├── grade, feedback
└── graded_by

Rating (التقييم)
├── id, student_id, branch_id
├── rateable_type (Instructor/Branch/Secretary)
├── rateable_id
├── rating (1-5)
├── comment
└── created_at
```

---

## 🔐 Policies & Scopes

### Branch Scoping (التصفية حسب الفرع)

```php
// Global Scope for all tenant models
class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (auth()->user()?->isOwner()) {
            return; // Owner sees all
        }
        
        $builder->where('branch_id', auth()->user()->branch_id);
    }
}

// Apply to all tenant models
protected static function booted()
{
    static::addGlobalScope(new BranchScope());
}
```

### Policy Example

```php
class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_students');
    }
    
    public function view(User $user, Student $student): bool
    {
        if ($user->isOwner()) return true;
        
        return $student->branch_id === $user->branch_id;
    }
    
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['owner', 'branch_manager', 'secretary']);
    }
}
```

---

## 🎯 خطة التنفيذ | Implementation Roadmap

### Sprint 0: Setup ✅
- ✅ Laravel 12 installed
- ✅ Filament 4 installed
- 🔲 Multi-Panel setup
- 🔲 Tenancy system
- 🔲 GitHub Copilot prompts template

### Sprint 1: Branches + Users + Roles (أسبوع 1)
- Models: Branch, User
- Roles: Owner, BranchManager, Instructor, Secretary, Student, Parent
- Policies: Branch scoping
- Filament: BranchResource, UserResource
- Tests: Policy tests, Feature tests

### Sprint 2: Courses + Tracks + Groups + Classrooms (أسبوع 2)
- Models: Track, Course, Group, Classroom
- Validations: منع التعارض
- Filament: Track/Course/Group/Classroom Resources
- Tests: Conflict detection

### Sprint 3: Students + QR (أسبوع 3)
- Models: Student, StudentEnrollment
- QR/Barcode generation
- Filament: StudentResource (with tabs)
- Tests: Enrollment flow

### Sprint 4: Sessions + Attendance (أسبوع 4)
- Models: Session, Attendance
- QR scanning endpoint
- Filament: SessionResource, Attendance reports
- Tests: Attendance flow

### Sprint 5: Payments + Cashbox (أسبوع 5)
- Models: Payment, CashboxTransaction, Expense
- Payment plans logic
- Filament: Payment/Cashbox/Expense Resources
- Tests: Payment calculations

### Sprint 6: Instructor Payroll (أسبوع 6)
- Models: InstructorPayment
- Salary calculation service
- Filament: Payroll page
- Tests: Salary calculations

### Sprint 7: Materials + Submissions + Ratings + Portal (أسبوعين)
- Models: Material, Submission, Rating
- Student Portal (خارج Filament)
- Parent Portal
- Tests: End-to-end user flows

---

## 🛠️ تقنيات ومكتبات | Tech Stack

### المثبت بالفعل:
- Laravel 12
- Filament 4
- Spatie Laravel Permission (للصلاحيات)
- Laravel Activity Log

### سيتم تثبيته:
```bash
# QR Code generation
composer require simplesoftwareio/simple-qrcode

# Excel/PDF exports
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf

# SMS/WhatsApp (اختياري)
composer require twilio/sdk
# أو
composer require ultramsg/whatsapp-php-sdk
```

---

## 📊 Database Design Decisions

### ✅ Single Database + tenant_id
**مميزات:**
- تقارير موحدة سهلة للـ Owner
- نسخ احتياطي واحد
- Migrations أسهل
- أداء أفضل للـ Joins عبر الفروع

**عيوب:**
- يحتاج Scopes دقيقة
- خطر تسريب بيانات (يُحل بـ Policies + Tests)

### ❌ Database per Tenant (غير مُختار)
**مميزات:**
- عزل فيزيائي كامل

**عيوب:**
- تقارير موحدة معقدة
- Migrations لكل فرع
- نسخ احتياطي متعدد

---

## 🚀 Next Steps

1. ✅ قراءة هذا الملف
2. 🔲 إعداد Multi-Panel (ملف منفصل)
3. 🔲 إعداد Tenancy System
4. 🔲 إنشاء GitHub Copilot Prompts Template
5. 🔲 بدء Sprint 1

---

## 📝 Notes for GitHub Copilot

عند استخدام Copilot، استخدم هذا الـ Prompt:

```
Implement [Feature Name] for Programming School System:
- Laravel 12 + Filament 4
- Multi-tenant (single DB + branch_id)
- Role-based access: Owner, BranchManager, Instructor, Secretary, Student, Parent
- Owner sees all branches, others see own branch only
- Include: Migration, Model, Policy, Filament Resource, Form validation, Tests

Requirements:
[الصق المتطلبات من الملف]

Generate complete working code with tests.
```

---

**آخر تحديث:** 2025-12-18  
**الحالة:** 🟡 Sprint 0 في التنفيذ
