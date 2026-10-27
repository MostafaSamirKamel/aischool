# AI SCHOOL — SINGLE-BRANCH PILOT DEPLOYMENT PLAN
**Document ID:** `19_SINGLE_BRANCH_PILOT_PLAN.md`  
**Execution Date:** 2026-10-25  
**Designated Pilot Branch:** الفرع الرئيسي - القاهرة (Cairo Main Branch — `tenant_id = 1`)  
**Launch Authority:** Requires Explicit Written Approval from System Owner  

---

## 1. Executive Summary

This plan outlines the controlled rollout of AI School to a single physical location: **الفرع الرئيسي - القاهرة**. Confining initial live operations to one branch protects organizational stability, enables close financial auditing, and allows fine-tuning before multi-branch expansion.

> [!CAUTION]
> **GOVERNANCE NOTICE:**  
> This document prepares the operational setup for the pilot branch. **The live pilot will NOT be activated without explicit owner authorization.**

---

## 2. Pilot Branch Profile & Configuration

| Parameter | Configuration Setting | Verification Notes |
| :--- | :--- | :--- |
| **Branch Name** | الفرع الرئيسي - القاهرة (Cairo Main Branch) | Tenant ID: 1 |
| **Physical Location** | Cairo Central Headquarters | Classrooms: 101, 102, Lab A, Lab B |
| **Operating Courses** | Python Programming, Web Dev, Scratch, AI Basics | Catalogs configured and verified |
| **Active Financial Vaults** | 1. كاش (Cash)<br>2. فودافون كاش (Vodafone Cash)<br>3. إنستاباي (Instapay) | All 3 vaults mapped to Tenant 1 |

---

## 3. Designated Pilot Personnel & Accounts

| Role | Designated Staff Member | Official Login Email | Password Protocol |
| :--- | :--- | :--- | :--- |
| **Branch Director** | مدير فرع القاهرة | `manager.cairo@aischool.test` | Forced change on first login |
| **Head of Accounts** | محاسب فرع القاهرة | `accountant.cairo@aischool.test` | Forced change on first login |
| **Registrar / Reception** | سكرتير فرع القاهرة | `secretary.main@admin.com` | Forced change on first login |
| **Lead Instructor** | أحمد محمد - مدرب رئيسي | `instructor.main@admin.com` | Forced change on first login |
| **Educational Supervisor**| خالد أحمد - مشرف أكاديمي | `supervisor.main@admin.com` | Forced change on first login |

---

## 4. Phase-by-Phase Pilot Rollout Schedule

```
┌────────────────────────────────────────────────────────┐
│ Phase 1: Operational Handover & Account Verification   │
│  - Staff credentials activated with forced password reset
│  - Vault opening balances counted and countersigned    │
└───────────────────────────┬────────────────────────────┘
                            │
                            ▼
┌────────────────────────────────────────────────────────┐
│ Phase 2: Live Registration & Group Enrollment (Days 1-7)
│  - Real student intake via Secretary portal            │
│  - Down payments collected via /admin/payments/create  │
└───────────────────────────┬────────────────────────────┘
                            │
                            ▼
┌────────────────────────────────────────────────────────┐
│ Phase 3: Daily Teaching, Attendance & Homework (Days 8-14)
│  - Instructors submit live session attendance          │
│  - Students upload homework via portal                 │
└───────────────────────────┬────────────────────────────┘
                            │
                            ▼
┌────────────────────────────────────────────────────────┐
│ Phase 4: First Midterm Exams & End-of-Month Payroll    │
│  - Online exams graded and published                   │
│  - Monthly teacher salary disbursement reconciled      │
└────────────────────────────────────────────────────────┘
```

---

## 5. Escalation & Technical Support Matrix

| Issue Severity | Category | Immediate Contact | SLA Resolution Target |
| :---: | :--- | :--- | :---: |
| **P0** | Vault discrepancy or payment failure | Lead Financial Auditor | < 15 Minutes |
| **P1** | Attendance or login outage | Senior Laravel Architect | < 30 Minutes |
| **P2** | Report formatting or minor UI glitch | Support Engineer | < 4 Hours |

---

## 6. Verification Verdict

**STATUS: SINGLE-BRANCH PILOT ARCHITECTURE READY FOR AUTHORIZATION**
