# 🏦 نظام إدارة الخزائن المتقدم - توثيق شامل

## 📋 نظرة عامة

تم تطوير نظام متكامل لإدارة الخزائن المالية مع ثلاثة أنواع من الخزائن:
- 💵 **الخزينة النقدية** (Cash)
- 📱 **فودافون كاش** (Vodafone Cash)
- 💳 **إنستاباي** (InstaPay)

---

## ✨ المزايا الرئيسية

### 1. **استقلالية الخزائن**
- كل خزينة مستقلة تماماً مع ID منفصل
- لا يوجد خلط أو تداخل بين الخزائن
- رصيد افتتاحي منفصل لكل خزينة
- تتبع دقيق لكل معاملة حسب خزينتها

### 2. **Multi-Tenancy**
- كل فرع له خزائنه الخاصة
- عزل تام بين بيانات الفروع المختلفة
- Super Admin يمكنه رؤية جميع الفروع
- المستخدمون العاديون يرون فرعهم فقط

### 3. **حسابات تلقائية**
```
الرصيد الحالي = الرصيد الافتتاحي + (الإيرادات + مدفوعات الطلاب) - المصروفات
```

### 4. **تنبيهات ذكية**
- تنبيه فوري عند وجود رصيد سالب
- تمييز بصري للخزائن ذات الرصيد السالب
- إشعارات مستمرة حتى حل المشكلة

---

## 🗂️ الهيكل الفني

### قاعدة البيانات

#### جدول `vaults`
```sql
- id (PK)
- tenant_id (FK to tenants)
- name (string) - اسم الخزينة
- type (enum: cash, vodafone, instapay)
- opening_balance (decimal 15,2) - الرصيد الافتتاحي
- description (text, nullable)
- is_active (boolean) - نشطة أم لا
- timestamps
```

#### جدول `vault_reconciliations`
```sql
- id (PK)
- tenant_id (FK to tenants)
- vault_id (FK to vaults)
- reconciliation_date (date)
- system_balance (decimal 15,2) - رصيد النظام
- actual_balance (decimal 15,2) - الرصيد الفعلي
- difference (decimal 15,2) - الفرق
- notes (text, nullable)
- status (enum: pending, approved, rejected)
- reconciled_by (FK to users)
- approved_by (FK to users, nullable)
- approved_at (timestamp, nullable)
- timestamps
```

#### التحديثات على الجداول المالية
```sql
expenses:
  + vault_id (FK to vaults, nullable)

revenues:
  + vault_id (FK to vaults, nullable)

student_payments:
  + vault_id (FK to vaults, nullable)
```

---

## 📊 الموارد (Resources)

### 1. **VaultResource** - إدارة الخزائن

**الصفحات:**
- `index` - قائمة الخزائن
- `create` - إنشاء خزينة جديدة
- `edit` - تعديل خزينة
- `transactions` - تقرير معاملات الخزائن

**المميزات:**
- إضافة/تعديل/حذف الخزائن
- تحديد الرصيد الافتتاحي
- تفعيل/إيقاف الخزينة
- عرض جميع المعاملات المرتبطة

### 2. **VaultReconciliationResource** - تسويات الخزائن

**الوظائف:**
- إنشاء تسوية جديدة
- حساب تلقائي لرصيد النظام حتى تاريخ التسوية
- إدخال الرصيد الفعلي من السكرتيرة
- حساب تلقائي للفرق
- اعتماد/رفض التسوية
- تتبع من قام بالتسوية ومن اعتمدها

**دورة العمل:**
```
1. إنشاء تسوية (pending)
2. إدخال البيانات الفعلية
3. مراجعة الفرق
4. اعتماد (approved) أو رفض (rejected)
```

---

## 🎯 التقارير والـ Widgets

### 1. **VaultBalancesWidget** - أرصدة الخزائن
**الموقع:** لوحة التحكم الرئيسية

**المعلومات المعروضة:**
- رصيد كل خزينة مع الأيقونة المميزة
- تفصيل: إيرادات | مدفوعات طلاب | مصروفات
- إجمالي كلي لجميع الخزائن
- تنبيهات بصرية للرصيد السالب

**الألوان:**
- 🟢 **أخضر**: رصيد موجب
- 🔴 **أحمر**: رصيد سالب (مع إطار تحذيري)

### 2. **VaultTransactions** - تقرير معاملات الخزائن
**الطريق:** `/admin/vaults/transactions`

**المميزات:**
- عرض جميع المعاملات (إيرادات، مدفوعات طلاب، مصروفات)
- فلترة حسب:
  - الخزينة
  - نوع المعاملة
  - التاريخ (من - إلى)
- عرض الرصيد بعد كل عملية
- إحصائيات عامة (إجمالي الرصيد، عدد المعاملات، التاريخ)

**نوع المعاملات:**
- 💰 **إيراد** (Revenue) - أخضر
- 🎓 **دفعة طالب** (Student Payment) - أزرق
- 💸 **مصروف** (Expense) - أحمر

### 3. **VaultDailyMovement** - تقرير الحركة اليومية
**الموقع:** Widget في لوحة التحكم

**جدول تفصيلي يعرض:**
- رصيد أول اليوم لكل خزينة
- إيرادات اليوم
- مدفوعات الطلاب اليوم
- إجمالي الوارد
- مصروفات اليوم
- صافي الحركة اليومية
- رصيد نهاية اليوم

**إجمالي كلي** في نهاية الجدول لجميع الخزائن

**اختيار التاريخ:** إمكانية تغيير التاريخ لعرض أي يوم

---

## 🔧 النماذج (Forms)

### ExpenseForm
```php
Select::make('vault_id')
    ->label('الخزينة')
    ->relationship('vault', 'name')
    ->required()
    ->createOptionForm([...]) // إنشاء خزينة جديدة بسرعة
```

### RevenueForm
```php
Select::make('vault_id')
    ->label('الخزينة')
    ->relationship('vault', 'name')
    ->required()
    ->helperText('اختر الخزينة التي تم التحصيل من خلالها')
```

### StudentPaymentForm
```php
Select::make('vault_id')
    ->label('الخزينة')
    ->relationship('vault', 'name')
    ->required()
    ->helperText('اختر الخزينة التي تم الدفع من خلالها')
```

---

## 📈 الجداول (Tables)

### تحديثات الجداول المالية

**ExpensesTable, RevenuesTable, StudentPaymentsTable:**

تمت إضافة:
1. **عمود الخزينة:**
```php
TextColumn::make('vault.name')
    ->label('الخزينة')
    ->badge()
    ->icon(fn ($record) => match($record->vault?->type) {
        'cash' => '💵',
        'vodafone' => '📱',
        'instapay' => '💳',
        default => '💰'
    })
    ->color('success')
    ->sortable()
```

2. **فلتر الخزينة:**
```php
SelectFilter::make('vault_id')
    ->label('الخزينة')
    ->relationship('vault', 'name')
    ->searchable()
    ->preload()
    ->multiple()
```

---

## 🔄 العلاقات (Relationships)

### Vault Model
```php
public function expenses(): HasMany
public function revenues(): HasMany
public function studentPayments(): HasMany
public function reconciliations(): HasMany
public function getCurrentBalance(): float
```

### Expense/Revenue/StudentPayment Models
```php
public function vault(): BelongsTo
```

### VaultReconciliation Model
```php
public function vault(): BelongsTo
public function reconciledBy(): BelongsTo
public function approvedBy(): BelongsTo
```

---

## 🎨 التصميم المرئي

### الأيقونات
- 💵 نقدي
- 📱 فودافون كاش
- 💳 إنستاباي
- 💰 الإجمالي
- 📊 التقارير
- ⚠️ تحذير

### الألوان
- **Success (أخضر)**: رصيد موجب، إيرادات
- **Danger (أحمر)**: رصيد سالب، مصروفات
- **Info (أزرق)**: مدفوعات طلاب
- **Warning (برتقالي)**: قيد المراجعة
- **Primary (بنفسجي)**: صافي الحركة

---

## 🚀 كيفية الاستخدام

### 1. إنشاء الخزائن
```bash
php artisan db:seed --class=VaultSeeder
```
يقوم بإنشاء 3 خزائن لكل فرع تلقائياً.

### 2. تسجيل المعاملات
عند إضافة مصروف أو إيراد أو دفعة طالب:
1. اختر الخزينة المناسبة من القائمة
2. أكمل باقي البيانات
3. احفظ - سيتم تحديث رصيد الخزينة تلقائياً

### 3. المراجعة اليومية
1. افتح تقرير الحركة اليومية
2. اختر التاريخ
3. راجع جميع المعاملات لكل خزينة
4. تحقق من الرصيد النهائي

### 4. التسوية مع السكرتيرة
1. اذهب إلى "تسويات الخزائن"
2. انقر "إنشاء جديد"
3. اختر الخزينة والتاريخ
4. أدخل الرصيد الفعلي من السكرتيرة
5. النظام سيحسب الفرق تلقائياً
6. اعتمد أو ارفض التسوية

---

## ⚙️ الإعدادات والصلاحيات

### Multi-Tenancy
- كل فرع يرى خزائنه فقط
- Super Admin يرى جميع الفروع
- التصفية التلقائية بـ `tenant_id`

### الصلاحيات المطلوبة
- `view_vault` - عرض الخزائن
- `create_vault` - إنشاء خزينة
- `edit_vault` - تعديل خزينة
- `delete_vault` - حذف خزينة
- `view_vault_reconciliation` - عرض التسويات
- `create_vault_reconciliation` - إنشاء تسوية
- `approve_vault_reconciliation` - اعتماد تسوية

---

## 📝 الملفات المهمة

### Models
- `app/Models/Vault.php`
- `app/Models/VaultReconciliation.php`
- `app/Models/Expense.php` (محدث)
- `app/Models/Revenue.php` (محدث)
- `app/Models/StudentPayment.php` (محدث)

### Resources
- `app/Filament/Admin/Resources/Vaults/VaultResource.php`
- `app/Filament/Admin/Resources/VaultReconciliations/VaultReconciliationResource.php`

### Widgets
- `app/Filament/Admin/Resources/Expenses/Widgets/VaultBalancesWidget.php`
- `app/Filament/Admin/Widgets/VaultDailyMovement.php`

### Pages
- `app/Filament/Admin/Resources/Vaults/Pages/VaultTransactions.php`

### Migrations
- `database/migrations/2025_12_26_232137_create_vaults_table.php`
- `database/migrations/2025_12_26_232228_update_financial_tables_to_use_vault_id.php`
- `database/migrations/2025_12_26_234555_create_vault_reconciliations_table.php`

### Seeders
- `database/seeders/VaultSeeder.php`

---

## 🐛 استكشاف الأخطاء

### مشكلة: لا تظهر الخزائن
**الحل:**
```bash
php artisan db:seed --class=VaultSeeder
```

### مشكلة: رصيد خاطئ
**الحل:**
1. تحقق من جميع المعاملات المرتبطة بالخزينة
2. تأكد من تحديد `vault_id` في جميع المعاملات
3. راجع الرصيد الافتتاحي للخزينة

### مشكلة: لا تظهر التنبيهات
**الحل:**
1. امسح الـ cache: `php artisan cache:clear`
2. امسح الـ session
3. أعد تحميل الصفحة

---

## 📊 إحصائيات النظام

### الميزات المنفذة ✅
- ✅ نظام الخزائن الأساسي
- ✅ ربط المعاملات المالية بالخزائن
- ✅ Widget أرصدة الخزائن
- ✅ تقرير معاملات الخزائن
- ✅ نظام التسوية مع السكرتيرة
- ✅ تقرير الحركة اليومية
- ✅ تنبيهات الرصيد السالب
- ✅ Multi-tenancy
- ✅ فلاتر متقدمة
- ✅ حسابات تلقائية

### التحسينات المستقبلية 🚀
- 📈 رسوم بيانية للحركة المالية
- 📊 تقارير شهرية/سنوية
- 💾 تصدير التقارير (PDF/Excel)
- 🔔 تنبيهات بالبريد الإلكتروني
- 📱 إشعارات push للموبايل
- 🔄 مزامنة تلقائية مع البنوك
- 📋 سجل كامل للتعديلات (Audit Log)

---

## 👥 الدعم والتواصل

للاستفسارات والدعم الفني، يرجى التواصل مع فريق التطوير.

**تم التطوير بواسطة:** فريق AI School
**التاريخ:** ديسمبر 2025
**الإصدار:** 1.0.0

---

## 📜 الترخيص

هذا النظام جزء من مشروع AI School وهو محمي بحقوق الملكية الفكرية.

---

✨ **نظام إدارة الخزائن - حل شامل وموثوق لإدارة المالية** ✨
