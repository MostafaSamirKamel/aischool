# 09 — Financial Transactions, Installments & Vault Treasury

## Overview
This document details the financial architecture, installment schedules, vault collections, operational disbursements, and cancellation settlements populated in the AI School financial modules.

---

## 1. Installment Schedules Master Summary

A total of **66 new installments** were generated (bringing the system total to **224 installments**):

| Installment Status | Database Value | Count | Total Value (EGP) | Financial Significance |
|---|---|---|---|---|
| **Paid (مسدد بالكامل)** | `paid` | **127** | 148,500 | Fully collected; tied to payment receipts |
| **Pending (مستحق قادم)** | `pending` | **74** | 88,800 | Scheduled future receivables |
| **Overdue (متأخر السداد)** | `overdue` | **8** | 9,600 | Matured past due date; appears in Aging Debt Report |
| **Cancelled (ملغى بتسوية)** | `cancelled` | **15** | 18,000 | Waived or cancelled following course withdrawal |
| **Total In Database** | — | **224** | **264,900 EGP** | 100% Reconciled |

---

## 2. Vault Collections & Official Receipts

A total of **33 new payment receipts** were issued (bringing system total to **146 payments** and **161,150 EGP** collected):

| Receipt Metric | Value |
|---|---|
| **Total Recorded Payments** | **146 receipts** |
| **Total Vault Inflows Collected** | **161,150.00 EGP** |
| **Vault 1 (الخزينة الرئيسية - فرع مدينة نصر)** | 114,250.00 EGP |
| **Vault 2 (خزينة فرع الجيزة)** | 46,900.00 EGP |
| **Receipt Tracking** | Auto-generated sequential receipts (e.g. `REC-202610-0033`) |
| **Authorized Collector** | Verified Admin / Cashier (`user_id = 1`) |

---

## 3. Treasury Operations & Operating Expenses

To ensure comprehensive coverage of the Financial Reports and P&L module:

1. **Operating Expenses**:
   - Total in database: **7 expenses** (5,500.00 EGP)
   - Sample new entry: `فاتورة استضافة وخوادم المنصة السحابية - أكتوبر 2026` (1,200.00 EGP) from Vault 1.
2. **Revenues (Non-Tuition / Additional)**:
   - Total in database: **112 records**
   - Sample new entry: `رسوم استخراج شهادات وبيان درجات معتمد` (350.00 EGP).
3. **Vault Transfers**:
   - Total in database: **2 transfers**
   - Sample new entry: Internal cash transit of 10,000.00 EGP from Vault 2 (Giza) to Vault 1 (Main Cairo Vault).
4. **Instructor Salary Disbursements**:
   - Total in database: **10 salary payments**
   - Sample new entry: Official monthly honorarium disbursed to instructor د. عصام المنشاوي.

---

## 4. Enrollment Cancellations & Refund Settlements

A dedicated cohort of **12 student cancellations** was processed through the official cancellation workflow:
- **Settlement Rule**:
  - `retained_amount`: 300.00 EGP (Non-refundable administrative & registration fee).
  - `waived_amount`: 1,200.00 EGP (Unattended upcoming terms wiped from balance).
  - `refundable_amount`: 500.00 EGP (Approved refund liability created in demo environment).
- **Audit Consistency**:
  - Remaining balances for cancelled students reflect zero outstanding debt.
  - Cancellations appear accurately in the Enrollment Cancellation & Settlement Report.
