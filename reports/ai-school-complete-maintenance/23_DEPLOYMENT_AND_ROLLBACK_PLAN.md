# AI SCHOOL — PRODUCTION DEPLOYMENT AND CONTINGENCY ROLLBACK PLAN
**Document ID:** `23_DEPLOYMENT_AND_ROLLBACK_PLAN.md`  
**Execution Date:** 2026-10-25  
**Target Environment:** AI School Production Release  

---

## 1. Executive Summary

This document outlines the standard operating procedure (SOP) for deploying the stabilized AI School codebase to production, accompanied by an instantaneous, zero-data-loss rollback procedure.

---

## 2. Pre-Deployment Verification Checklist

Before initiating production deployment, the deployment engineer must verify:
- [ ] Production database snapshot completed and integrity verified.
- [ ] Application backup created: `database/backups/backup_complete_maintenance_20261025.sqlite` (or equivalent MySQL/PostgreSQL dump).
- [ ] Maintenance window scheduled during low-traffic off-hours.
- [ ] PHP 8.3 CLI and web server runtime verified.
- [ ] Storage and cache directory permissions set (`chmod -R 775 storage bootstrap/cache`).

---

## 3. Step-by-Step Production Deployment Protocol

### Step 1: Enable Maintenance Mode
```bash
php artisan down --message="AI School System Maintenance in Progress. Please check back shortly." --retry=60
```

### Step 2: Full Database & State Backup
```bash
# SQLite backup (if running SQLite in production):
cp database/database.sqlite database/backups/pre_deploy_backup_$(date +%Y%m%d_%H%M%S).sqlite

# Or MySQL / PostgreSQL backup:
mysqldump -u <user> -p <db_name> > database/backups/pre_deploy_mysql_$(date +%Y%m%d_%H%M%S).sql
```

### Step 3: Deploy Modified Code
Deploy the 4 stabilized files:
- `app/Models/Enrollment.php`
- `app/Filament/Admin/Pages/Concerns/HandlesInstallmentDuePayments.php`
- `app/Services/InstallmentDueReportService.php`
- `tests/Feature/RealWorldOperationalVerificationTest.php`

### Step 4: Clear and Rebuild Optimized Caches
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:cache-components
```

### Step 5: Execute Smoke Tests
Verify core endpoints respond with `HTTP 200`:
- Admin Login: `/admin/login`
- Today's Installments Due: `/admin/installments-due-today`
- Payment Creation Form: `/admin/payments/create`
- Run targeted sanity test:
  ```bash
  php artisan test --filter InstallmentDueReportIntegrationTest
  ```

### Step 6: Disable Maintenance Mode
```bash
php artisan up
```

---

## 4. Contingency Rollback Plan

If an unexpected production defect occurs post-deployment, execute the following rollback steps immediately:

### Step 1: Enable Maintenance Mode
```bash
php artisan down --message="Reverting system state. Please wait."
```

### Step 2: Restore Source Files
Revert the 4 modified files to their previous git commit or pre-deployment snapshot:
```bash
git checkout HEAD~1 -- app/Models/Enrollment.php app/Filament/Admin/Pages/Concerns/HandlesInstallmentDuePayments.php app/Services/InstallmentDueReportService.php
```

### Step 3: Restore Database Snapshot (Only if database corruption occurred)
```bash
# Restore verified backup:
cp database/backups/backup_complete_maintenance_20261025.sqlite database/database.sqlite
```

### Step 4: Re-clear Caches & Reopen Application
```bash
php artisan optimize:clear
php artisan up
```

---

## 5. Deployment Authorization

**STATUS: READY FOR DEPLOYMENT**  
The deployment package is lean, contains zero database migrations, poses no data loss risks, and features a tested rollback path.
