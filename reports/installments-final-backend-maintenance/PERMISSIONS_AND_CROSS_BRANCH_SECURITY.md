# PERMISSIONS AND CROSS-BRANCH SECURITY

## 1. Security Architecture & Separation of Concerns
In accordance with sound internal financial controls, the ability to **view** installment schedules must be decoupled from the ability to **collect payments** and mutate cash ledgers.

The dedicated helper `InstallmentDueReportAccess` enforces this separation across all three report pages and their payment actions.

---

## 2. Role & Permission Matrix

| Role / Persona | `canViewReport()` | `canCollectPayment()` | `canAccessVaults()` | Scope Level |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | **ALLOWED** | **ALLOWED** | **ALLOWED** | Global (All Branches/Tenants) |
| **Branch Manager** | **ALLOWED** | **ALLOWED** | **ALLOWED** | Branch-Scoped (`tenant_id`) |
| **Accountant** | **ALLOWED** | **ALLOWED** | **ALLOWED** | Branch-Scoped (`tenant_id`) |
| **Secretary (with collection)** | **ALLOWED** | **ALLOWED** | Scoped Cash Vaults | Branch-Scoped (`tenant_id`) |
| **Secretary (view-only)** | **ALLOWED** | **DENIED (403)** | **DENIED** | Branch-Scoped (`tenant_id`) |
| **Academic Follow-up** | **ALLOWED** | **DENIED (403)** | **DENIED** | Branch-Scoped (`tenant_id`) |
| **Inactive Staff Member** | **DENIED (403)** | **DENIED (403)** | **DENIED** | Blocked |
| **Cross-Branch Employee** | Scoped to own branch | Blocked from foreign branch | Blocked | Strict Tenant Isolation |
| **Unauthenticated / Guest** | **DENIED (Redirect)**| **DENIED (403)** | **DENIED** | None |

---

## 3. Defense Against Direct Livewire Request Tampering

### Threat Vector:
An employee with view-only permissions might inspect the network tab, extract the Livewire component snapshot, and synthesize a POST request executing:
```json
{
  "fingerprint": { "name": "app.filament.admin.pages.installments-due-today" },
  "updates": [
    { "type": "callMethod", "method": "openPaymentModal", "params": [101] },
    { "type": "callMethod", "method": "savePayment", "params": [] }
  ]
}
```

### Backend Enforcement:
`HandlesInstallmentDuePayments` enforces explicit backend guards at both entry points:
```php
public function openPaymentModal(int $installmentId): void
{
    abort_unless(InstallmentDueReportAccess::canCollectPayment(auth()->user()), 403, 'غير مصرح لك بتحصيل الأقساط.');
    ...
}

public function savePayment(): void
{
    abort_unless(InstallmentDueReportAccess::canCollectPayment(auth()->user()), 403, 'غير مصرح لك بتحصيل الأقساط.');
    ...
}
```
**Outcome:** Even if an unauthorized user tampers with client-side JavaScript or submits crafted Livewire payloads, the backend immediately halts execution with an HTTP 403 Forbidden exception before any database query or transaction lock is evaluated.

---

## 4. Multi-Tenant & Cross-Branch Isolation

### Vault Isolation:
`InstallmentDueReportService::getActiveVaults(?int $tenantId)` filters by `$tenantId`. A cashier in Branch 2 cannot view, select, or deposit funds into Branch 1's cash box.

### Installment Record Scoping:
Before a payment is processed:
```php
$installment = Installment::lockForUpdate()->findOrFail($this->paymentInstallmentId);

if ($tenantId !== null && (int)$installment->tenant_id !== (int)$tenantId) {
    throw new \Exception('لا يمكن تحصيل قسط تابع لفرع آخر.');
}
```
**Outcome:** Cross-tenant payment attempts trigger an immediate exception and rollback. Verified in automated test `test_j_cross_tenant_installment_access_denied`.
