# 01 — EXISTING WORKFLOW MAP & SYSTEM ARCHITECTURE

## 1. Architectural Overview
This report documents the integrated architecture of AI School (Laravel 12 / Filament v3 / Livewire), mapping every service, model, and lifecycle event connecting registration, parent relations, academic enrollment, classroom grouping, down payments, installments, attendance, exams, cancellation, financial settlement, refund disbursement, and reporting.

```mermaid
graph TD
    A[Student Registration] --> B[Parent Association]
    B --> C[Enrollment in Course & Group]
    C --> D{Payment Plan}
    D -->|Cash Upfront| E[Down Payment via Vault]
    D -->|Installments| F[Down Payment + Installment Schedule]
    E --> G[Vault Cash Movement]
    F --> G
    F --> H[Student Payments -> Add Payment]
    H --> G
    C --> I[Classroom Group Membership]
    I --> J[Attendance Sessions & Records]
    I --> K[Exam Grades & Academic History]
    C --> L[Cancellation & Settlement]
    L --> M{Settlement Decision}
    M -->|Waive Debt| N[Installments Cancelled]
    M -->|Keep Debt| O[Installments Retained]
    M -->|Refund Due| P[Refund Liability -> Execute Disbursement]
    P --> Q[Vault Cash Outflow]
    L --> R[Remove Active Group Membership]
    L --> S[Preserve Historical Attendance & Grades]
```

## 2. Integrated Modules Matrix

| Module | Core Models | Primary Service | State Transitions | External Dependencies |
|---|---|---|---|---|
| Registration | `User`, `Student`, `StudentParent` | Filament Form Actions | Created $\rightarrow$ Active | Branch / Tenant Scope |
| Enrollment | `Enrollment`, `EnrollmentCourse` | `Enrollment::saving`, `CreateEnrollment` | Active $\rightarrow$ Cancelled / Completed | `Course`, `Group` |
| Down Payment | `Payment`, `Enrollment`, `Vault` | `PaymentObserver::class` | Inflow posted to Vault | `Vault::assertSufficientBalance` |
| Installments | `Installment` | `InstallmentCalculationService`, `PaymentObserver` | Pending $\rightarrow$ Partial $\rightarrow$ Paid / Cancelled | Seniority Allocation Engine |
| Attendance | `AttendanceSession`, `AttendanceRecord` | `AttendanceSession::created`, `AttendanceRecord` | Present, Absent, Late, Excused | Instructor Evaluation |
| Cancellation | `EnrollmentCancellation`, `Enrollment` | `EnrollmentCancellationService::class` | Active $\rightarrow$ Cancelled (Locked) | Debt Waiver / Retention |
| Refund | `Expense`, `Payment(refunded)`, `Vault` | `executeRefund` in Cancellation Service | Pending Refund $\rightarrow$ Completed | Reimbursing Vault |
| Reporting | All core transaction models | `ReportingCenterService::class` | Synchronous SQL aggregation | Multi-Tenant Branch Scoping |