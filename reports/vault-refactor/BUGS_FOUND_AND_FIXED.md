# BUGS FOUND AND FIXED REPORT
## Detailed Catalog of Discovered Defects, Root Cause Analysis & Corrective Actions

---

### Bug 01 — Filament SelectFilter Closure Parameter Null Error

- **Location:** `app/Filament/Admin/Resources/Payments/Tables/PaymentsTable.php` (and corresponding tables in Expenses, Salary Payments, Revenues).
- **Symptom:** Accessing `/admin/payments` triggered a `HTTP 500` error:
  `Error: Call to a member function where() on null at PaymentsTable.php:96`
- **Root Cause:**
  The relationship filter closure was written as `fn($q) => $q->where('is_active', true)...`.
  In Filament's `SelectFilter::relationship`, the closure is evaluated via dependency injection expecting either a parameter explicitly named `$query` or typed `\Illuminate\Database\Eloquent\Builder $query`. Because `$q` matched neither, Filament injected `NULL`, causing a fatal error upon calling `->where()`.
- **Resolution:**
  Updated all relationship filter closures across `PaymentsTable.php`, `ExpensesTable.php`, `SalaryPaymentsTable.php`, and `RevenuesTable.php` to:
  `fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('is_active', true)->when(...)`.

---

### Bug 02 — VaultTransfer Exception Message Conflict with Unauthorized Masking

- **Location:** `app/Models/VaultTransfer.php:64-69`
- **Symptom:** `FinancialIntegrityTest.php` failed asserting that the thrown exception message contained `'رصيد الخزنة المحول منها غير كافٍ'`.
- **Root Cause:**
  When no authenticated user was logged in (CLI or unit test environment), the code defaulted to the unauthorized user message (`"لا يوجد رصيد كافٍ في الخزنة المختارة لإتمام العملية."`), hiding the detailed transfer reason from legitimate administrative and automated testing callers.
- **Resolution:**
  Refined unauthorized detection to only apply when an authenticated employee is logged in AND is neither a super admin nor possesses `view_vault_balances`:
  ```php
  $isUnauthorized = auth()->check() && !auth()->user()->is_super_admin && !auth()->user()->can('view_vault_balances');
  if ($isUnauthorized) {
      throw new \Exception('لا يوجد رصيد كافٍ في الخزنة المختارة لإتمام العملية.');
  }
  throw new \Exception('لا يمكن إتمام التحويل: رصيد الخزنة المحول منها غير كافٍ. الرصيد الحالي: ' . number_format($fromBalance, 2) . ' ج.م');
  ```

---

### Bug 03 — StatsOverview Total Vault Balance Card Count Discrepancy

- **Location:** `tests/Feature/DashboardChartsAccuracyTest.php:41`
- **Symptom:** `Failed asserting that 3 is identical to 4.`
- **Root Cause:**
  `StatsOverview.php` dynamically includes the 4th KPI card (Total Vault Balance) only if the current user has `view_vault_balances` permission or is super admin. The test initialized an `$adminUser` with `is_super_admin => false` and no assigned permissions, causing the widget to rightfully hide the financial balance card and return only 3 cards.
- **Resolution:**
  Configured `$adminUser` with `is_super_admin => true` in `DashboardChartsAccuracyTest.php`, confirming that all 4 KPI cards and their trend charts render accurately when authorized.

---

### Bug 04 — Payment Code NOT NULL Constraint on Direct Model Creation

- **Location:** `app/Models/Payment.php`
- **Symptom:** Direct creation of `Payment::create([...])` without passing `'payment_code'` triggered SQLite error:
  `Integrity constraint violation: 19 NOT NULL constraint failed: payments.payment_code`
- **Root Cause:**
  While `Payment::generatePaymentCode()` was implemented on the model, it lacked a `creating` Eloquent boot hook to auto-populate the code if omitted during programmatic or API creation.
- **Resolution:**
  Added the standard `boot` method to `Payment.php`:
  ```php
  protected static function boot()
  {
      parent::boot();

      static::creating(function ($payment) {
          if (empty($payment->payment_code)) {
              $payment->payment_code = static::generatePaymentCode();
          }
      });
  }
  ```

---

### Bug 05 — RolesAndPermissionsSeeder Missing Vault Visibility Permissions

- **Location:** `database/seeders/RolesAndPermissionsSeeder.php`
- **Symptom:** Running test database resets or fresh seeding omitted `view_vault_balances` and `view_vault_transactions` from the administrative roles.
- **Root Cause:**
  The seeder file had not yet integrated the new granular vault visibility permissions into `$allPermissions` and `$admin->givePermissionTo(...)`.
- **Resolution:**
  Added `$vaultPermissions` array containing `view_vaults`, `view_any_vaults`, `create_vaults`, `update_vaults`, `delete_vaults`, `view_vault_balances`, and `view_vault_transactions` to `RolesAndPermissionsSeeder.php`.
