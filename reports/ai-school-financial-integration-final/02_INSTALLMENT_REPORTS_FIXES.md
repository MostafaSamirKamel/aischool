# 02 — INSTALLMENT REPORTS FIXES & UNIFIED BACKEND INTEGRATION

## 1. Architectural Architecture & Core Fixes
To eliminate fragmentation, discrepancies, and duplicate arithmetic across the three report pages, an authoritative shared reporting and collection architecture was implemented:

```
[Filament Report Pages]
├── InstallmentsDueToday
├── InstallmentsDueWeek
└── InstallmentsDueMonth
       │
       ├── uses HandlesInstallmentDuePayments (Payment Modal & ACID Transaction Collection)
       ├── delegates queries to InstallmentDueReportService (Unified Calculations & whereDate())
       └── delegates authorization to InstallmentDueReportAccess (Separation of View vs. Collect)
```

---

## 2. Implemented Fixes Details

### Fix 1: Authoritative Shared Reporting Service (`InstallmentDueReportService.php`)
- **Location:** `app/Services/InstallmentDueReportService.php`
- **Correction:** Implemented standardized query builders for daily, weekly, and monthly due dates using `whereDate('installments.due_date', ...)` rather than raw string comparison.
- **Methods:**
  - `getTodayInstallments(?int $tenantId, string $statusFilter = 'all')`
  - `getTodayStats(?int $tenantId)`
  - `getWeekInstallmentsQuery(?int $tenantId, Carbon $startDate, Carbon $endDate, string $filter = 'all')`
  - `getWeekInstallmentsByDay(?int $tenantId, Carbon $startDate, Carbon $endDate, string $filter = 'all')`
  - `getWeekStats(?int $tenantId, Carbon $startDate, Carbon $endDate)`
  - `getMonthInstallmentsQuery(?int $tenantId, int $year, int $month, string $filter = 'all')`
  - `getMonthInstallments(?int $tenantId, int $year, int $month, string $filter = 'all')`
  - `getMonthStats(?int $tenantId, int $year, int $month)`
  - `getActiveVaults(?int $tenantId)`

### Fix 2: Shared Livewire Payment Handler (`HandlesInstallmentDuePayments.php`)
- **Location:** `app/Filament/Admin/Pages/Concerns/HandlesInstallmentDuePayments.php`
- **Correction:** Standardized modal state and collection execution. Wrapped payment creation in `DB::transaction()` with row-level pessimistic locking (`Installment::lockForUpdate()`), preventing duplicate concurrent submissions. Generates unique `TXN-` receipt codes.

### Fix 3: In-Memory Model Attributes (`Payment.php`)
- **Location:** `app/Models/Payment.php`
- **Correction:** Added `protected $attributes = ['status' => 'completed', 'payment_method' => 'cash'];` guaranteeing that model instances created via `Payment::create()` have `status = 'completed'` immediately in memory when `PaymentObserver` fires.

### Fix 4: PaymentObserver Overdue Synchronization (`PaymentObserver.php`)
- **Location:** `app/Observers/PaymentObserver.php`
- **Correction:** Synchronized overdue logic in `recalculateStudentInstallments()`. If an installment has an unpaid balance and `Carbon::parse($due_date)->isPast()`, its status transitions to `overdue`, matching `Installment::updateStatus()`.

### Fix 5: Permissions & Tenant Isolation (`InstallmentDueReportAccess.php`)
- **Location:** `app/Services/InstallmentDueReportAccess.php`
- **Correction:** Decoupled `canViewReport()` from `canCollectPayment()`. Only users with `create_student_payments` or super admins can collect payments. Scoped vaults and installments to `$user->tenant_id`.
