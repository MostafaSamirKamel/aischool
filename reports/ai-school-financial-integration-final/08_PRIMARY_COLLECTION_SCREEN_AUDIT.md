# 08 — PRIMARY PAYMENT COLLECTION SCREEN AUDIT

## 1. Scope & Screen Overview
- **Route:** `/admin/payments/create`
- **Controller/Page:** `app/Filament/Admin/Resources/Payments/Pages/CreatePayment.php`
- **Form Schema:** `app/Filament/Admin/Resources/Payments/Schemas/PaymentForm.php`

The primary payment collection screen serves as the central cashier desk for recording tuition payments, installments, and fees.

---

## 2. Component & Field Mapping Audit

| Field / Component | Filament Form Element | Interactive Event / Method | Backend Service / Observer | Database Persistence |
| :--- | :--- | :--- | :--- | :--- |
| **Barcode Scanner** | `TextInput::make('barcode')` | `->live()->afterStateUpdated(...)` | Queries student by barcode/code | Triggers `hydrateEnrollmentData()` on match |
| **Student Selector** | `Select::make('student_id')` | `->searchable()->live()->afterStateUpdated(...)` | Resolves student's active enrollments, courses, tracks, and installments | Hydrates enrollment totals into form fields |
| **Track & Course Info** | `TextInput::make('track_name')`, `TextInput::make('courses_total')` | Auto-populated by `hydrateEnrollmentData()` | Sums prices of all enrolled courses | Read-only visual confirmation |
| **Financial Ledger Details** | `paid_upfront`, `total_discount`, `other_payments`, `total_paid`, `remaining_balance` | Real-time calculation | Computes: `remaining = totalCourses - discount - totalPaid` | Informs cashier of current outstanding debt |
| **Payment Code** | `TextInput::make('payment_code')` | Unique validation rule | `Payment::generatePaymentCode()` | Stored in `payments.payment_code` |
| **Payment Amount** | `TextInput::make('amount')` | `required()->numeric()` | Validated against remaining debt | Stored in `payments.amount` |
| **Vault Dropdown** | `Select::make('vault_id')` | Filtered by user's tenant | Scoped to active vaults | Stored in `payments.vault_id` |
| **Payment Date** | `DatePicker::make('payment_date')` | `required()->date()` | Defaults to current date | Stored in `payments.payment_date` |
| **Receipt Reference** | `TextInput::make('transaction_reference')` | `nullable()->maxLength(255)` | Cashier reference notes | Stored in `payments.transaction_reference` |
| **Receiver** | `Select::make('received_by')` | `required()->default(auth()->id())` | Scoped to active staff | Stored in `payments.received_by` |
| **Notes** | `RichEditor::make('notes')` | Optional remarks | Auditor notes | Stored in `payments.notes` |

---

## 3. Backend Lifecycle & Synchronization
1. When the cashier clicks "Save", `CreatePayment::create()` executes.
2. `mutateFormDataBeforeCreate()` injects `$data['tenant_id'] = auth()->user()->tenant_id`.
3. `Payment::create()` is committed.
4. **Authoritative `PaymentObserver` triggers:**
   - Creates a matching `Revenue` record tagged with `payment_id`.
   - Recalculates student installments oldest-first (`PaymentObserver::recalculateStudentInstallments`).
   - Updates `enrollment.paid_amount` and `enrollment.remaining_balance`.
   - Increases vault cash balance without double-counting.
5. **Instant Synchronization:**
   - The installment reports (`/today`, `/week`, `/month`) immediately reflect the reduced remaining installment balance.
   - The student financial profile (`/admin/enrollments/{id}`) immediately reflects the new payment in the installments widget.
   - The parent portal (`/parent/my-payments`) immediately lists the new receipt.
