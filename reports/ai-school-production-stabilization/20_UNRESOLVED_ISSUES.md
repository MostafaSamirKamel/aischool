# AI SCHOOL — UNRESOLVED ISSUES AND TECHNICAL DISCLOSURES
**Document ID:** `20_UNRESOLVED_ISSUES.md`  
**Execution Date:** 2026-10-25  
**Core Standard:** Absolute Transparency — Zero Fabricated Claims  

---

## 1. Executive Summary

This document transparently records technical items and database observations identified during the audit that remain outside the authorized scope of autonomous agent modification or require formal institutional approval.

---

## 2. Identified Technical Items and Disclosures

### Item 1: Legacy Payments Without Linked Vaults (29 Records)
- **Description:** 29 historical payment records from an early data migration exist in the database with `vault_id = NULL`.
- **Impact:** These records accurately offset student debts and appear in global revenue ledgers, but they do not link to any individual vault balance.
- **Why Unresolved:** In accordance with the **Non-Negotiable Safety Requirements**, the agent is strictly prohibited from inventing vaults or silently rewriting historical financial records.
- **Action Required:** School administration and external auditors must formally approve mapping these records to a dedicated "Historical Migration Vault" (`الخزنة التاريخية`).
- **Severity:** Low / Informational (Historical integrity preserved).
- **Classification:** **REQUIRES OWNER APPROVAL**.

---

### Item 2: Database Driver Differences (SQLite vs. Production MySQL / PostgreSQL)
- **Description:** The local development, staging, and automated test environment executes against SQLite 3.x, whereas high-concurrency production deployments typically run MySQL 8.x or PostgreSQL 15+.
- **Impact:**
  - Differences in strict SQL mode (`ONLY_FULL_GROUP_BY`).
  - Differences in locking mechanics (`lockForUpdate` is transactional in InnoDB/Postgres; SQLite locks at the database file level).
- **Mitigation & Recommendation:**
  - Run the full test suite (`php artisan test`) against a production-clone MySQL/PostgreSQL staging container prior to live deployment cutover.
- **Severity:** Medium.
- **Classification:** **OPERATIONAL PREREQUISITE**.

---

### Item 3: PHPUnit 12 Metadata Deprecation Warnings
- **Description:** Older test methods in certain unit test suites utilize doc-comment metadata (e.g. `@test`) rather than PHP 8 Attributes (`#[Test]`).
- **Impact:** PHPUnit 11 outputs informational deprecation warnings (`WARN Metadata found in doc-comment`). These warnings do not affect test execution under PHPUnit 11, but will be removed in PHPUnit 12.
- **Action Required:** Upgrade test doc-comments to PHP 8 attributes during the next scheduled minor version test cleanup.
- **Severity:** Trivial / Non-blocking.
- **Classification:** **DEFERRED TO NEXT MAINTENANCE WINDOW**.

---

## 3. Summary Risk Table

| Risk Item | Impact Level | Production Blocker? | Remediation Requirement |
| :--- | :--- | :---: | :--- |
| **Legacy Unlinked Payments (29)** | Low | **NO** | Owner approval for historical reconciliation |
| **Database Engine Differences** | Medium | **NO** | Staging smoke-test on MySQL before cutover |
| **Doc-comment Deprecations** | Trivial | **NO** | Update to PHP 8 attributes before PHPUnit 12 |

---

## 4. Audit Verdict

There are **zero critical blockers** preventing deployment to production. All identified risks are manageable and fully disclosed.
