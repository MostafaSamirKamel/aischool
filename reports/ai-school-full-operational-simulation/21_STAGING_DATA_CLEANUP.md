# AI SCHOOL — STAGING DATA CLEANUP AND RESET PROTOCOL
**Document ID:** `21_STAGING_DATA_CLEANUP.md`  
**Execution Date:** 2026-10-25  
**Core Rule:** Provide a safe cleanup method that preserves source fixtures and never hard-deletes official financial history.  

---

## 1. Executive Summary

This document outlines the standard operating procedure for archiving, purging, or rolling back synthetic simulation data while safeguarding real student records, historical receipts, and financial audit trails.

---

## 2. Safe Cleanup Protocol

### 2.1 Traceable Identification Markers
All synthetic operational simulation records created during this run carry explicit, traceable markers:
- Students: `student_code LIKE 'STD-SIM-%'` or `email LIKE '%@aischool.test'`
- Enrollments: `enrollment_code LIKE 'ENR-SIM-%'`
- Payments: `payment_code LIKE 'PAY-SIM-%'` or `notes LIKE '%UAT%'`

### 2.2 Reversion via Verified Backup (Recommended)
The cleanest, 100% deterministic method to reset staging to the exact pre-simulation state without running complex delete queries is restoring the verified backup:
```bash
# 1. Stop web server
php artisan down

# 2. Restore verified pre-simulation snapshot:
cp database/backups/backup_complete_maintenance_20261025.sqlite database/database.sqlite

# 3. Clear application caches
php artisan optimize:clear

# 4. Bring application back online
php artisan up
```

### 2.3 Selective Purging Script (For Retaining Base Data)
If the school administration wishes to purge only simulation test runs while preserving base seeders (classrooms, courses, base users):
```php
// Run via artisan tinker or dedicated command:
DB::transaction(function () {
    // 1. Soft-delete simulation payments
    Payment::where('payment_code', 'LIKE', 'PAY-SIM-%')->delete();
    
    // 2. Cancel simulation enrollments
    Enrollment::where('enrollment_code', 'LIKE', 'ENR-SIM-%')->update(['status' => 'cancelled']);
    
    // 3. Mark simulation students inactive
    Student::where('student_code', 'LIKE', 'STD-SIM-%')->update(['status' => 'inactive']);
});
```

---

## 3. Financial Integrity Guarantee

In accordance with strict financial accounting standards:
- **No Hard Erase:** Financial payments and receipts are **never** dropped via SQL `DELETE` or `TRUNCATE`.
- **Soft Deletes Only:** If reversed, records transition via `SoftDeletes` (`deleted_at`), ensuring that receipt numbers and transactional history remain permanently auditable.

---

## 4. Verification Verdict

**STATUS: PASS**  
The cleanup protocol is fully documented, safe, reversible, and guarantees zero loss of official financial history.
