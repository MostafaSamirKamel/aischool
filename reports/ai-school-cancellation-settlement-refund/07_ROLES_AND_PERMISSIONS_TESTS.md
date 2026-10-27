# 07 — ROLES AND PERMISSIONS TESTS
## Role-Based Access Control, Guard Verification & Tenant Isolation Matrix

**Project:** AI School Management System  
**Test Suites:** `Tests\Feature\ExecuteRefundActionLivewireTest`, `Tests\Feature\SubscriptionCancellationAndSettlementTest`, `Tests\Feature\RoleGatePolicyTest`, `Tests\Feature\MultiTenantSecurityTest`  
**Execution Result:** PASS  

---

### 1. The Three Independent Operational Permissions

The AI School authorization architecture enforces three distinct Spatie/Gate permissions:

1. **Cancellation Permission:** `cancel_subscription` / `Cancel:Enrollment`
2. **Settlement Permission:** `settle_subscription` / `Settle:Enrollment`
3. **Refund Disbursement Permission:** `execute_refunds` / `refund_subscription` / `Refund:Payment`

These permissions are completely orthogonal:
$$\text{Cancellation} \neq \text{Settlement} \neq \text{Refund Execution}$$

---

### 2. Comprehensive Role Test Matrix

| Role / Profile | Cancellation | Settlement | Refund Disbursement | Multi-Tenant Boundary | Tested Livewire / Policy Behavior | Audit Result |
|---|:---:|:---:|:---:|:---:|---|:---:|
| **Super Admin** | YES | YES | YES | Global Access | Bypasses gates via `Gate::before`; full capability across all tenants. | **PASS** |
| **Branch Manager** | YES | YES | YES | Own Tenant Only | Cannot access foreign tenant enrollments; holds permissions within branch scope. | **PASS** |
| **Secretary (With Cancellation)** | YES | NO | NO | Own Tenant Only | Can cancel enrollment; cannot waive custom debt; cannot disburse refund; refund button hidden. | **PASS** |
| **Secretary (Without Cancellation)** | NO | NO | NO | Own Tenant Only | Cancellation action hidden; direct Livewire call returns 403 authorization error. | **PASS** |
| **Accountant (With Refund)** | NO | NO | YES | Own Tenant Only | Can execute approved refund from active branch vault; cannot cancel active subscriptions. | **PASS** |
| **Accountant (Without Refund)** | NO | NO | NO | Own Tenant Only | Cannot disburse refund; `ExecuteRefundAction` button hidden; direct dispatch blocked. | **PASS** |
| **Student** | NO | NO | NO | Own Student Portal | Forbidden from administrative panels; 403 redirect on Filament admin routes. | **PASS** |
| **Parent** | NO | NO | NO | Own Parent Portal | Forbidden from administrative actions; restricted strictly to viewing child overview. | **PASS** |
| **Foreign Branch User** | NO | NO | NO | Blocked | Cross-tenant policy check returns `false`; direct URL access aborts with 404/403. | **PASS** |

---

### 3. Detailed Authorization Scenarios Verified

#### Scenario 1: Secretary Cancels Without Manager Escalation
- **Test:** User with role `secretary` and permission `cancel_subscription` opens enrollment.
- **Result:** `cancel_subscription` action is visible and fully operational. Clicking confirm immediately transitions the enrollment to `cancelled` and records the secretary's user ID as `cancelled_by`. No manager approval is requested.
- **Verification:** **PASS**

#### Scenario 2: Secretary Cannot Disburse Funds
- **Test:** Secretary with `cancel_subscription` attempts to execute a refund.
- **UI Check:** In the cancellation modal, a warning banner informs the user that refund execution is disabled for their role. In `ViewEnrollment`, the `ExecuteRefundAction` button is strictly hidden (`assertActionHidden('execute_refund')`).
- **Direct Backend Test:** Direct Livewire method invocation without permission throws `AuthorizationException` (403).
- **Verification:** **PASS**

#### Scenario 3: Accountant Disburses Approved Refund
- **Test:** Refund officer with `execute_refunds` logs in to a cancelled enrollment with 300 EGP pending refund.
- **UI Check:** `ExecuteRefundAction` is prominently visible.
- **Execution:** Officer selects vault and confirms. Refund is recorded and deducted.
- **Post-Execution:** Action immediately disappears because remaining pending refund is 0.00 EGP.
- **Verification:** **PASS**

#### Scenario 4: Cross-Tenant Isolation
- **Test:** User belonging to Tenant A (Main Campus) attempts to call `cancelSubscription` or `executeRefund` on an enrollment belonging to Tenant B (Downtown Branch).
- **Result:** Policy check `(int) $authUser->tenant_id !== (int) $enrollment->tenant_id` returns `false`. Request rejected with 403 Forbidden.
- **Verification:** **PASS**
