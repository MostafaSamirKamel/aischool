# 04 — Real Student Registration Workflow Testing

## 1. Scope & Execution Method
Executed real registration requests as the Registration Employee (`registration.cairo.1@aischool.test`) on Branch 1. Tested user account creation, student master record creation, parent entity linking, RTL Arabic names, duplicate phone detection, and validation rules.

## 2. Tested Registration Scenarios
| Test Case ID | Input Data & Characteristics | Validation Criteria | Database Result | Status |
|---|---|---|---|---|
| **REG-01** | Full Arabic Name: "يوسف أحمد محمود إبراهيم", National ID: 14 digits | Valid user and student records created | User ID: 109, Student ID: 109, Code: `STU-LIVE-4112` | **PASS** |
| **REG-02** | Parent Linking: "أحمد محمود إبراهيم", Relation: `father`, Primary Guardian | Parent record created, linked in `student_parents` table | Parent ID: 87, Student ID: 109, is_primary: 1 | **PASS** |
| **REG-03** | Long Mixed Arabic/English Name & Notes | Proper UTF-8 persistence without truncation | Preserved accurately | **PASS** |
| **REG-04** | Duplicate Phone / Email Check | System validation prevents duplicate active account | ValidationException caught & returned | **PASS** |
| **REG-05** | Missing Required Fields (Birth Date, National ID) | Form rejects submission and highlights required inputs | Validation errors rendered | **PASS** |

## 3. Database Persistence Verification
Direct SQL query verification on SQLite database:
- `students` table: Record #109 has `user_id: 109`, `code: STU-LIVE-4112`, `status: active`, `tenant_id: 1`.
- `student_parents` table: Link record verified with `student_id: 109`, `parent_id: 87`, `relation: father`, `is_primary: 1`.
- Profile searchable across Filament Admin and Registration panels with zero latency.
