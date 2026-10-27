# 03 — Test Environment & Synthetic Data Setup

## 1. Safety & Isolation Protocol
To prevent any disruption to live or production operations:
- **Dedicated Staging Environment:** Real operational execution against local SQLite database (`database/database.sqlite`).
- **Cold Backup Snapshot:** Complete database copy created at `database/database.sqlite.pre_full_ops_backup`.
- **Third-Party Gateways Safeguarded:**
  - `MAIL_MAILER=log` (No real emails sent to customers).
  - WhatsApp & SMS notification drivers diverted to local debug logs.
  - No live financial payment gateway requests executed.

## 2. Test Execution Identifier
All synthetic records created in this test cycle are explicitly prefixed and tagged:
- **Run Identifier:** `[E2E-LIVE-74b6b]`
- **Student Code Pattern:** `STU-LIVE-XXXX`
- **Enrollment Code Pattern:** `ENR-LIVE-XXXX`
- **Invoice / Receipt Code Pattern:** `INV-LIVE-XXXX` / `REC-LIVE-XXXX`

## 3. Organizational Entities Configured
| Entity | Synthetic Name | Database ID | Role/Type |
|---|---|---|---|
| **Branch 1 (Main)** | الفرع الرئيسي - القاهرة | `1` | Primary Operational Branch |
| **Branch 2 (Secondary)** | فرع الجيزة - الدقي | `2` | Secondary Branch (Isolation Testing) |
| **Registration Staff** | موظف تسجيل 1 - الفرع الرئيسي | `user_id: 11` | Registration Role |
| **Accountant Staff** | محاسب فرع القاهرة (E2E) | `user_id: 84` | Accountant Role |
| **Instructor** | أحمد محمد - مدرب | `user_id: 14` | Instructor Role |
| **Educational Supervisor** | خالد أحمد - مشرف | `user_id: 15` | Supervisor Role |
| **Vault** | كاش الرئيسي | `vault_id: 1` | Primary Cash Treasury |

## 4. Test Population Profiles
A comprehensive cohort of synthetic students was created representing all school scenarios:
1. `Student A`: Standard active student with full lifecycle (attendance, homework, exam, passing score).
2. `Student B`: Student with high academic achievement (100% attendance, A+ grades).
3. `Student C`: Student with academic struggle (<50% exam score) triggering alerts and follow-up.
4. `Student D`: Student with attendance absence and cancellation/settlement history.
