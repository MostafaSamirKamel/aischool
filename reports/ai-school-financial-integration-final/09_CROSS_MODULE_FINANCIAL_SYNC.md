# 09 — CROSS-MODULE FINANCIAL SYNCHRONIZATION

## 1. Unified Architecture: One Authoritative Financial Truth
A primary objective of this maintenance is ensuring that AI School possesses **one single financial reality**, regardless of which screen initiates an operation:

```
                          ┌───────────────────────────┐
                          │   Payment Collection UI   │
                          └─────────────┬─────────────┘
                                        │
           ┌────────────────────────────┴───────────────────────────┐
           ▼                                                        ▼
[Report Collection Modal]                               [Primary Payment Screen]
(/admin/installments-due-*)                             (/admin/payments/create)
           │                                                        │
           └────────────────────────────┬───────────────────────────┘
                                        │
                                        ▼
                            [Authoritative Domain]
                            Payment::create()
                                        │
                                        ▼
                            [PaymentObserver Engine]
           ┌────────────────────────────┼───────────────────────────┐
           ▼                            ▼                           ▼
[Revenue Created]           [Oldest-First Allocation]   [Enrollment Balance Sync]
(revenues.payment_id)       (installments.paid_amount)  (enrollment.paid_amount)
           │                            │                           │
           └────────────────────────────┼───────────────────────────┘
                                        │
                                        ▼
                            [System-Wide Views]
  ├── Daily, Weekly, Monthly Reports (/admin/installments-due-*)
  ├── Student Financial Profile (/admin/enrollments/{record})
  ├── Student Portfolio Report (/admin/student-portfolio-report)
  ├── Authorized Vault Balance (Vault::getCurrentBalance())
  ├── Parent Portal (/parent/my-payments)
  └── Student Portal (/student/my-payments)
```

---

## 2. Bidirectional Synchronization Verification

### Scenario 1: Payment Collected via Installment Report Action
1. Cashier opens `/admin/installments-due-today`.
2. Clicks "تحصيل" on Installment #1 (1,000 EGP), submits 400 EGP.
3. **Synchronized Outcomes:**
   - **Daily, Weekly, Monthly Reports:** Installment shows Paid = 400 EGP, Remaining = 600 EGP, Status = `partial`.
   - **Primary Payment Screen (`/admin/payments/create`):** Selecting this student immediately displays `total_paid = 400 EGP` and `remaining_balance = 1600 EGP` (accounting for second installment).
   - **Student Financial Profile (`/admin/enrollments/{id}`):** Installments widget immediately reflects Paid: 400 EGP, Remaining: 600 EGP with 40% progress bar.
   - **Vault Ledger:** Balance increased by exactly 400 EGP.

### Scenario 2: Payment Collected via Primary Payment Screen
1. Cashier opens `/admin/payments/create`.
2. Selects student, records 600 EGP payment for course enrollment.
3. **Synchronized Outcomes:**
   - **Installment Reports:** Installment #1 transitions to `paid` (0 remaining).
   - **Student Financial Profile:** Installment #1 badge shows `مكتمل` (100% green).
   - **Vault Ledger:** Balance increased by exactly 600 EGP.
   - **Parent Portal (`/parent/my-payments`):** Both 400 EGP and 600 EGP payment rows are displayed with official receipt codes and course titles.

---

## 3. Financial Invariant Guarantee
At all times across all modules:
```
Σ(Payments with status = completed) 
  = Σ(Installment Paid Amounts) 
  = Enrollment Paid Amount 
  = Increase in Authorized Vault Balances
```
There is zero drift, zero double-recognition, and zero discrepancy between screens.
