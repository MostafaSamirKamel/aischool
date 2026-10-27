# 12 — MODIFIED FILES INVENTORY

## 1. Frontend Schemas & Pages
- `app/Filament/Admin/Resources/Payments/Schemas/PaymentForm.php`:
  - Removed independent `payment_method` selector.
  - Made `vault_id` mandatory receiving account selector with Arabic labeling.
- `app/Filament/Admin/Resources/Payments/Pages/CreatePayment.php`:
  - Added automatic derivation of `payment_method` from vault type.
  - Added backend tenant isolation and active vault verification.
- `app/Filament/Admin/Resources/Payments/Pages/EditPayment.php`:
  - Added vault type derivation for payment method on edits.
- `app/Filament/Admin/Resources/Expenses/Schemas/ExpenseForm.php`:
  - Enforced conditional required vault validation when `status === 'paid'`.
- `app/Filament/Admin/Resources/Enrollments/Schemas/EnrollmentForm.php`:
  - Enforced conditional required vault validation when `paid_amount > 0`.
- `app/Filament/Admin/Resources/Enrollments/Pages/CreateEnrollment.php`:
  - Sets `vault_id = null` when `paid_amount <= 0` to prevent phantom vault attachments on unpaid obligations.

## 2. Eloquent Backend Models
- `app/Models/Payment.php`: Added `static::saving` hook enforcing active tenant-matching vault for completed payments.
- `app/Models/Expense.php`: Added `static::saving` hook enforcing active tenant vault and balance check for paid expenses.
- `app/Models/SalaryPayment.php`: Added `static::saving` hook enforcing active tenant vault and balance check for paid salaries.
- `app/Models/Enrollment.php`: Added `static::saving` hook enforcing vault for upfront payments and reimbursing vault for refunds. Added `static::saved` hook to sync group capacity.
- `app/Models/Revenue.php`: Added `static::saving` hook enforcing active tenant vault for received revenues.
- `app/Models/VaultTransfer.php`: Added `static::saving` hook verifying both source and destination vaults.
- `app/Models/Vault.php`: Hardened `getCurrentBalance()` to only deduct paid expenses (`whereIn('status', ['paid', 'completed'])`).
- `app/Models/Group.php`: Updated `activeStudents()` to support both `enrollments.group_id` and `enrollment_courses.group_id`.

## 3. Financial Services
- `app/Services/Enrollment/EnrollmentCancellationService.php`:
  - Hardened refund execution with vault linkage.
  - Removed duplicate Expense creation on simplified cancellation.
- `app/Services/Reporting/ReportingCenterService.php`:
  - Updated expense queries in vault reports to use paid/completed statuses.