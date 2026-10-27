# 📊 دليل نظام تقارير الأقساط المحدث (2026)

## 🎯 نظرة عامة

نظام تقارير أقساط متقدم مع **إحصائيات ذكية** و**فلاتر ديناميكية** يوفر:
- ✅ تقرير أقساط اليوم
- ✅ تقرير أقساط الأسبوع
- ✅ تقرير أقساط الشهر
- ✅ **فلترة ذكية**: متأخرة | اليوم | قادمة | الكل
- ✅ **إحصائيات فورية** بعدد ومبلغ كل فئة
- ✅ سداد مباشر من التقرير
- ✅ طباعة احترافية

---

## 🆕 آخر التحديثات (30 مارس 2026)

### ⭐ ميزات جديدة تمت إضافتها اليوم:

#### 1. **نظام الإحصائيات الذكية**
```php
public function getInstallmentStats(): array
{
    $today = now()->format('Y-m-d');
    $baseQuery = Installment::whereBetween('due_date', [$startDate, $endDate])
        ->where('is_paid', false)
        ->whereHas('contract', function ($query) {
            $query->whereIn('status', ['active', 'finished']);
        });

    return [
        'overdue' => ['count' => ..., 'amount' => ...],   // ⚠️ المتأخرة
        'today' => ['count' => ..., 'amount' => ...],      // 📅 اليوم
        'upcoming' => ['count' => ..., 'amount' => ...],   // 🔜 القادمة
        'total' => ['count' => ..., 'amount' => ...],      // 📊 الإجمالي
    ];
}
```

#### 2. **نظام الفلترة الديناميكية**
```php
public $installmentFilter = 'all'; // Property للفلتر

public function updated($property)
{
    if ($property === 'installmentFilter') {
        $this->filterInstallments(); // إعادة تحميل البيانات تلقائياً
    }
}

public function filterInstallments(): void
{
    // ... Query البيانات
    
    switch ($this->installmentFilter) {
        case 'overdue':
            $query->where('due_date', '<', $today);
            break;
        case 'today':
            $query->where('due_date', '=', $today);
            break;
        case 'upcoming':
            $query->where('due_date', '>', $today);
            break;
        default: // 'all'
            break;
    }
}
```

#### 3. **واجهة الإحصائيات في Blade**
```blade
@php
    $stats = $this->getInstallmentStats();
@endphp

<!-- 4 بطاقات إحصائية ملونة -->
<div class="no-print" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
    <!-- إجمالي - بنفسجي -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div>📊 إجمالي الأقساط</div>
        <div>{{ $stats['total']['count'] }}</div>
        <div>{{ number_format($stats['total']['amount'], 2) }} ج.م</div>
    </div>
    
    <!-- متأخرة - وردي/أحمر -->
    <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
        <div>⚠️ متأخرة</div>
        <div>{{ $stats['overdue']['count'] }}</div>
        <div>{{ number_format($stats['overdue']['amount'], 2) }} ج.م</div>
    </div>
    
    <!-- اليوم - أزرق -->
    <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
        <div>📅 اليوم</div>
        <div>{{ $stats['today']['count'] }}</div>
        <div>{{ number_format($stats['today']['amount'], 2) }} ج.م</div>
    </div>
    
    <!-- قادمة - أخضر -->
    <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
        <div>🔜 قادمة</div>
        <div>{{ $stats['upcoming']['count'] }}</div>
        <div>{{ number_format($stats['upcoming']['amount'], 2) }} ج.م</div>
    </div>
</div>
```

#### 4. **قائمة الفلترة الذكية**
```blade
<select wire:model.live="installmentFilter" style="...">
    <option value="all">📊 الكل ({{ $stats['total']['count'] }})</option>
    <option value="overdue">⚠️ المتأخرة ({{ $stats['overdue']['count'] }})</option>
    <option value="today">📅 اليوم ({{ $stats['today']['count'] }})</option>
    <option value="upcoming">🔜 القادمة ({{ $stats['upcoming']['count'] }})</option>
</select>
```

---

## 📁 هيكل الملفات

### 1️⃣ تقرير أقساط اليوم

#### **الملف الرئيسي (Controller/Page)**
```
app/Filament/Pages/InstallmentsDueToday.php
```

**الكود الكامل:**
```php
<?php

namespace App\Filament\Pages;

use App\Models\Installment;
use App\Models\Payment;
use App\Models\Vault;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use BackedEnum;
use Filament\Notifications\Notification;

class InstallmentsDueToday extends Page
{
    public function getTitle(): string
    {
        return __('filament.nav.installments_due_today');
    }
    
    public static function getNavigationLabel(): string
    {
        return __('filament.nav.installments_due_today');
    }

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';
    protected string $view = 'filament.pages.installments-due-today';

    public static function canAccess(): bool
    {
        return Filament::auth()->user()->can('View:InstallmentsDueToday');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.reports_installments');
    }

    public $installments;

    // Payment modal properties
    public $showPaymentModal = false;
    public $selectedInstallment = null;
    public $paymentAmount = null;
    public $paymentDate = null;
    public $paymentMethod = 'cash';
    public $vaultId = null;
    public $receiptNumber = null;
    public $paymentNotes = null;

    public function mount(): void
    {
        $today = now()->format('Y-m-d');
        $this->installments = Installment::with(['contract.customer'])
            ->where('due_date', $today)
            ->where('is_paid', false)
            ->whereHas('contract', function ($query) {
                $query->whereIn('status', ['active', 'finished']);
            })
            ->get();

        $this->paymentDate = now()->format('Y-m-d');
    }

    public function openPaymentModal($installmentId)
    {
        $installment = Installment::with('contract.customer')->find($installmentId);

        if (!$installment) {
            Notification::make()
                ->title('خطأ')
                ->body('القسط غير موجود')
                ->danger()
                ->send();
            return;
        }

        $this->selectedInstallment = $installment;
        $this->paymentAmount = $installment->value - ($installment->paid_amount ?? 0);
        $this->paymentDate = now()->format('Y-m-d');
        $this->paymentMethod = 'cash';
        $this->vaultId = null;
        $this->receiptNumber = null;
        $this->paymentNotes = null;
        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->selectedInstallment = null;
    }

    public function savePayment()
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
            'vaultId' => 'required|exists:vaults,id',
            'paymentDate' => 'required|date',
            'paymentMethod' => 'required|in:cash,bank_transfer,check,card',
        ], [
            'paymentAmount.required' => 'المبلغ مطلوب',
            'paymentAmount.numeric' => 'المبلغ يجب أن يكون رقماً',
            'paymentAmount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
            'vaultId.required' => 'الخزنة مطلوبة',
            'vaultId.exists' => 'الخزنة غير موجودة',
            'paymentDate.required' => 'تاريخ الدفع مطلوب',
            'paymentMethod.required' => 'طريقة الدفع مطلوبة',
        ]);

        try {
            $payment = Payment::create([
                'customer_id' => $this->selectedInstallment->contract->customer_id,
                'contract_id' => $this->selectedInstallment->contract_id,
                'vault_id' => $this->vaultId,
                'amount' => $this->paymentAmount,
                'payment_date' => $this->paymentDate,
                'payment_method' => $this->paymentMethod,
                'receipt_number' => $this->receiptNumber ?: 'REC-' . time(),
                'notes' => $this->paymentNotes,
                'status' => 'completed',
            ]);

            Notification::make()
                ->title('تم بنجاح')
                ->body('تم إضافة الدفعة وتوزيعها على الأقساط بنجاح')
                ->success()
                ->send();

            $this->closePaymentModal();
            $this->mount(); // Refresh data
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطأ')
                ->body('حدث خطأ أثناء حفظ الدفعة: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getVaultsProperty()
    {
        return Vault::where('is_active', true)->pluck('name', 'id')->toArray();
    }
}
```

---

### 2️⃣ تقرير أقساط الأسبوع (مع التحديثات الجديدة)

#### **الملف الرئيسي (Controller/Page)**
```
app/Filament/Pages/InstallmentsDueWeek.php
```

**الكود الكامل المحدث:**
```php
<?php

namespace App\Filament\Pages;

use App\Models\Installment;
use App\Models\Payment;
use App\Models\Vault;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use BackedEnum;
use Filament\Notifications\Notification;

class InstallmentsDueWeek extends Page
{
    public function getTitle(): string
    {
        return __('filament.nav.installments_due_week');
    }
    
    public static function getNavigationLabel(): string
    {
        return __('filament.nav.installments_due_week');
    }

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';
    protected string $view = 'filament.pages.installments-due-week';

    public static function canAccess(): bool
    {
        return Filament::auth()->user()->can('View:InstallmentsDueWeek');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.reports_installments');
    }

    public $installmentsByDay = [];
    public $installmentFilter = 'all'; // 🆕 all, overdue, today, upcoming

    // Payment modal properties
    public $showPaymentModal = false;
    public $selectedInstallment = null;
    public $paymentAmount = null;
    public $paymentDate = null;
    public $paymentMethod = 'cash';
    public $vaultId = null;
    public $receiptNumber = null;
    public $paymentNotes = null;

    public function mount(): void
    {
        $this->paymentDate = now()->format('Y-m-d');
        $this->filterInstallments();
    }

    // 🆕 التحديث التلقائي عند تغيير الفلتر
    public function updated($property)
    {
        if ($property === 'installmentFilter') {
            $this->filterInstallments();
        }
    }

    // 🆕 دالة الفلترة المحدثة
    public function filterInstallments(): void
    {
        $startOfWeek = now()->startOfWeek()->format('Y-m-d');
        $endOfWeek = now()->endOfWeek()->format('Y-m-d');
        $today = now()->format('Y-m-d');

        $query = Installment::with(['contract.customer'])
            ->whereBetween('due_date', [$startOfWeek, $endOfWeek])
            ->where('is_paid', false)
            ->whereHas('contract', function ($query) {
                $query->whereIn('status', ['active', 'finished']);
            });

        // 🆕 تطبيق الفلتر
        switch ($this->installmentFilter) {
            case 'overdue':
                $query->where('due_date', '<', $today);
                break;
            case 'today':
                $query->where('due_date', '=', $today);
                break;
            case 'upcoming':
                $query->where('due_date', '>', $today);
                break;
            default:
                // 'all' - no additional filter
                break;
        }

        $installments = $query->orderBy('due_date')->get();

        $days = [];
        foreach ($installments as $installment) {
            $day = Carbon::parse($installment->due_date)->translatedFormat('l Y-m-d');
            $days[$day][] = $installment;
        }
        $this->installmentsByDay = $days;
    }

    // 🆕 دالة الإحصائيات الجديدة
    public function getInstallmentStats(): array
    {
        $startOfWeek = now()->startOfWeek()->format('Y-m-d');
        $endOfWeek = now()->endOfWeek()->format('Y-m-d');
        $today = now()->format('Y-m-d');

        $baseQuery = Installment::whereBetween('due_date', [$startOfWeek, $endOfWeek])
            ->where('is_paid', false)
            ->whereHas('contract', function ($query) {
                $query->whereIn('status', ['active', 'finished']);
            });

        // Overdue (before today)
        $overdueCount = (clone $baseQuery)->where('due_date', '<', $today)->count();
        $overdueAmount = (clone $baseQuery)->where('due_date', '<', $today)->sum('value');

        // Today
        $todayCount = (clone $baseQuery)->where('due_date', '=', $today)->count();
        $todayAmount = (clone $baseQuery)->where('due_date', '=', $today)->sum('value');

        // Upcoming (after today)
        $upcomingCount = (clone $baseQuery)->where('due_date', '>', $today)->count();
        $upcomingAmount = (clone $baseQuery)->where('due_date', '>', $today)->sum('value');

        // Total
        $totalCount = $overdueCount + $todayCount + $upcomingCount;
        $totalAmount = $overdueAmount + $todayAmount + $upcomingAmount;

        return [
            'overdue' => ['count' => $overdueCount, 'amount' => $overdueAmount],
            'today' => ['count' => $todayCount, 'amount' => $todayAmount],
            'upcoming' => ['count' => $upcomingCount, 'amount' => $upcomingAmount],
            'total' => ['count' => $totalCount, 'amount' => $totalAmount],
        ];
    }

    public function openPaymentModal($installmentId)
    {
        $installment = Installment::with('contract.customer')->find($installmentId);

        if (!$installment) {
            Notification::make()
                ->title('خطأ')
                ->body('القسط غير موجود')
                ->danger()
                ->send();
            return;
        }

        $this->selectedInstallment = $installment;
        $this->paymentAmount = $installment->value - ($installment->paid_amount ?? 0);
        $this->paymentDate = now()->format('Y-m-d');
        $this->paymentMethod = 'cash';
        $this->vaultId = null;
        $this->receiptNumber = null;
        $this->paymentNotes = null;
        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->selectedInstallment = null;
    }

    public function savePayment()
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
            'vaultId' => 'required|exists:vaults,id',
            'paymentDate' => 'required|date',
            'paymentMethod' => 'required|in:cash,bank_transfer,check,card',
        ], [
            'paymentAmount.required' => 'المبلغ مطلوب',
            'paymentAmount.numeric' => 'المبلغ يجب أن يكون رقماً',
            'paymentAmount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
            'vaultId.required' => 'الخزنة مطلوبة',
            'vaultId.exists' => 'الخزنة غير موجودة',
            'paymentDate.required' => 'تاريخ الدفع مطلوب',
            'paymentMethod.required' => 'طريقة الدفع مطلوبة',
        ]);

        try {
            $payment = Payment::create([
                'customer_id' => $this->selectedInstallment->contract->customer_id,
                'contract_id' => $this->selectedInstallment->contract_id,
                'vault_id' => $this->vaultId,
                'amount' => $this->paymentAmount,
                'payment_date' => $this->paymentDate,
                'payment_method' => $this->paymentMethod,
                'receipt_number' => $this->receiptNumber ?: 'REC-' . time(),
                'notes' => $this->paymentNotes,
                'status' => 'completed',
            ]);

            Notification::make()
                ->title('تم بنجاح')
                ->body('تم إضافة الدفعة وتوزيعها على الأقساط بنجاح')
                ->success()
                ->send();

            $this->closePaymentModal();
            $this->filterInstallments(); // Refresh data
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطأ')
                ->body('حدث خطأ أثناء حفظ الدفعة: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getVaultsProperty()
    {
        return Vault::where('is_active', true)->pluck('name', 'id')->toArray();
    }
}
```

---

### 3️⃣ تقرير أقساط الشهر (مع التحديثات الجديدة)

#### **الملف الرئيسي (Controller/Page)**
```
app/Filament/Pages/InstallmentsDueMonth.php
```

**الكود الكامل المحدث:**
```php
<?php

namespace App\Filament\Pages;

use App\Models\Installment;
use App\Models\Payment;
use App\Models\Vault;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use BackedEnum;
use Filament\Notifications\Notification;

class InstallmentsDueMonth extends Page
{
    public function getTitle(): string
    {
        return __('filament.nav.installments_due_month');
    }
    
    public static function getNavigationLabel(): string
    {
        return __('filament.nav.installments_due_month');
    }

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';
    protected string $view = 'filament.pages.installments-due-month';

    public static function canAccess(): bool
    {
        return Filament::auth()->user()->can('View:InstallmentsDueMonth');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.reports_installments');
    }

    public $installments;
    public $installmentFilter = 'all'; // 🆕 all, overdue, today, upcoming

    // Payment modal properties
    public $showPaymentModal = false;
    public $selectedInstallment = null;
    public $paymentAmount = null;
    public $paymentDate = null;
    public $paymentMethod = 'cash';
    public $vaultId = null;
    public $receiptNumber = null;
    public $paymentNotes = null;

    public function mount(): void
    {
        $this->paymentDate = now()->format('Y-m-d');
        $this->filterInstallments();
    }

    // 🆕 التحديث التلقائي عند تغيير الفلتر
    public function updated($property)
    {
        if ($property === 'installmentFilter') {
            $this->filterInstallments();
        }
    }

    // 🆕 دالة الفلترة المحدثة
    public function filterInstallments(): void
    {
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = now()->endOfMonth()->format('Y-m-d');
        $today = now()->format('Y-m-d');

        $query = Installment::with(['contract.customer'])
            ->whereBetween('due_date', [$startOfMonth, $endOfMonth])
            ->where('is_paid', false)
            ->whereHas('contract', function ($query) {
                $query->whereIn('status', ['active', 'finished']);
            });

        // 🆕 تطبيق الفلتر
        switch ($this->installmentFilter) {
            case 'overdue':
                $query->where('due_date', '<', $today);
                break;
            case 'today':
                $query->where('due_date', '=', $today);
                break;
            case 'upcoming':
                $query->where('due_date', '>', $today);
                break;
            default:
                // 'all' - no additional filter
                break;
        }

        $this->installments = $query->orderBy('due_date')->get();
    }

    // 🆕 دالة الإحصائيات الجديدة
    public function getInstallmentStats(): array
    {
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = now()->endOfMonth()->format('Y-m-d');
        $today = now()->format('Y-m-d');

        $baseQuery = Installment::whereBetween('due_date', [$startOfMonth, $endOfMonth])
            ->where('is_paid', false)
            ->whereHas('contract', function ($query) {
                $query->whereIn('status', ['active', 'finished']);
            });

        // Overdue (before today)
        $overdueCount = (clone $baseQuery)->where('due_date', '<', $today)->count();
        $overdueAmount = (clone $baseQuery)->where('due_date', '<', $today)->sum('value');

        // Today
        $todayCount = (clone $baseQuery)->where('due_date', '=', $today)->count();
        $todayAmount = (clone $baseQuery)->where('due_date', '=', $today)->sum('value');

        // Upcoming (after today)
        $upcomingCount = (clone $baseQuery)->where('due_date', '>', $today)->count();
        $upcomingAmount = (clone $baseQuery)->where('due_date', '>', $today)->sum('value');

        // Total
        $totalCount = $overdueCount + $todayCount + $upcomingCount;
        $totalAmount = $overdueAmount + $todayAmount + $upcomingAmount;

        return [
            'overdue' => ['count' => $overdueCount, 'amount' => $overdueAmount],
            'today' => ['count' => $todayCount, 'amount' => $todayAmount],
            'upcoming' => ['count' => $upcomingCount, 'amount' => $upcomingAmount],
            'total' => ['count' => $totalCount, 'amount' => $totalAmount],
        ];
    }

    // Same payment methods as Week report...
    public function openPaymentModal($installmentId) { /* ... */ }
    public function closePaymentModal() { /* ... */ }
    public function savePayment() { /* ... */ }
    public function getVaultsProperty() { /* ... */ }
}
```

---

## 🎨 واجهة العرض (Blade Template) - التحديثات الجديدة

### ملف العرض للأسبوع/الشهر (مع الإحصائيات والفلترة)

```blade
<div id="due-week-report" dir="rtl">
    <!-- Header and Styles -->
    <style>
        /* ... تصميم احترافي ... */
    </style>

    <div class="toolbar">
        <button class="btn" onclick="window.print()">🖨️ طباعة</button>
    </div>

    <h2 class="title">تقرير الأقساط المستحقة هذا الأسبوع</h2>

    <!-- 🆕 بطاقات الإحصائيات (تظهر فقط على الشاشة) -->
    @php
        $stats = $this->getInstallmentStats();
    @endphp
    
    <div class="no-print" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <!-- إجمالي الأقساط -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 16px; border-radius: 12px; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);">
            <div style="font-size: 12px; opacity: 0.9; margin-bottom: 4px;">📊 إجمالي الأقساط</div>
            <div style="font-size: 24px; font-weight: bold;">{{ $stats['total']['count'] }}</div>
            <div style="font-size: 14px; margin-top: 4px;">{{ number_format($stats['total']['amount'], 2) }} ج.م</div>
        </div>
        
        <!-- الأقساط المتأخرة -->
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 16px; border-radius: 12px; box-shadow: 0 4px 12px rgba(245, 87, 108, 0.4);">
            <div style="font-size: 12px; opacity: 0.9; margin-bottom: 4px;">⚠️ متأخرة</div>
            <div style="font-size: 24px; font-weight: bold;">{{ $stats['overdue']['count'] }}</div>
            <div style="font-size: 14px; margin-top: 4px;">{{ number_format($stats['overdue']['amount'], 2) }} ج.م</div>
        </div>
        
        <!-- أقساط اليوم -->
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 16px; border-radius: 12px; box-shadow: 0 4px 12px rgba(79, 172, 254, 0.4);">
            <div style="font-size: 12px; opacity: 0.9; margin-bottom: 4px;">📅 اليوم</div>
            <div style="font-size: 24px; font-weight: bold;">{{ $stats['today']['count'] }}</div>
            <div style="font-size: 14px; margin-top: 4px;">{{ number_format($stats['today']['amount'], 2) }} ج.م</div>
        </div>
        
        <!-- الأقساط القادمة -->
        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 16px; border-radius: 12px; box-shadow: 0 4px 12px rgba(67, 233, 123, 0.4);">
            <div style="font-size: 12px; opacity: 0.9; margin-bottom: 4px;">🔜 قادمة</div>
            <div style="font-size: 24px; font-weight: bold;">{{ $stats['upcoming']['count'] }}</div>
            <div style="font-size: 14px; margin-top: 4px;">{{ number_format($stats['upcoming']['amount'], 2) }} ج.م</div>
        </div>
    </div>

    <!-- 🆕 قائمة الفلترة (تظهر فقط على الشاشة) -->
    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <label style="font-weight: bold; margin-left: 10px; color: #3730a3;">تصفية حسب:</label>
        <select wire:model.live="installmentFilter" 
                style="padding: 8px 16px; border-radius: 8px; border: 2px solid #c7d2fe; 
                       background: white; color: #3730a3; font-weight: bold; 
                       cursor: pointer; transition: all 0.3s;">
            <option value="all">📊 الكل ({{ $stats['total']['count'] }})</option>
            <option value="overdue">⚠️ المتأخرة ({{ $stats['overdue']['count'] }})</option>
            <option value="today">📅 اليوم ({{ $stats['today']['count'] }})</option>
            <option value="upcoming">🔜 القادمة ({{ $stats['upcoming']['count'] }})</option>
        </select>
    </div>

    <!-- جدول البيانات -->
    @php
        $weekTotal = 0;
        $weekPaid = 0;
        $weekRemaining = 0;
        $weekCount = 0;
    @endphp
    
    @foreach($installmentsByDay as $day => $installments)
        <!-- عرض الأقساط لكل يوم -->
        <div class="card">
            <h3>{{ $day }}</h3>
            <table>
                <thead>
                    <tr>
                        <th>اسم العميل</th>
                        <th>رقم الهاتف</th>
                        <th>قيمة القسط</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                        <th>حالة السداد</th>
                        <th class="no-print">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($installments as $installment)
                        <tr>
                            <td>{{ $installment->contract->customer->name ?? '-' }}</td>
                            <td>{{ $installment->contract->customer->phone ?? '-' }}</td>
                            <td>{{ number_format($installment->value, 2) }}</td>
                            <td>{{ number_format($installment->paid_amount ?? 0, 2) }}</td>
                            <td>{{ number_format($installment->value - ($installment->paid_amount ?? 0), 2) }}</td>
                            <td>
                                @if($installment->is_paid)
                                    <span style="color: green;">✅ مسدد</span>
                                @else
                                    <span style="color: red;">❌ غير مسدد</span>
                                @endif
                            </td>
                            <td class="no-print">
                                @if(!$installment->is_paid)
                                    <button wire:click="openPaymentModal({{ $installment->id }})" 
                                            style="padding: 6px 12px; background: #10b981; color: white; 
                                                   border: none; border-radius: 6px; cursor: pointer;">
                                        💰 سداد
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <!-- الإجماليات -->
    <div style="margin: 16px 0; font-weight: bold; color: #3730a3;">
        العدد: {{ $weekCount }}<br>
        الإجمالي: {{ number_format($weekTotal, 2) }} ج.م<br>
        المدفوع: {{ number_format($weekPaid, 2) }} ج.م<br>
        المتبقي: {{ number_format($weekRemaining, 2) }} ج.م
    </div>

    <!-- Payment Modal (نافذة السداد) -->
    @if($showPaymentModal && $selectedInstallment)
        <div class="modal-overlay" wire:click="closePaymentModal" 
             style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                    background: rgba(0,0,0,0.5); display: flex; align-items: center; 
                    justify-content: center; z-index: 9999;">
            <div wire:click.stop style="background: white; padding: 24px; border-radius: 12px; 
                                        max-width: 500px; width: 90%;">
                <h3>سداد قسط</h3>
                <!-- Payment form fields -->
            </div>
        </div>
    @endif
</div>
```

---

## 🔧 خطوات التطبيق في نظام جديد

### 1. **إنشاء جدول الأقساط (Migration)**

```php
Schema::create('installments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('contract_id')->constrained()->onDelete('cascade');
    $table->date('due_date');
    $table->decimal('value', 15, 2);
    $table->decimal('paid_amount', 15, 2)->default(0);
    $table->boolean('is_paid')->default(false);
    $table->date('payment_date')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### 2. **موديل Installment**

```php
class Installment extends Model
{
    protected $fillable = [
        'contract_id', 'due_date', 'value', 
        'paid_amount', 'is_paid', 'payment_date', 'notes'
    ];

    protected $casts = [
        'due_date' => 'date',
        'payment_date' => 'date',
        'is_paid' => 'boolean',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
```

### 3. **موديل Contract مع العلاقات**

```php
class Contract extends Model
{
    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
```

### 4. **إنشاء الـ Page Classes**

انسخ ملفات:
- `InstallmentsDueToday.php`
- `InstallmentsDueWeek.php`
- `InstallmentsDueMonth.php`

إلى مجلد `app/Filament/Pages/`

### 5. **إنشاء ملفات View**

انسخ ملفات Blade:
- `installments-due-today.blade.php`
- `installments-due-week.blade.php`
- `installments-due-month.blade.php`

إلى مجلد `resources/views/filament/pages/`

### 6. **إضافة الصلاحيات (Permissions)**

```php
// في Seeder الصلاحيات
$permissions = [
    'View:InstallmentsDueToday',
    'View:InstallmentsDueWeek',
    'View:InstallmentsDueMonth',
];

foreach ($permissions as $permission) {
    Permission::create(['name' => $permission]);
}
```

### 7. **إضافة الترجمات**

ملف `lang/ar/filament.php`:
```php
'nav' => [
    'installments_due_today' => 'أقساط اليوم',
    'installments_due_week' => 'أقساط الأسبوع',
    'installments_due_month' => 'أقساط الشهر',
    'reports_installments' => 'تقارير الأقساط',
],
```

### 8. **تسجيل الصفحات في Filament Panel Provider**

```php
// في AppPanelProvider.php
use App\Filament\Pages\InstallmentsDueToday;
use App\Filament\Pages\InstallmentsDueWeek;
use App\Filament\Pages\InstallmentsDueMonth;

public function panel(Panel $panel): Panel
{
    return $panel
        ->pages([
            InstallmentsDueToday::class,
            InstallmentsDueWeek::class,
            InstallmentsDueMonth::class,
        ]);
}
```

---

## ⚙️ الميزات الأساسية

### ✅ **الإحصائيات الذكية**
- عدد ومبلغ الأقساط المتأخرة
- عدد ومبلغ أقساط اليوم
- عدد ومبلغ الأقساط القادمة
- الإجمالي الكلي

### ✅ **الفلترة الديناmiكية**
- فلترة فورية بدون تحديث الصفحة (`wire:model.live`)
- عرض عدد الأقساط بجانب كل خيار
- تحديث تلقائي للبيانات

### ✅ **السداد المباشر**
- نافذة منبثقة لسداد القسط
- اختيار الخزينة وطريقة الدفع
- توزيع تلقائي على الأقساط
- إشعارات نجاح/فشل

### ✅ **الطباعة**
- إخفاء الفلاتر والإحصائيات عند الطباعة
- تصميم احترافي للطباعة A4
- عرض فقط البيانات الأساسية

### ✅ **التصميم**
- بطاقات Gradient ملونة
- Responsive Design
- Dark Mode Support
- أيقونات Emoji واضحة

---

## 📊 الفرق بين النسخة القديمة والجديدة

| الميزة | النسخة القديمة | النسخة الجديدة (2026) |
|--------|----------------|---------------------|
| **الإحصائيات** | ❌ غير موجودة | ✅ 4 بطاقات إحصائية ملونة |
| **الفلترة** | ❌ عرض الكل فقط | ✅ فلترة ديناميكية (متأخرة/اليوم/قادمة/الكل) |
| **التحديث التلقائي** | ❌ يتطلب تحديث يدوي | ✅ `wire:model.live` |
| **عدد الأقساط في الفلتر** | ❌ غير معروض | ✅ معروض بجانب كل خيار |
| **دالة getInstallmentStats()** | ❌ غير موجودة | ✅ موجودة |
| **دالة filterInstallments()** | ✅ بسيطة | ✅ محسّنة مع switch case |
| **updated() method** | ❌ غير موجود | ✅ للتحديث التلقائي |

---

## 🎯 حالات الاستخدام

### 1. **مدير التحصيل**
- يفتح تقرير اليوم صباحاً
- يستخدم الفلتر "اليوم" لرؤية المستحق اليوم فقط
- يستخدم الفلتر "المتأخرة" لمتابعة المتأخرات
- يسدد الأقساط مباشرة من التقرير

### 2. **إدارة المبيعات**
- تفتح تقرير الأسبوع لتخطيط التحصيل
- تستخدم الإحصائيات لمعرفة الأولويات
- تطبع التقرير للفريق الميداني

### 3. **المحاسب**
- يفتح تقرير الشهر لعمل تسويات
- يستخدم فلتر "القادمة" للتخطيط المالي
- يستخدم فلتر "المتأخرة" لإجراءات المتابعة

---

## 🚀 نصائح للتطبيق

1. **التأكد من Livewire**: النظام يعتمد على Livewire للفلترة الديناميكية
2. **الصلاحيات**: أضف صلاحيات مناسبة لكل تقرير
3. **الأداء**: إذا كان لديك آلاف الأقساط، استخدم pagination
4. **التخصيص**: يمكن تعديل الألوان في Blade templates
5. **الترجمة**: أضف ترجمات إضافية حسب الحاجة

---

## 📝 ملاحظات مهمة

- ✅ النظام يستخدم **Filament Pages** وليس Resources
- ✅ **wire:model.live** يتطلب Livewire 3+
- ✅ الإحصائيات تحسب فقط الأقساط غير المسددة
- ✅ الفلترة لا تؤثر على الطباعة
- ✅ Modal السداد يدعم Livewire click events

---

## 🎨 تخصيص الألوان

يمكنك تغيير ألوان البطاقات بسهولة:

```blade
<!-- إجمالي: بنفسجي -->
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

<!-- متأخرة: وردي/أحمر -->
background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);

<!-- اليوم: أزرق -->
background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);

<!-- قادمة: أخضر -->
background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
```

---

## 📞 الدعم

إذا واجهت أي مشكلة أثناء التطبيق:
1. تأكد من إصدار Livewire (يجب أن يكون 3+)
2. تأكد من تفعيل Livewire في Filament Panel
3. تأكد من وجود الصلاحيات المناسبة
4. راجع Console للأخطاء JavaScript

---

**آخر تحديث:** 30 مارس 2026  
**الإصدار:** 2.0 (مع الإحصائيات والفلاتر الديناميكية)
