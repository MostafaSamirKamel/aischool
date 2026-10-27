# 🔄 نظام التحويل بين الخزائن - Vault Transfer System

## 📝 نظرة عامة

تم إضافة نظام متكامل لإدارة التحويلات المالية بين الخزائن المختلفة، مع نظام اعتماد وتتبع كامل.

---

## 🗂️ الملفات المضافة/المُحدثة

### 1️⃣ Database Migration
- **الملف**: `database/migrations/2026_03_29_000001_create_vault_transfers_table.php`
- **الجدول**: `vault_transfers`
- **الحقول الرئيسية**:
  - `from_vault_id`: الخزينة المُحوّل منها
  - `to_vault_id`: الخزينة المُحوّل إليها
  - `amount`: المبلغ المُحوّل
  - `transfer_code`: كود التحويل (فريد)
  - `status`: حالة التحويل (pending/completed/cancelled)
  - `transferred_by`: من قام بالتحويل
  - `approved_by`: من اعتمد التحويل
  - `approved_at`: تاريخ الاعتماد

### 2️⃣ Model
- **الملف**: `app/Models/VaultTransfer.php`
- **المميزات**:
  - توليد كود تحويل تلقائي بصيغة `VT-YYMMDD-0001`
  - Scopes: `pending()`, `completed()`, `cancelled()`
  - Helper methods: `isPending()`, `isCompleted()`, `isCancelled()`, `canBeEdited()`, `canBeDeleted()`
  - علاقات: `fromVault`, `toVault`, `transferredBy`, `approvedBy`

### 3️⃣ تحديث Vault Model
- **الملف**: `app/Models/Vault.php`
- **التحديثات**:
  - إضافة علاقات: `transfersFrom()`, `transfersTo()`
  - تحديث `getCurrentBalance()` ليشمل التحويلات الواردة والصادرة
  - إضافة حماية الحذف للتحويلات المرتبطة

### 4️⃣ Filament Resource
- **المجلد**: `app/Filament/Admin/Resources/VaultTransfers/`
- **الملفات**:
  - `VaultTransferResource.php`: الـ Resource الرئيسي
  - `Schemas/VaultTransferForm.php`: نموذج الإدخال
  - `Tables/VaultTransfersTable.php`: جدول العرض والإجراءات
  - `Pages/ListVaultTransfers.php`: صفحة القائمة
  - `Pages/CreateVaultTransfer.php`: صفحة الإنشاء
  - `Pages/EditVaultTransfer.php`: صفحة التعديل
  - `Pages/ViewVaultTransfer.php`: صفحة العرض

### 5️⃣ Policy
- **الملف**: `app/Policies/VaultTransferPolicy.php`
- **الصلاحيات**:
  - `viewAny`, `view`, `create`, `update`, `delete`
  - صلاحيات خاصة: `approve()`, `cancel()`

### 6️⃣ ملفات الترجمة
- **العربية**: `lang/ar/filament/admin/vault_transfer_resource.php`
- **الإنجليزية**: `lang/en/filament/admin/vault_transfer_resource.php`

---

## 🎯 المميزات الرئيسية

### ✅ دورة حياة التحويل (Transfer Lifecycle)

```
1. إنشاء التحويل (Pending) 
   ↓
2. اعتماد التحويل (Completed) ← يتم تحديث الأرصدة
   ↓
   أو
   ↓
   إلغاء التحويل (Cancelled) ← مع ذكر السبب
```

### ✅ توليد كود تلقائي
- نمط الكود: `VT-YYMMDD-0001`
- مثال: `VT-260329-0001` (أول تحويل في 29 مارس 2026)

### ✅ منع التحويل من نفس الخزينة لنفس الخزينة
- لا يمكن اختيار نفس الخزينة في "من" و "إلى"
- التحقق في Form باستخدام `live()` و `afterStateUpdated()`

### ✅ الحماية من التعديل/الحذف
- لا يمكن تعديل/حذف التحويلات المكتملة أو الملغاة
- فقط التحويلات `pending` قابلة للتعديل

### ✅ Actions في الجدول
- **عرض** (View): لكل التحويلات
- **اعتماد** (Approve): للتحويلات قيد الانتظار فقط
- **تعديل** (Edit): للتحويلات قيد الانتظار فقط
- **إلغاء** (Cancel): للتحويلات قيد الانتظار فقط + إدخال سبب الإلغاء
- **حذف** (Delete): للتحويلات قيد الانتظار فقط

### ✅ تحديث الأرصدة التلقائي
عند اعتماد التحويل:
```php
$fromVault->current_balance -= $amount  // خصم من الخزينة المصدر
$toVault->current_balance += $amount    // إضافة للخزينة الهدف
```

---

## 🔍 Filters المتاحة

1. **حالة التحويل** (Status)
2. **من خزينة** (From Vault)
3. **إلى خزينة** (To Vault)
4. **تاريخ التحويل** (Transfer Date Range)

---

## 📊 العلاقات (Relationships)

```php
VaultTransfer:
- fromVault() → belongsTo(Vault)
- toVault() → belongsTo(Vault)
- transferredBy() → belongsTo(User)
- approvedBy() → belongsTo(User)

Vault:
- transfersFrom() → hasMany(VaultTransfer, 'from_vault_id')
- transfersTo() → hasMany(VaultTransfer, 'to_vault_id')
```

---

## 🚀 طريقة الاستخدام

### 1️⃣ إنشاء تحويل جديد
1. اذهب إلى "الإدارة المالية" → "التحويلات بين الخزائن"
2. اضغط "تحويل جديد"
3. املأ البيانات:
   - اختر الخزينة المصدر
   - اختر الخزينة الهدف
   - أدخل المبلغ
   - أضف ملاحظات (اختياري)
4. احفظ

### 2️⃣ اعتماد التحويل
1. افتح قائمة التحويلات
2. اضغط على زر "اعتماد" بجانب التحويل قيد الانتظار
3. أكد الاعتماد
4. سيتم تحديث الأرصدة تلقائياً

### 3️⃣ إلغاء التحويل
1. افتح قائمة التحويلات
2. اضغط على زر "إلغاء" بجانب التحويل قيد الانتظار
3. أدخل سبب الإلغاء
4. أكد الإلغاء

---

## ⚙️ خطوات تشغيل Migration

```bash
# تشغيل Migration
php artisan migrate

# إذا كنت تريد rollback
php artisan migrate:rollback

# إعادة تشغيل جميع Migrations (احذر! ستحذف البيانات)
php artisan migrate:fresh
```

---

## 🔐 الصلاحيات المطلوبة

يجب إضافة الصلاحيات التالية في نظام Spatie Permissions:

```php
'view_vault_transfers'     // عرض قائمة التحويلات
'create_vault_transfers'   // إنشاء تحويل جديد
'update_vault_transfers'   // تعديل تحويل
'delete_vault_transfers'   // حذف تحويل
'approve_vault_transfers'  // اعتماد تحويل
'cancel_vault_transfers'   // إلغاء تحويل
```

---

## 📌 ملاحظات مهمة

1. **التحويلات المكتملة لا يمكن تعديلها أو حذفها**
2. **يتم تسجيل من قام بإنشاء التحويل واعتماده تلقائياً**
3. **كود التحويل فريد ولا يمكن تكراره**
4. **يتم منع حذف الخزائن التي بها تحويلات**
5. **يمكن للـ Super Admin رؤية التحويلات فقط، بدون أي إجراءات**

---

## 🧪 اختبار النظام

```php
// إنشاء تحويل تجريبي
$transfer = VaultTransfer::create([
    'tenant_id' => auth()->user()->tenant_id,
    'from_vault_id' => 1,
    'to_vault_id' => 2,
    'amount' => 1000.00,
    'transfer_date' => now(),
    'status' => 'pending',
    'notes' => 'تحويل تجريبي',
]);

// اعتماد التحويل
$transfer->update([
    'status' => 'completed',
    'approved_by' => auth()->id(),
    'approved_at' => now(),
]);

// التحقق من الأرصدة
$fromVault = Vault::find(1);
$toVault = Vault::find(2);

echo "From Vault Balance: " . $fromVault->getCurrentBalance();
echo "To Vault Balance: " . $toVault->getCurrentBalance();
```

---

## ✅ ملخص التحديث

- ✅ Migration للجدول الجديد
- ✅ Model مع علاقات ومنطق الأعمال
- ✅ تحديث Vault model
- ✅ Filament Resource كامل
- ✅ Forms وTables
- ✅ Pages (List, Create, Edit, View)
- ✅ Policy للصلاحيات
- ✅ ملفات الترجمة (عربي/إنجليزي)
- ✅ Actions (Approve, Cancel, Edit, Delete, View)
- ✅ Filters متقدمة
- ✅ تتبع كامل (من أنشأ، من اعتمد)

---

**تم إنشاء النظام بتاريخ**: 29 مارس 2026  
**النسخة**: 1.0.0  
**الحالة**: ✅ جاهز للاستخدام
