# Report 02: Branch Configuration and Authoritative Account Verification

## 1. Branch Configuration
The AI School system operates on a multi-tenant architecture where each physical or virtual school location is represented by a Tenant record:

| Branch / Tenant ID | Branch Name (Arabic) | Code / Domain | Status | Operational Focus |
|---|---|---|---|---|
| **1** | أبو المطامير | `matamir` | Active | **Primary Simulation Branch** |
| **2** | الأونلاين | `online` | Active | **Isolation Target Branch** |

---

## 2. Authorized Staging Accounts
Authentication and role-based access control were verified for the four mandated test accounts:

| Role | Username (Phone) | Display Name | Assigned Branch | User ID | Auth Verification |
|---|---|---|---|---|---|
| **Abu El Matamir Manager** | `01070801308` | مدير فرع أبو المطامير | أبو المطامير (Tenant 1) | 335 | **PASS** (Filament Admin Panel) |
| **Online Branch Manager** | `01501101213` | مدير فرع الأونلاين | الأونلاين (Tenant 2) | 336 | **PASS** (Filament Admin Panel) |
| **Lead Instructor** | `01091812925` | أحمد حسن - مدرب أبو المطامير | أبو المطامير (Tenant 1) | 337 | **PASS** (Instructor Portal) |
| **Primary Test Student** | `01007287273` | زياد طارق - طالب أبو المطامير | أبو المطامير (Tenant 1) | 338 | **PASS** (Student Portal) |

> [!IMPORTANT]
> The instructor user record (User ID `337`) is mapped to an authoritative `instructors` table profile with matching ID `337` and Tenant ID `1`.

---

## 3. Test Result Format
- **Test ID:** BRN-ACC-01
- **Module:** Multi-Tenancy & RBAC
- **Role:** System Administrator
- **Route:** `/admin/login`, `/instructor/login`, `/student/login`
- **Initial State:** Staging database with tenant definitions.
- **Actual Action Executed:** Validated credentials, tenant foreign keys, and role policies.
- **Expected Result:** Accounts authenticate strictly into their designated tenant context with no credential leaks or duplicate identities.
- **Actual Result:** Authenticated sessions successfully established for all 4 roles.
- **Status:** **PASS**