# 16 — Supervision Report Versioning, Revisions & Clarifications

## 1. Architectural Need
Supervision reports represent legal, operational, and institutional records submitted to executive management. If management requests revisions or if historical reports were modified in-place, accountability would be destroyed. The system therefore implements an immutable snapshot versioning mechanism in `supervision_report_versions`.

## 2. Database Schema
```sql
CREATE TABLE supervision_report_versions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supervision_report_id BIGINT UNSIGNED NOT NULL,
    version_number INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    executive_summary TEXT NULL,
    achievements TEXT NULL,
    challenges TEXT NULL,
    recommendations TEXT NULL,
    metrics_snapshot JSON NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    change_reason VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (supervision_report_id) REFERENCES supervision_reports(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);
```

## 3. Management Review Actions in Admin Panel
When viewing a published report in `/admin/admin-supervision-reports`, Management has two specialized actions:

### 3.1 Request Clarification (`طلب استيضاح`)
- Used when management needs additional explanation without altering the report's published status.
- Management enters inquiry notes.
- A notification and status note are added to the report (`management_notes`), and the supervisor responds in an audit log without modifying core report data.

### 3.2 Request Revision (`طلب تعديل رسمي`)
- Used when metrics are inaccurate or critical analysis is missing.
- Management provides formal revision instructions.
- **System Action:**
  1. Creates an immutable version snapshot of the current report in `supervision_report_versions` with `change_reason = 'Pre-revision snapshot requested by management'`.
  2. Updates report status to `needs_revision`.
  3. Re-opens the report for editing in the Supervisor Panel.
  4. Upon resubmission, a new version snapshot is created and status returns to `published`.
