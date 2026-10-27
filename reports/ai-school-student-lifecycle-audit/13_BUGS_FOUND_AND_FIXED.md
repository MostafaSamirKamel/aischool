# 13 — BUGS IDENTIFIED & REPAIRED DURING AUDIT

## 1. Defect 1: Duplicate Expense Creation in `cancelSimplified`
- **Symptom**: When simplified cancellation executed a cash refund, vault balance decreased by double the refund amount.
- **Root Cause**: `cancelSimplified` had two separate `Expense::create` calls (lines 382 and 517).
- **Repair**: Removed redundant second `Expense::create` block, retaining audit logging and single atomic expense posting.
- **Verification**: Retested via Scenario J1 -> Vault decreased by exactly 800.00 EGP.

## 2. Defect 2: Group Active Students Query Ambiguity
- **Symptom**: `Group::activeStudents()` only checked `enrollment_courses.group_id`, excluding enrollments where `group_id` was set directly on `enrollments` table.
- **Root Cause**: Missing fallback check on `enrollments.group_id`.
- **Repair**: Updated `Group::activeStudents()` to check `enrollments.group_id == $this->id OR enrollmentCourses.group_id == $this->id`. Added `static::saved` hook on `Enrollment` to sync group capacity automatically.
- **Verification**: Retested via Scenario A and Scenario F -> Group active count updates instantly and accurately.

## 3. Defect 3: Vault Ledger Expenses Sum Filtering
- **Symptom**: Unpaid expense liabilities were deducting cash in `Vault::getCurrentBalance()` because query checked `where('status', '!=', 'cancelled')`.
- **Root Cause**: Status `'pending'` expenses were counted as cash outflows.
- **Repair**: Updated query to `whereIn('status', ['paid', 'completed'])`.
- **Verification**: Retested via Scenario 11 in vault suite -> Pending expenses do not move cash.