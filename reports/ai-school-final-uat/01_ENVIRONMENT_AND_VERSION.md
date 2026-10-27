# 01 — ENVIRONMENT AND VERSION SPECIFICATION

**Project:** AI School — School Management System  
**Audit Stage:** Master Production Acceptance Testing (UAT)  
**Evaluation Date:** October 21, 2026  
**Lead Auditor:** Senior QA Automation & Laravel Backend Architect (Google Antigravity)  

---

## 1. System Runtime & Architecture

| Component | Specification / Version | Verification Status | Notes |
|:---|:---|:---|:---|
| **Operating System** | Windows 10/11 x64 Enterprise | Verified | Local Host Platform |
| **PHP Runtime** | PHP 8.2.12 (cli) | Verified | Memory Limit: 512M |
| **Framework** | Laravel 11.45.1 | Verified | Modern Vite & Livewire stack |
| **Admin Panel** | Filament v3.2.147 | Verified | Multi-panel tenant architecture |
| **Frontend Engine** | Livewire v3.6.4 / Alpine.js v3 | Verified | Zero frontend redesign enforced |
| **Database Engine** | SQLite (Isolated Test DB: `database/database.sqlite`) | Verified | Direct PRAGMA foreign keys active |
| **Web Server** | `php artisan serve` (127.0.0.1:8000) | Active | Background Task Process |
| **Application Fingerprint** | `ai_school_new-main-rev-20261021` | Verified | Source snapshot consolidated |

---

## 2. Active PHP Extensions

The following critical extensions were verified as active and operational:
- `pdo_sqlite`, `pdo_mysql`
- `curl`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`, `zip`

---

## 3. Database & Staging Environment Safety

1. **Isolation from Production Data:**
   - Staging database is completely isolated in `database/database.sqlite`.
   - Outbound notifications (WhatsApp, SMS, Email) are intercepted with synthetic loggers (`SmsLog`, `WhatsAppLog`).
   - No live external payment gateways are invoked; synthetic collections and payouts are channeled via virtual vaults (`vaults` table).

2. **Pre-flight Database Snapshot:**
   - Snapshot File: `database/backups/backup_uat_preflight.sqlite`
   - File Size: `2,666,496 bytes` (2.54 MB)
   - MD5 Fingerprint: Generated and verified pre-test execution.

---

## 4. Multi-Tenant Branch Configuration

| Branch / Tenant | ID | Code | Currency | Default Vault | Status |
|:---|:---|:---|:---|:---|:---|
| **Branch Cairo (Main)** | 1 | `CAI-01` | EGP | Vault Main (ID: 1) | Active |
| **Branch Alexandria** | 2 | `ALX-02` | EGP | Vault Alex (ID: 2) | Active |

All data partitions enforce strict multi-tenant boundaries (`tenant_id` scopes) ensuring that branch operations are completely isolated at the Eloquent model layer.
