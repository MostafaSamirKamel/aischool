# 01 — ALL MONEY MOVEMENT ENTRY POINTS AUDIT

## 1. Executive Summary
This document provides a comprehensive audit of every financial entry point capable of receiving or disbursing money within AI School (Laravel 12 / Filament v3 / Livewire). In accordance with the mandatory vault directive, independent alternative payment methods (Cash, Card, Bank, Wallet, Other) have been removed, and every real money-moving form now strictly requires an existing, active, authorized vault.

## 2. Complete Inventory of Financial Workflows

| # | Screen / Module | UI Action | Backend Handler | Authoritative Accounting Service | Source Model | Real Money Moves? | Required Vault Type | Legacy Method Derived From |
|---|---|---|---|---|---|---|---|---|
| 1 | Student Payments | Add Payment (`/admin/payments/create`) | `CreatePayment::class` | `PaymentObserver::class` | `Payment` | **YES** | Receiving Vault (`vault_id`) | Vault Type (`$vault->type`) |
| 2 | Student Payments | Edit Payment (`/admin/payments/{record}/edit`) | `EditPayment::class` | `PaymentObserver::class` | `Payment` | **YES** | Receiving Vault (`vault_id`) | Vault Type |
| 3 | Expenses | Add Expense (`/admin/expenses/create`) | `CreateExpense::class` | `Expense::saving` Hook | `Expense` | **YES (when paid)** | Disbursing Vault (`vault_id`) | Vault Type |
| 4 | Revenues | Add Revenue (`/admin/revenues/create`) | `CreateRevenue::class` | `Revenue::saving` Hook | `Revenue` | **YES (when received)** | Receiving Vault (`vault_id`) | Vault Type |
| 5 | Instructor Salaries | Pay Salary (`/admin/salary-payments/create`) | `CreateSalaryPayment::class` | `SalaryPayment::saving` Hook | `SalaryPayment` | **YES (when paid)** | Disbursing Vault (`vault_id`) | Vault Type |
| 6 | Enrollments | Immediate Down Payment | `CreateEnrollment::class` | `Enrollment::saving` Hook | `Enrollment` | **YES (if paid_amount > 0)** | Receiving Vault (`vault_id`) | Vault Type |
| 7 | Enrollments | Unpaid Registration | `CreateEnrollment::class` | `Enrollment::saving` Hook | `Enrollment` | **NO (obligation only)** | None (`vault_id = null`) | None |
| 8 | Subscriptions | Simplified Cancellation with Refund | `cancelSimplified` Action | `EnrollmentCancellationService::class` | `Expense` + `Payment(refunded)` | **YES (if refund > 0)** | Disbursing Vault (`vault_id`) | Vault Type |
| 9 | Subscriptions | Full Settlement with Refund | `cancelSubscription` Action | `EnrollmentCancellationService::class` | `Expense` + `Payment(refunded)` | **YES (if refund > 0)** | Disbursing Vault (`vault_id`) | Vault Type |
| 10 | Subscriptions | Authorized Refund Disbursement | `executeRefund` Action | `EnrollmentCancellationService::class` | `Expense` + `Payment(refunded)` | **YES** | Disbursing Vault (`vault_id`) | Vault Type |
| 11 | Vaults | Transfer Between Vaults | `CreateVaultTransfer::class` | `VaultTransfer::saving` Hook | `VaultTransfer` | **YES (internal cash move)** | From Vault & To Vault | Internal |
| 12 | Installments | Generate Schedule | `InstallmentCalculationService::class` | Database Eloquent | `Installment` | **NO (obligation only)** | None | None |

## 3. Financial Atomicity & Isolation
Every cash movement is wrapped in a database transaction (`DB::transaction`) with pessimistic row locking (`lockForUpdate`). Zero cash movements occur without a valid vault ID, preventing orphaned receipts or disbursements.