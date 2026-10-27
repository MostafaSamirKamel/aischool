# دليل تدفق نظام المدفوعات - Payment System Flow

## المعادلة الموحدة في كل النظام

```
الخطوة 1: إجمالي الكورسات = مجموع أسعار الكورسات
الخطوة 2: الصافي = إجمالي - الخصم
الخطوة 3: المتبقي = الصافي - المدفوع
```

---

## 📋 التدفق الكامل للنظام

### 1️⃣ التسجيل الأولي (CreateEnrollment)

**الملف:** [CreateEnrollment.php](app/Filament/Admin/Resources/Enrollments/Pages/CreateEnrollment.php)

```php
// المدخلات:
- الكورسات المختارة → الإجمالي
- الخصم (discount)
- المقدم (paid_amount)
- عدد الأقساط

// الخدمة المستخدمة:
InstallmentCalculationService::calculateInstallments()
  ↓
1. صافي = إجمالي - خصم
2. متبقي = صافي - مقدم
3. قيمة القسط = متبقي ÷ عدد الأقساط
  ↓
createInstallmentsForEnrollment() - إنشاء سجلات الأقساط
  ↓
applyAdvancePaymentToInstallments() - تطبيق المقدم على الأقساط
```

**النتيجة:**
- ✅ `enrollment.paid_amount` = المقدم المدفوع
- ✅ الأقساط الأولى تكون مدفوعة جزئياً أو كلياً

---

### 2️⃣ دفع قسط جديد (PaymentForm → PaymentObserver)

**الملف:** [PaymentForm.php](app/Filament/Admin/Resources/Payments/Schemas/PaymentForm.php)

```php
// عند اختيار الطالب:
hydrateEnrollmentData()
  ↓
// جلب بيانات جميع التسجيلات للطالب
- إجمالي الكورسات (من جميع التسجيلات)
- الخصومات (total_discount)
- المدفوع مقدماً (paid_upfront)
  ↓
// حساب المدفوعات الأخرى:
if (نظام تقسيط):
    totalPaid = sum(installments.paid_amount)
    otherPayments = totalPaid - paidUpfront
else:
    totalPaid = paidUpfront + previousPayments
  ↓
// حساب المتبقي (المعادلة الموحدة):
remaining = إجمالي - خصم - totalPaid
```

**عند الدفع:**
```php
Payment::create([
    amount: المبلغ المدفوع,
    status: 'completed'
])
  ↓
PaymentObserver::updated() يتم تشغيله تلقائياً
```

---

### 3️⃣ تطبيق المدفوعات على الأقساط (PaymentObserver)

**الملف:** [PaymentObserver.php](app/Observers/PaymentObserver.php)

```php
updateInstallmentPayment(payment)
  ↓
1. جلب الأقساط المعلقة (pending) حسب due_date
  ↓
2. توزيع المبلغ المدفوع بالترتيب:
   foreach قسط معلق:
       if المبلغ المتبقي >= القسط المتبقي:
           ✅ دفع القسط كاملاً (status = paid)
       else:
           ⚠️ دفع جزئي (status = partial)
  ↓
3. تحديث enrollment.paid_amount:
   paid_amount = sum(installments.paid_amount)
  ↓
4. إنشاء سجل revenue تلقائياً
```

---

## 🔄 دورة الحياة الكاملة - مثال عملي

### السيناريو:
- طالب يسجل في كورسين: 500 + 300 = **800 ج.م**
- خصم: **50 ج.م**
- مقدم (عند التسجيل): **150 ج.م**
- نظام الدفع: **4 أقساط**

---

### المرحلة 1: التسجيل

```
الحساب:
  إجمالي = 800
  صافي = 800 - 50 = 750
  متبقي للأقساط = 750 - 150 = 600
  قيمة القسط = 600 ÷ 4 = 150

الأقساط المُنشأة:
  القسط 1: 150 ج.م (استحقاق شهر 1)
  القسط 2: 150 ج.م (استحقاق شهر 2)
  القسط 3: 150 ج.م (استحقاق شهر 3)
  القسط 4: 150 ج.م (استحقاق شهر 4)

تطبيق المقدم (150 ج.م):
  ✅ القسط 1: paid_amount = 150 → status = paid
  ⏳ القسط 2-4: paid_amount = 0 → status = pending

enrollment.paid_amount = 150 (المقدم)
```

---

### المرحلة 2: دفعة جديدة (200 ج.م)

```
في شاشة المدفوعات:
  courses_total: 800
  total_discount: 50
  paid_upfront: 150
  other_payments: 0 (لا توجد مدفوعات سابقة)
  total_paid: 150
  remaining_balance: 800 - 50 - 150 = 600 ✓

عند الدفع:
  Payment::create([amount: 200])
    ↓
  PaymentObserver يشتغل تلقائياً:
    ↓
  توزيع الـ 200 على الأقساط المعلقة:
    القسط 2: 200 >= 150 ✅ → paid_amount = 150, status = paid
    المتبقي من الدفعة: 200 - 150 = 50
    القسط 3: 50 < 150 ⚠️ → paid_amount = 50, status = partial
    ↓
  enrollment.paid_amount = 150 + 150 + 50 = 350 ✓
```

---

### المرحلة 3: عرض البيانات في شاشة المدفوعات

```
hydrateEnrollmentData() تحسب:
  
  totalPaidFromInstallments = sum(paid_amount من الأقساط)
                             = 150 + 150 + 50 + 0
                             = 350
  
  total_paid = 350
  other_payments = 350 - 150 (المقدم) = 200
  
  remaining_balance = 800 - 50 - 350 = 400 ✓

التفاصيل المعروضة:
  💰 المدفوع مقدماً: 150 ج.م
  📋 مدفوعات أخرى: 200 ج.م
  ✅ إجمالي المدفوع: 350 ج.م
  ⚠️ المتبقي: 400 ج.م
```

---

## 📊 جداول الأقساط

### بعد التسجيل:
| القسط | المبلغ | المدفوع | الحالة | المتبقي |
|------|--------|---------|--------|---------|
| 1 | 150 | 150 | ✅ paid | 0 |
| 2 | 150 | 0 | ⏳ pending | 150 |
| 3 | 150 | 0 | ⏳ pending | 150 |
| 4 | 150 | 0 | ⏳ pending | 150 |

### بعد دفع 200 ج.م:
| القسط | المبلغ | المدفوع | الحالة | المتبقي |
|------|--------|---------|--------|---------|
| 1 | 150 | 150 | ✅ paid | 0 |
| 2 | 150 | 150 | ✅ paid | 0 |
| 3 | 150 | 50 | ⚠️ partial | 100 |
| 4 | 150 | 0 | ⏳ pending | 150 |

---

## 🔗 الربط بين الملفات

### سلسلة التكامل:
```
EnrollmentForm.php (حسابات حية)
  ↓ يستخدم
InstallmentCalculationService::calculateForForm()
  ↓
CreateEnrollment.php (عند الحفظ)
  ↓ يستخدم
InstallmentCalculationService
  ↓ ينشئ
Installments (جدول installments)
  ↓
PaymentForm.php (عند اختيار طالب)
  ↓ يقرأ
Installments + Enrollments
  ↓ يحسب
remaining_balance (المعادلة الموحدة)
  ↓
Payment (عند الدفع)
  ↓ يُطلق
PaymentObserver
  ↓ يحدّث
Installments.paid_amount
  ↓ يحدّث
Enrollment.paid_amount
  ↓ ينشئ
Revenue (إيراد تلقائي)
```

---

## ✅ النقاط الرئيسية

1. **المعادلة موحدة في كل مكان:**
   ```
   المتبقي = إجمالي - خصم - مدفوع
   ```

2. **paid_amount يتم تحديثه تلقائياً:**
   ```php
   enrollment.paid_amount = sum(installments.paid_amount)
   ```

3. **الأقساط تُملأ بالترتيب:**
   - حسب `due_date` ثم `installment_number`

4. **المقدم جزء من paid_amount:**
   - عند التسجيل: `paid_amount = المقدم`
   - عند كل دفعة: `paid_amount = sum(installments.paid_amount)`

5. **شاشة المدفوعات تدعم تسجيلات متعددة:**
   - تجمع بيانات جميع تسجيلات الطالب
   - تحسب الإجمالي الكلي

---

## 📝 الملفات المتأثرة

| الملف | الدور | المعادلة المستخدمة |
|------|------|-------------------|
| [InstallmentCalculationService.php](app/Services/Enrollment/InstallmentCalculationService.php) | حساب الأقساط | متبقي = صافي - مقدم |
| [Enrollment.php](app/Models/Enrollment.php) | نموذج البيانات | `getRemainingBalance()` |
| [PaymentObserver.php](app/Observers/PaymentObserver.php) | تطبيق المدفوعات | `paid_amount = sum(installments)` |
| [PaymentForm.php](app/Filament/Admin/Resources/Payments/Schemas/PaymentForm.php) | واجهة الدفع | متبقي = إجمالي - خصم - مدفوع |
| [CreateEnrollment.php](app/Filament/Admin/Resources/Enrollments/Pages/CreateEnrollment.php) | إنشاء التسجيل | يستخدم الخدمة |
| [EnrollmentForm.php](app/Filament/Admin/Resources/Enrollments/Schemas/EnrollmentForm.php) | نموذج التسجيل | حسابات حية |

---

**آخر تحديث:** مارس 2026  
**الحالة:** ✅ موحد ومُختبر
