# خدمة حساب الأقساط - InstallmentCalculationService

## نظرة عامة

تم إنشاء خدمة متخصصة `InstallmentCalculationService` تطبق المعادلة الصحيحة لحساب الأقساط والمدفوعات بشكل مركزي وموحد في جميع تطبيق الإنرولمنت.

## المعادلة الصحيحة (4 خطوات)

```
الخطوة 1: إجمالي الكورسات = مجموع أسعار الكورسات المختارة
الخطوة 2: الصافي = إجمالي الكورسات - الخصم
الخطوة 3: المتبقي = الصافي - المقدم (المدفوع عند التسجيل)
الخطوة 4: قيمة القسط = المتبقي ÷ عدد الأقساط
```

## القواعد الأساسية

✓ **المقدم لا يُعتبر قسط** - يتم الخصم من المبلغ قبل التقسيم
✓ **التقسيم على المتبقي فقط** - لا ندرج المقدم في عملية التقسيم
✓ **التعامل مع الكسور** - آخر قسط يتم تعديله لمراعاة الكسور العشرية
✓ **ترتيب الأقساط** - يتم ترتيب الأقساط حسب تاريخ الاستحقاق

## الملفات المتأثرة

### 1. خدمة جديدة: `app/Services/Enrollment/InstallmentCalculationService.php`

**الفئة الرئيسية:**
- `calculateInstallments()` - حساب الأقساط بناءً على المعاملات
- `distributeInstallments()` - توزيع المبلغ على الأقساط
- `createInstallmentsForEnrollment()` - إنشاء سجلات الأقساط
- `applyAdvancePaymentToInstallments()` - تطبيق المقدم على الأقساط ترتيبياً
- `getSummary()` - الحصول على ملخص الحساب
- `calculateForForm()` (static) - استخدام خاص بالفورم للحسابات الحية

### 2. نموذج الإنشاء: `app/Filament/Admin/Resources/Enrollments/Pages/CreateEnrollment.php`

**الخطوة الجديدة في `afterCreate()`:**
```php
$installmentService = new \App\Services\Enrollment\InstallmentCalculationService();

// إنشاء الأقساط باستخدام الخدمة
$installmentService->createInstallmentsForEnrollment($record, $installmentsData);

// تطبيق المقدم على الأقساط
$installmentService->applyAdvancePaymentToInstallments($record, $paidUpfront);
```

### 3. نموذج التعديل: `app/Filament/Admin/Resources/Enrollments/Pages/EditEnrollment.php`

**استخدام الخدمة عند تحديث الأقساط في `afterSave()`:**
- تدعم الآن الاستخدام المستقبلي للخدمة (معلق حالياً)
- الكود موجود لكن الخدمة اختيارية في المرحلة الحالية

### 4. نموذج الفورم: `app/Filament/Admin/Resources/Enrollments/Schemas/EnrollmentForm.php`

**الحسابات الحية (Live Calculations):**

#### أ. عند تغير عدد الأقساط (`installments_count`):
```php
->afterStateUpdated(function ($state, callable $set, callable $get) {
    $installments = \App\Services\Enrollment\InstallmentCalculationService::calculateForForm(
        $total,
        $discount,
        $paid,
        $state
    );
    $set('installments', $installments);
})
```

#### ب. عند تغير الخصم (`discount`):
```php
// تحديث الأقساط إذا كانت موجودة
$installments = \App\Services\Enrollment\InstallmentCalculationService::calculateForForm(
    $total,
    $discount,
    $paid,
    $installmentCount
);
$set('installments', $installments);
```

#### ج. عند تغير المقدم (`paid_amount`):
```php
// تحديث الأقساط إذا كانت موجودة
$installments = \App\Services\Enrollment\InstallmentCalculationService::calculateForForm(
    $total,
    $discount,
    $paid,
    $installmentCount
);
$set('installments', $installments);
```

## أمثلة الاستخدام

### مثال 1: حساب الأقساط على الفورم (حي)
```php
// استخدام static method للفورم
$installments = \App\Services\Enrollment\InstallmentCalculationService::calculateForForm(
    totalAmount: 1000,      // إجمالي الكورسات
    discount: 100,          // الخصم
    advancePayment: 200,    // المقدم
    installmentCount: 4     // عدد الأقساط
);

// النتيجة:
// صافي = 1000 - 100 = 900
// متبقي = 900 - 200 = 700
// قيمة القسط = 700 / 4 = 175
// الأقساط: 175, 175, 175, 175
```

### مثال 2: استخدام الخدمة عند الإنشاء
```php
$service = new InstallmentCalculationService();

// 1. حساب الأقساط
$installmentsData = $service->calculateInstallments(
    totalAmount: 1000,
    discount: 100,
    advancePayment: 200,
    installmentCount: 4
);

// 2. إنشاء سجلات الأقساط
$installments = $service->createInstallmentsForEnrollment($enrollment, $installmentsData);

// 3. تطبيق المقدم على الأقساط
$service->applyAdvancePaymentToInstallments($enrollment, 200);
```

### مثال 3: الحصول على ملخص الحساب
```php
$summary = $service->getSummary(
    totalAmount: 1000,
    discount: 100,
    advancePayment: 200,
    installmentCount: 4
);

// المخرجات:
// [
//   'total_courses' => 1000,
//   'discount' => 100,
//   'net_after_discount' => 900,
//   'advance_payment' => 200,
//   'remaining_to_installments' => 700,
//   'installment_count' => 4,
//   'installment_amount' => 175,
// ]
```

## كيفية عمل تطبيق المقدم

عند استدعاء `applyAdvancePaymentToInstallments($enrollment, 200)` مع 4 أقساط بـ 175 لكل واحد:

```
الأقساط الأص قبل: [175, 175, 175, 175]
المقدم: 200

1. القسط الأول: 175 < 200 → تسديد كامل، متبقي = 25
2. القسط الثاني: 175 > 25 → تسديد 25 فقط (جزئي)، متبقي = 0
3. القسط الثالث والرابع: بدون تغيير

النتيجة النهائية:
- القسط 1: الحالة = paid (مدفوع بالكامل)
- القسط 2: الحالة = partial (مدفوع جزئياً، 25/175)
- القسط 3 و 4: الحالة = pending (معلق)
```

## الحقول المتأثرة في قاعدة البيانات

### جدول `enrollments`:
- `total_amount` - إجمالي الكورسات
- `discount` - الخصم
- `paid_amount` - المقدم (المدفوع عند التسجيل)
- `installments_count` - عدد الأقساط
- `payment_plan` - نوع الدفع (full/installments)

### جدول `installments`:
- `enrollment_id` - معرف الإنرولمنت
- `installment_number` - رقم القسط
- `amount` - قيمة القسط
- `due_date` - تاريخ الاستحقاق
- `status` - الحالة (pending, partial, paid)
- `paid_amount` - المبلغ المدفوع
- `paid_date` - تاريخ الدفع

## التحقق والاختبار

### تشغيل الاختبارات:
```bash
# تشغيل الاختبارات المتعلقة بالأقساط
php artisan test --filter=Installment

# تشغيل جميع اختبارات الإنرولمنت
php artisan test --filter=Enrollment
```

### التحقق من الحسابات يدويًا:
1. انتقل إلى صفحة إنشاء إنرولمنت جديد
2. اختر الكورسات واسحب الأسعار تحديث الحقول الحسابية
3. أدخل الخصم والمقدم
4. غير عدد الأقساط لرؤية التحديث الحي

## الملاحظات التقنية

### 1. البسالة (Immutability)
الخدمة لا تعدل البيانات القديمة مباشرة، بل تُرجع بيانات جديدة.

### 2. Validation
الخدمة تتحقق من:
- أن المقدم لا يزيد عن الصافي
- أن عدد الأقساط لا يقل عن 2

### 3. Decimal Handling
الخدمة تتعامل مع الكسور العشرية بشكل صحيح:
```php
// مثال: 100 / 3 = 33.33...
// الأقساط: [33.33, 33.33, 33.34]
```

### 4. Timestamps
تاريخ الاستحقاق الأول = شهر واحد من الآن
كل قسط لاحق = قسط سابق + شهر واحد

## الأداء والتحسينات المستقبلية

### الأداء الحالي:
- O(n) حيث n = عدد الأقساط
- عادة لا يزيد n عن 12 قسط
- لا توجد استدعاءات قاعدة بيانات في الحسابات

### التحسينات المستقبلية المقترحة:
1. إضافة caching للحسابات المتكررة
2. إضافة events عند إنشاء/تحديث الأقساط
3. إضافة webhook لتطبيقات خارجية (مثل البرامج المحاسبية)

## استكشاف الأخطاء

### المشكلة: الأقساط لا تتغير عند تغير الخصم
**الحل:** تأكد من أن `payment_plan` = 'installments' و `installments_count` ≥ 2

### المشكلة: حساب الأقساط غير صحيح
**التحقق:**
```php
// تحقق من القيم الأساسية
$total = 1000;      // الكورسات
$discount = 100;    // الخصم
$paid = 200;        // المقدم
$count = 4;         // الأقساط

// الحساب:
$net = 1000 - 100 = 900;
$remaining = 900 - 200 = 700;
$per_installment = 700 / 4 = 175;
```

## المراجع والملفات ذات الصلة

- [InstallmentCalculationService.php](#ملف-الخدمة)
- [Creating Enrollments](CreateEnrollment.php)
- [Editing Enrollments](EditEnrollment.php)
- [Enrollment Form Schema](EnrollmentForm.php)
- [Installment Model](../Models/Installment.php)
- [Payment Observer](../Observers/PaymentObserver.php)
