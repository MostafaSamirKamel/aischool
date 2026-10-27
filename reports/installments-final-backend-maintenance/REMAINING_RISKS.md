# REMAINING RISKS AND MITIGATIONS

## 1. Risk Assessment Summary
Following comprehensive backend refactoring, integration of authoritative services, and automated test validation, the overall technical and financial risk profile is **LOW**. 

The table below outlines potential operational risks and the safeguards implemented to mitigate them.

---

## 2. Risk Registry & Mitigation Strategies

| Risk Description | Severity | Likelihood | Safeguard / Implemented Mitigation | Residual Risk |
| :--- | :--- | :--- | :--- | :--- |
| **High Concurrency / Double Clicks**<br>Multiple cashiers collecting on the same student simultaneously or rapid double-clicking of the payment button. | High | Low | **Mitigated:** Implementation of `Installment::lockForUpdate()` inside `DB::transaction()` guarantees serial execution. Balance is re-checked under lock; second attempt is rejected if debt is already satisfied. | **Negligible** |
| **Oldest-First Allocation Perception**<br>Cashier clicks "Pay" on installment #3, but funds are assigned to older unpaid installment #1. | Medium | Medium | **Mitigated:** Adheres strictly to approved school accounting policy. The UI refreshes immediately displaying real post-allocation balances, preventing cashier confusion. | **Low** (Operational training recommended) |
| **Timezone Drift on Day Boundaries**<br>Payments collected around midnight (23:59:59 vs 00:00:01) falling into adjacent calendar days. | Low | Low | **Mitigated:** All queries utilize `whereDate('due_date', Carbon::today())` based on the application's configured default timezone (`config('app.timezone')`). | **Negligible** |
| **Database Engine Differences**<br>Behavior differences between SQLite in staging and MySQL in production. | Medium | Low | **Mitigated:** Eloquent ORM abstraction used throughout. Database-specific date functions avoided in favor of standard `whereDate()`. | **Negligible** |
| **Large Data Volume on Monthly Report**<br>Campuses with thousands of active installments in a single month impacting memory. | Low | Low | **Mitigated:** Queries utilize pagination (`paginate(25)`) and database-level aggregations (`SUM()`, `COUNT()`) rather than loading full Eloquent collections into memory. | **Negligible** |

---

## 3. Operational Recommendations for Staging to Production Promotion
1. **Clear Application Caches:** Run `php artisan optimize:clear` after deployment to ensure new service classes and route caches are fresh.
2. **Staff Guidance:** Ensure front-desk cashiers understand that student installments are settled oldest-first by design.
3. **Audit Log Monitoring:** Periodically verify that generated payment receipt numbers (`TXN-...`) match daily cash deposits in physical branch vaults.
