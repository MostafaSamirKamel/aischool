# 07 — Installment Schedules & Debt Aging Tests

## 1. Installment Generation Rules
Upon finalizing an installment-based enrollment, the system divides the contracted net amount into structured payment milestones with defined due dates.

## 2. Tested Schedule Setup
- **Schedule Structure:** 2 Equal Installments of 1,400.00 EGP each.
  - **Installment 1:** Due immediately / today (Amount: 1,400.00 EGP).
  - **Installment 2:** Due in 30 days / future (Amount: 1,400.00 EGP).

## 3. Debt Aging Matrix Verification
The Reporting Center categorizes all school receivables into 4 aging buckets:
1. **Overdue (متأخرة):** Due date in the past, status unpaid.
2. **Due Today (مستحقة اليوم):** Due date equals current date, status unpaid.
3. **Future (مستقبلية):** Due date > current date.
4. **Paid (مسددة):** Successfully settled.

## 4. Test Execution & Status Transitions
| Installment ID | Due Date | Amount | Payment Action | Resulting Status | Reporting Bucket |
|---|---|---|---|---|---|
| **Inst-1** | 2026-10-22 | 1,400.00 EGP | Paid 1,400.00 EGP | `paid` | Collections (+1,400 EGP) |
| **Inst-2 (Initial)** | 2026-11-21 | 1,400.00 EGP | Unpaid | `unpaid` | Future Debt (34,300 total pool) |
| **Inst-2 (Settled)** | 2026-11-21 | 1,400.00 EGP | Paid 1,400.00 EGP | `paid` | Collections (+1,400 EGP), Future Debt reduced |

## 5. Overpayment Prevention Test
Attempted to submit a third payment of 500.00 EGP against the already fully settled enrollment. The application prevented the transaction and flagged that the remaining debt is 0.00 EGP.
