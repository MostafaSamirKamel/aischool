# 06 — Supervisory Observation Workflow & Lifecycle Engine

## 1. Overview & Purpose
Supervisory observations form the foundational feedback loop between Educational Supervisors and Instructors. They establish clear accountability, documented evidence, structured remediation paths, and verification prior to closure.

## 2. State Machine & Transitions

```
               ┌───────────────────────┐
               │         NEW           │◄────────┐
               │       (جديدة)         │         │
               └──────────┬────────────┘         │
                          │                      │
                   Instructor Submits            │ Supervisor Rejects
                   Remediation Evidence          │ Correction
                          │                      │ (Return for Correction)
                          ▼                      │
               ┌───────────────────────┐         │
               │ WAITING_SUPERVISOR_   │─────────┘
               │        REVIEW         │
               │(بانتظار مراجعة المشرف)│
               └──────────┬────────────┘
                          │
                  Supervisor Validates
                  & Approves
                          │
                          ▼
               ┌───────────────────────┐
               │       COMPLETED       │
               │       (مكتملة)        │
               └───────────────────────┘
```

## 3. Strict Permission & Role Enforcement
1. **Creation:** Authorized supervisors create observations via `SupervisoryObservationResource`. Fields include:
   - `observation_code`: Automatically formatted (e.g., `SOB-2026-0001`).
   - `instructor_id`: Target instructor.
   - `group_id` / `session_id`: Contextual link.
   - `category`: `attendance_recording`, `homework_followup`, `class_interaction`, `exam_performance`, `syllabus_adherence`, `administrative`.
   - `severity`: `low`, `medium`, `high`, `critical`.
   - `title`, `description`, `required_action`.
   - `expected_resolution_date`.
2. **Instructor Response:** The assigned instructor accesses the observation via their panel (`/instructor/instructor-supervisory-observations`).
   - The instructor **CANNOT** close or approve the observation.
   - The instructor provides `response_notes`, attaches evidence or links, and clicks **"رفع للإشراف للاعتماد"**. This transitions status to `waiting_supervisor_review`.
3. **Supervisor Review & Final Determination:**
   - The supervisor inspects the instructor's submission.
   - **Approval:** Supervisor clicks **"اعتماد اكتمال الملاحظة"** (Approve Completion). The status changes to `completed` and `completed_at` is stamped.
   - **Rejection/Return:** If the correction is unsatisfactory, supervisor clicks **"إعادة للتصحيح"** (Return for Correction) with feedback. Status reverts to `new` with updated audit history.

## 4. Code Implementation (`EducationalSupervisionService.php`)
```php
public function updateObservationStatus(
    SupervisoryObservation $observation,
    string $newStatus,
    User $actor,
    ?string $notes = null
): SupervisoryObservation {
    $oldStatus = $observation->status;

    // Instructor cannot transition directly to completed
    if ($actor->hasRole('instructor') && $newStatus === SupervisoryObservation::STATUS_COMPLETED) {
        throw new \DomainException('لا يمكن للمهندس اعتماد إغلاق الملاحظة.');
    }

    $observation->status = $newStatus;
    if ($newStatus === SupervisoryObservation::STATUS_COMPLETED) {
        $observation->completed_at = now();
        $observation->approved_by = $actor->id;
    }
    $observation->save();

    // Log complete audit history
    SupervisoryObservationHistory::create([
        'supervisory_observation_id' => $observation->id,
        'user_id' => $actor->id,
        'action' => 'status_change',
        'from_status' => $oldStatus,
        'to_status' => $newStatus,
        'notes' => $notes,
    ]);

    return $observation;
}
```
