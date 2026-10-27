# 01 — Environment Safety Check & Database Identification

## Overview
This document details the environment inspection, database isolation, tenancy boundaries, and safety precautions executed prior to generating persistent demonstration records for the existing student cohort in AI School.

---

## 1. Environment & Target Identification

| Property | Value | Safety Status |
|---|---|---|
| **Application Environment (`APP_ENV`)** | `local` | **SAFE (Isolated Staging/Development)** |
| **Debug Mode (`APP_DEBUG`)** | `true` | Expected in local/dev |
| **Application URL (`APP_URL`)** | `http://127.0.0.1:8000` | Local development host |
| **Database Connection (`DB_CONNECTION`)** | `sqlite` | Isolated local file-based database |
| **Database Path (`database_path()`)** | [database/database.sqlite](file:///d:/ai_school_new-main%20%281%29/ai_school_new-main/database/database.sqlite) | Dedicated local file |
| **Production Mutation Risk** | **ZERO (0%)** | Confirmed non-production workspace |

> [!IMPORTANT]
> Live production systems or external institutional databases are completely isolated from this workspace. All database operations strictly target the dedicated local SQLite database instance.

---

## 2. Multi-Tenancy & Branch Discovery

The inspection of the active schema confirmed:
- **Tenant ID**: `1` (Main AI School Academy tenant).
- **Active Branches**:
  - `Branch 1`: Main Campus (Cairo / Nasr City Branch)
  - `Branch 2`: Satellite Campus (Giza Branch)
- **Tenant Isolation Enforcement**:
  Every entity created (Enrollments, Groups, Vaults, Sessions, Attendance, Payments, Follow-Up, Supervision) has `tenant_id = 1` and appropriate `branch_id` associations to preserve strict tenancy boundaries.

---

## 3. Pre-Population Cold Backup

Before executing any database inserts or schema touches, an offline cold backup of the active SQLite database was created:

- **Backup Source**: `database/database.sqlite`
- **Backup Destination**: [database/database.sqlite.pre_demo_population_backup](file:///d:/ai_school_new-main%20%281%29/ai_school_new-main/database/database.sqlite.pre_demo_population_backup)
- **Backup Timestamp**: `2026-10-22 17:35:00 UTC`
- **Integrity Status**: Verified intact and recoverable.

---

## 4. Safety Guardrails in Data Generation

1. **Tagging & Auditability**:
   Every generated operational record is marked with the unique identifier `AI_SCHOOL_PERSISTENT_DEMO` in its description, notes, or reference field.
2. **Reversible CLI Command**:
   Implemented via [PopulateExistingStudentsDemoData.php](file:///d:/ai_school_new-main%20%281%29/ai_school_new-main/app/Console/Commands/PopulateExistingStudentsDemoData.php) supporting `--dry-run` and `--cleanup` flags.
3. **Immutability of Real Accounting Rules**:
   No hard deletes or artificial direct mutations were used on posted ledger tables. All financial transactions reflect balanced vault inflows and outflows.
