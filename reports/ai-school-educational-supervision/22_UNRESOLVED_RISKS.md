# 22 — Operational Risks & Mitigation Strategies

## 1. Risk Assessment Matrix

| # | Risk Description | Severity | Likelihood | Mitigation Strategy Implemented |
| :--- | :--- | :--- | :--- | :--- |
| **R1** | **Instructor Overload from Observations:** Supervisors issuing excessive observations could cause friction. | Medium | Medium | Severity categorizations (`low`, `medium`, `high`, `critical`) allow instructors to prioritize. Supervisors must review and approve closure. |
| **R2** | **Subjective Monthly Ratings:** Bias in manual 1–5 scoring. | High | Low | The 6 explicit rating criteria, mandatory written fields (strengths, improvement, recommendations), draft isolation, and formal appeal channel to management mitigate bias. |
| **R3** | **Unrecorded Attendance Mislabeled as Student Absence:** High risk of parental panic if missing attendance records were counted as student absences. | Critical | Low | **Strict Architectural Rule:** Attendance absence is only counted from submitted records. Missing attendance is tracked separately as an operational reminder for the instructor. |
| **R4** | **Financial & Salary Contamination:** Risk of accidental deduction from instructor wages based on supervisory drafts. | High | Low | **Absolute Domain Boundary:** Zero foreign keys or triggers connect `supervisory_observations` or `instructor_evaluations` to `salaries` or `vault_transactions`. |
| **R5** | **Supervision Report Tampering:** Supervisors or managers altering submitted report numbers after executive meetings. | High | Low | **Immutable Version Snapshots:** Every published report and management-requested revision creates an immutable row in `supervision_report_versions`. |
| **R6** | **External CDN Dependency in Browser Tests:** Playwright CLI driver binary download failure from Azure CDN. | Low | Medium | Direct automated HTTP testing (`$this->actingAs()->get()`) and live local server verification bypasses external CDN failures. |

## 2. Recommendations for Post-Deployment Monitoring
1. Monitor instructor appeal volume during the first 60 days of monthly evaluation roll-out.
2. Provide supervisor training on distinguishing between educational support observations vs. administrative disciplinary escalation.
3. Review branch-level report revision frequency to ensure clear management criteria for report approvals.
