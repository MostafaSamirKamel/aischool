# 07 — Observation Audit History & Timeline Tracking

## 1. Requirement & Rationale
Supervisory observations frequently involve sensitive professional evaluations and performance dialogues between supervisors and instructors. To prevent disputes, accusations of arbitrary rejection, or untracked changes, the system enforces an append-only audit trail in the `supervisory_observation_histories` table.

## 2. Database Schema
```sql
CREATE TABLE supervisory_observation_histories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supervisory_observation_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL,
    from_status VARCHAR(50) NULL,
    to_status VARCHAR(50) NULL,
    notes TEXT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (supervisory_observation_id) REFERENCES supervisory_observations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## 3. Captured Actions
- `created`: Initial registration of the observation by the supervisor.
- `instructor_response`: Submission of corrective action notes or materials by the instructor.
- `returned_for_correction`: Rejection of remediation with explicit supervisor feedback notes.
- `approved_completed`: Final supervisor validation and closure.
- `reopened`: Re-opening of a completed case if recurring violations occur.

## 4. UI Infolist Timeline Integration
Within the `SupervisoryObservationResource` view page (`ViewSupervisoryObservation`), an embedded Infolist section renders the audit trail as an interactive timeline.
- **Actor Name & Role:** Displays whether the step was taken by the supervisor or instructor.
- **Timestamp:** Formatted in Arabic locale (`Y-m-d H:i`).
- **Status Change Pills:** Visual badge showing transition (e.g., `بانتظار مراجعة المشرف` ➔ `مكتملة`).
- **Notes & Instructions:** Full text notes explaining why a step was accepted or returned.
