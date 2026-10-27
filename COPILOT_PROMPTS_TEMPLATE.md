# قوالب Prompts لـ GitHub Copilot
# GitHub Copilot Prompts Templates

هذا الملف يحتوي على قوالب جاهزة لمخاطبة GitHub Copilot في VS Code أثناء تطوير النظام.

---

## 📋 Template 1: إنشاء Model + Migration

```
Create a Laravel Model for [MODEL_NAME] in Programming School System.

Context:
- Multi-tenant system (single DB + branch_id for tenant isolation)
- Role-based access: Owner (all branches), BranchManager (own branch), Instructor, Secretary, Student, Parent

Requirements:
- Migration with proper foreign keys and indexes
- Model with:
  * BranchScope global scope (if tenant model)
  * Relationships (specify: HasMany, BelongsTo, etc.)
  * Fillable/guarded
  * Casts (JSON fields, dates, etc.)
  * Accessors/Mutators if needed
- Policy with:
  * viewAny: check permission
  * view: check branch ownership (except Owner)
  * create/update/delete: role-based
- Factory for testing
- Feature test covering CRUD

Model Details:
[الصق تفاصيل الموديل هنا]

Generate complete code with proper Arabic comments where helpful.
```

---

## 📋 Template 2: إنشاء Filament Resource

```
Create a Filament 4 Resource for [MODEL_NAME].

Context:
- Multi-panel system: OwnerPanel (sees all), BranchPanel (filtered by branch)
- Current panel: [PANEL_NAME]
- User roles: Owner, BranchManager, Instructor, Secretary

Requirements:
- Resource with:
  * Table columns (with sortable/searchable where appropriate)
  * Filters (branch filter for Owner panel, date ranges, status, etc.)
  * Actions (view, edit, delete)
  * Bulk actions if applicable
- Form with:
  * All required fields with validation
  * Relationship selects (filtered by current branch)
  * Conditional fields based on role/status
  * Arabic labels
- Tabs in ViewRecord if model has related data
- Query scoping: Owner sees all, others see own branch
- Authorization via Policy

Resource Details:
Model: [MODEL_NAME]
Key fields: [FIELD1, FIELD2, ...]
Relationships: [RELATION1, RELATION2, ...]
Special validations: [VALIDATION_RULES]

Generate complete Resource class with proper authorization.
```

---

## 📋 Template 3: إنشاء Service/Action Class

```
Create a Service class for [FUNCTIONALITY_NAME] in Programming School System.

Context:
- Laravel 12, following SOLID principles
- Multi-tenant: always work within user's branch (except Owner)

Requirements:
- Service class with clear responsibility
- Methods:
  [METHOD1: description]
  [METHOD2: description]
- Proper exception handling
- Return consistent results (DTO or array)
- Transaction handling where needed
- Activity logging for important actions
- Unit tests covering all methods

Service Details:
[الصق تفاصيل الوظيفة هنا]

Example usage:
[كود مثال للاستخدام]

Generate complete service with tests.
```

---

## 📋 Template 4: Validation Rules معقدة

```
Create validation rules for [FORM_NAME] in Programming School System.

Context:
- Laravel 12 validation
- Multi-tenant: validate within branch scope
- Current user role: [ROLE]

Requirements:
Custom validation rules for:
1. [RULE1]: description, conditions, error message in Arabic
2. [RULE2]: description, conditions, error message in Arabic
3. ...

Create:
- Custom Rule classes (Rule::unique()->where('branch_id', ...), custom rules)
- FormRequest class if complex
- Tests covering validation cases

Validation Details:
[الصق التفاصيل هنا]

Generate complete validation logic with tests.
```

---

## 📋 Template 5: إنشاء Feature Test

```
Create a Feature Test for [FEATURE_NAME].

Context:
- Laravel 12 + Filament 4
- Multi-tenant system with roles
- Testing as: [ROLE]

Test Scenarios:
1. [SCENARIO1]: Expected outcome
2. [SCENARIO2]: Expected outcome (should fail with 403/404)
3. [SCENARIO3]: Expected outcome
4. ...

Requirements:
- Use factories for test data
- Test within proper branch context
- Test authorization (403 for unauthorized)
- Test validation errors
- Test successful outcomes
- Use Filament Livewire testing if testing resources

Feature Details:
[الصق تفاصيل الميزة هنا]

Generate complete test class with proper assertions.
```

---

## 📋 Template 6: Policy Class

```
Create a Policy for [MODEL_NAME] in Programming School System.

Roles & Permissions:
- Owner: full access to all branches
- BranchManager: full access to own branch
- Instructor: [specific permissions]
- Secretary: [specific permissions]
- Student/Parent: [specific permissions]

Policy Methods Needed:
- viewAny: [WHO can list]
- view: [WHO can view individual + branch check]
- create: [WHO can create]
- update: [WHO can update + branch check]
- delete: [WHO can delete + additional checks]
- [CUSTOM_METHOD]: [description]

Special Rules:
[أي قواعد خاصة، مثل: لا يمكن حذف جروب به طلاب]

Generate complete Policy with proper branch scoping.
```

---

## 📋 Template 7: Global Scope للـ Tenancy

```
Create a Global Scope for multi-tenant branch filtering.

Requirements:
- Scope name: BranchScope
- Apply to models with 'branch_id' column
- Logic:
  * If user is Owner: no filtering (sees all branches)
  * Otherwise: filter by auth()->user()->branch_id
  * Handle unauthenticated state gracefully
- Provide withoutBranchScope() method for Owner queries
- Document usage in Model

Generate complete Scope class with usage example.
```

---

## 📋 Template 8: Filament Widget/Dashboard

```
Create a Filament Widget for [WIDGET_PURPOSE].

Context:
- Panel: [OwnerPanel / BranchPanel]
- User role: [ROLE]
- Data scope: [all branches / current branch]

Widget Type: [Stats / Chart / Table]

Requirements:
- Widget showing:
  [METRIC1]: description, calculation
  [METRIC2]: description, calculation
  ...
- Proper scoping by branch
- Caching if expensive query
- Refresh on interval if real-time needed
- Arabic labels
- Click actions if applicable

Widget Details:
[الصق التفاصيل هنا]

Generate complete Widget class.
```

---

## 📋 Template 9: Migration معقدة

```
Create a Laravel Migration for [DESCRIPTION].

Requirements:
- Table: [TABLE_NAME]
- Columns:
  [COLUMN1]: type, constraints, indexes
  [COLUMN2]: type, constraints, indexes
  ...
- Foreign keys with proper cascading
- Indexes for:
  * branch_id (for tenant queries)
  * Frequently queried columns
  * Unique constraints
- Timestamps
- Soft deletes if applicable
- Proper down() method

Special Requirements:
[أي متطلبات خاصة، مثل: JSON columns, full-text index, etc.]

Generate migration with proper rollback.
```

---

## 📋 Template 10: QR/Barcode Generation

```
Create QR/Barcode generation for Students in Programming School System.

Requirements:
- Generate unique QR code and Barcode for each student
- QR format: [specify format, e.g., JSON with student_id, branch_id]
- Barcode format: [specify, e.g., CODE128]
- Store in: storage/app/qrcodes/[branch_id]/[student_id].png
- Model method: generateQrCode()
- Filament action: "طباعة QR" button in StudentResource
- Generate PDF with student info + QR for printing
- Service class: QrCodeService

Libraries to use:
- simplesoftwareio/simple-qrcode
- barryvdh/laravel-dompdf (for printing)

Generate complete implementation.
```

---

## 📋 Template 11: Custom Filament Page

```
Create a custom Filament Page for [PAGE_PURPOSE].

Context:
- Panel: [PANEL_NAME]
- Navigation group: [GROUP_NAME]
- User roles allowed: [ROLES]

Page Type: [Simple page / Form / Table]

Requirements:
- Page showing:
  [SECTION1]: description, components
  [SECTION2]: description, components
  ...
- Actions:
  [ACTION1]: description, authorization
  [ACTION2]: description, authorization
- Filters if applicable
- Proper authorization check
- Arabic labels and headings

Page Details:
[الصق التفاصيل هنا]

Generate complete Page class with view/form/table as needed.
```

---

## 📋 Template 12: Report/Export

```
Create an Export for [REPORT_NAME] in Programming School System.

Context:
- Export format: [Excel / PDF / Both]
- Scope: [all branches for Owner / current branch for others]
- Triggered from: [Resource / Custom page]

Export Requirements:
- Columns:
  [COLUMN1]: label, data source
  [COLUMN2]: label, data source
  ...
- Filters applied:
  * Branch (for Owner)
  * Date range
  * [OTHER_FILTERS]
- Formatting:
  * Arabic headers
  * Number formatting (currency, percentages)
  * Conditional formatting if needed
- Filename: [PATTERN]

Use: maatwebsite/excel for Excel, barryvdh/laravel-dompdf for PDF

Generate complete Export class with Filament action.
```

---

## 🎯 كيفية الاستخدام | How to Use

### في VS Code:

1. **افتح ملف جديد** أو اذهب للمكان اللي عايز تكتب فيه الكود

2. **اكتب تعليق** باستخدام أحد القوالب أعلاه:
   ```php
   // Create a Laravel Model for Student in Programming School System.
   // Context: Multi-tenant system (single DB + branch_id)...
   // [باقي الـ prompt]
   ```

3. **Copilot هيبدأ يقترح الكود** - اضغط `Tab` للقبول

4. **راجع الكود** وعدّل لو محتاج

5. **أكمل بباقي الملفات** (Policy, Resource, Tests) باستخدام نفس الطريقة

### نصائح:

- ✅ **كن واضح ومحدد** في الـ prompt
- ✅ **اذكر السياق** (multi-tenant, roles, branch scoping)
- ✅ **اطلب Tests** دائمًا
- ✅ **استخدم أمثلة** لو الطلب معقد
- ❌ **لا تقبل الكود أعمى** - راجع دائمًا
- ❌ **لا تنسى الـ Authorization** - Policies مهمة جدًا

---

## 📚 أمثلة واقعية | Real Examples

### مثال 1: إنشاء Student Model

**Prompt:**
```
Create a Laravel Model for Student in Programming School System.

Context:
- Multi-tenant system (single DB + branch_id for tenant isolation)
- Role-based access: Owner (all branches), BranchManager (own branch)

Requirements:
- Migration with:
  * id, branch_id (foreign key to branches)
  * name, phone, email
  * parent_name, parent_phone, parent_email
  * qr_code (unique), barcode (unique)
  * status (active/inactive/suspended)
  * timestamps, soft deletes
  
- Model with:
  * BranchScope global scope
  * Relationships: branch (BelongsTo), enrollments (HasMany), attendances (HasMany)
  * Fillable all except id
  * Casts: status as enum
  * Method: generateQrCode()
  
- Policy:
  * viewAny: check 'view_students' permission
  * view: Owner sees all, others see own branch only
  * create/update: BranchManager + Secretary
  * delete: BranchManager only, not if has active enrollments
  
- Factory with realistic Arabic names
- Feature test covering CRUD as BranchManager

Generate complete code with Arabic comments where helpful.
```

**Result:** Copilot سيولد Model + Migration + Policy + Factory + Test

---

### مثال 2: إنشاء Payment Resource

**Prompt:**
```
Create a Filament 4 Resource for Payment.

Context:
- Multi-panel: BranchPanel (filtered by branch)
- Roles: BranchManager (full access), Secretary (create/view)

Requirements:
- Table with columns:
  * Student name (searchable)
  * Amount (sortable, formatted as currency)
  * Paid amount (formatted)
  * Due date (sortable)
  * Status (badge: paid=success, partial=warning, pending=danger)
  * Payment method
  
- Filters:
  * Status (paid/partial/pending)
  * Date range
  * Payment method
  
- Form with:
  * Student select (only students in current branch)
  * Course select (based on student enrollments)
  * Amount (read-only, from course price)
  * Paid amount (required, max: remaining amount)
  * Payment method (cash/card/online)
  * Due date
  * Notes (textarea)
  
- Actions:
  * Mark as Paid (if status = pending)
  * Print Receipt (generates PDF)
  
- Query scoping: only current branch
- Authorization: Secretary can create, BranchManager can do all

Generate complete Resource with authorization.
```

---

## 🚦 Testing Prompts

استخدم هذا لاختبار Copilot:

```php
// Test Prompt: Generate a simple Policy for Branch model
// Requirements:
// - Owner: can do everything
// - BranchManager: can only view own branch
// - Others: no access
// Generate complete BranchPolicy class
```

إذا Copilot ولّد كود صحيح، يبقى جاهز للشغل الفعلي! 🎉

---

**آخر تحديث:** 2025-12-18  
**الحالة:** ✅ جاهز للاستخدام
