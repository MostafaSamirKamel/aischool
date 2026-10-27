# AI SCHOOL — UNRESOLVED ISSUES AND TECHNICAL DISCLOSURES
**Document ID:** `20_UNRESOLVED_ISSUES.md`  
**Execution Date:** 2026-10-25  
**Audit Standard:** Complete Honesty & Evidence-Based Transparency  

---

## 1. Executive Summary

This report transparently documents technical items identified during the operational simulation that remain outside the authorized scope of autonomous agent modification or require formal administrative approval.

---

## 2. Identified Unresolved Items and Disclosures

### Item 1: Legacy Payments Without Linked Vaults (29 Historical Records)
- **Description:** 29 legacy payment records dating back to early system data imports remain in the database with `vault_id = NULL`.
- **Impact:** They accurately offset student balances and appear in global revenue ledgers, but they do not link to any individual vault statement.
- **Why Unresolved:** In accordance with the non-negotiable safety rules, the agent is strictly prohibited from inventing vaults or silently rewriting historical financial records.
- **Action Required:** School administration and external auditors must formally approve mapping these records to a dedicated "Historical Migration Vault" (`الخزنة التاريخية`).
- **Classification:** **REQUIRES OWNER APPROVAL**.

---

### Item 2: Production Database Engine Differences (SQLite vs. MySQL / PostgreSQL)
- **Description:** Staging testing runs on SQLite 3.x, whereas high-concurrency production deployments typically run MySQL 8.x or PostgreSQL 15+.
- **Impact:** Minor SQL differences in locking mechanisms and strict grouping modes.
- **Recommendation:** Perform a sanity smoke test on a production-clone MySQL database before live cutover.
- **Classification:** **OPERATIONAL PREREQUISITE**.

---

### Item 3: PHPUnit 12 Doc-Comment Deprecations
- **Description:** Older test methods in unit test suites use `@test` doc-comments rather than PHP 8 `#[Test]` attributes.
- **Impact:** Generates informational deprecation warnings in PHPUnit 11.
- **Action Required:** Upgrade test annotations during the next scheduled minor maintenance window.
- **Classification:** **NON-BLOCKING / DEFERRED**.

---

## 3. Risk Assessment Summary

| Item | Impact Level | Production Blocker? | Remediation Requirement |
| :--- | :--- | :---: | :--- |
| **Legacy Unlinked Payments (29)** | Low | **NO** | Owner approval for historical reconciliation |
| **Database Engine Differences** | Medium | **NO** | Staging smoke-test on MySQL before cutover |
| **Doc-comment Deprecations** | Trivial | **NO** | Update to PHP 8 attributes before PHPUnit 12 |

---

## 4. Verification Verdict

There are **zero critical security, academic, or financial blockers** preventing production deployment.
