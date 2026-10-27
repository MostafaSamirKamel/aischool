# 03 — Branch Authorization & Security Architecture

## 1. Multi-Tenant Branch Isolation Model
AI School enforces multi-tenancy at the database level where every branch is identified by a unique `tenant_id`. All educational supervision entities (`supervisory_observations`, `instructor_evaluations`, `supervision_reports`, etc.) include a non-nullable `tenant_id` foreign key referencing the `tenants` table.

```
       [User / Auth Token]
               │
        ┌──────▼──────┐
        │  Tenant ID  │
        └──────┬──────┘
               ├────────────────────────────────────────┐
               ▼                                        ▼
    [Branch Supervisor]                         [Limited Secretary]
    can('branch_educational_supervisor')       Explicit Assignment Only
    Scope: WHERE tenant_id = user.tenant_id     Scope: WHERE group_id IN (assignments)
```

## 2. Explicit Assignment Tables
To support flexible delegation without leaking branch-wide data to junior staff or secretaries, two assignment tables are utilized:
1. `supervision_group_assignments`:
   - `user_id`: Target staff member.
   - `group_id`: Assigned academic group.
   - `assigned_by`: Admin or supervisor who granted access.
   - `assigned_at` & `revoked_at`: Soft revocation tracking.
2. `supervision_instructor_assignments`:
   - `user_id`: Target staff member.
   - `instructor_id`: Assigned instructor.
   - `assigned_by` & `revoked_at`.

## 3. Scope Resolution Algorithm in `EducationalSupervisionService`
```php
public function getSupervisedGroupIds(User $user): array
{
    if ($user->is_super_admin && !$user->tenant_id) {
        return Group::pluck('id')->toArray();
    }

    // Branch Designated Supervisor has branch-wide visibility
    if ($this->isBranchDesignatedSupervisor($user) && $user->tenant_id) {
        return Group::where('tenant_id', $user->tenant_id)->pluck('id')->toArray();
    }

    // Limited Supervisors / Secretaries: explicit assignments + legacy supervisor profile
    $assignedGroupIds = SupervisionGroupAssignment::where('user_id', $user->id)
        ->whereNull('revoked_at')
        ->when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
        ->pluck('group_id')
        ->toArray();

    $supervisorProfile = Supervisor::where('user_id', $user->id)->first();
    $legacyGroupIds = $supervisorProfile
        ? Group::where('supervisor_id', $supervisorProfile->id)
            ->when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->pluck('id')
            ->toArray()
        : [];

    return array_values(array_unique(array_merge($assignedGroupIds, $legacyGroupIds)));
}
```

## 4. Spatie Shield Permissions Matrix (18 Granular Permissions)
The following 18 permissions govern access across Supervisor, Instructor, and Management panels:
1. `view_educational_supervision_dashboard`
2. `branch_educational_supervisor`
3. `create_supervisory_observations`
4. `view_supervisory_observations`
5. `update_supervisory_observations`
6. `approve_supervisory_observations`
7. `create_instructor_evaluations`
8. `view_instructor_evaluations`
9. `publish_instructor_evaluations`
10. `respond_instructor_evaluation_appeals`
11. `decide_instructor_evaluation_appeals` (Management Only)
12. `generate_supervision_reports`
13. `view_supervision_reports`
14. `publish_supervision_reports`
15. `request_supervision_report_clarification` (Management Only)
16. `request_supervision_report_revision` (Management Only)
17. `view_instructor_supervision_profiles`
18. `view_group_supervision_profiles`
