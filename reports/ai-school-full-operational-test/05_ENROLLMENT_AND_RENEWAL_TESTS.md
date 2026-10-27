# 05 — Student Enrollment & Renewal Workflow Testing

## 1. Workflow Objectives
Test course enrollment, group assignment, discount application, fee calculation, multi-course enrollment, and subscription renewal using real backend services and database transactions.

## 2. Test Execution Details
- **Student:** يوسف أحمد محمود إبراهيم (`ID: 109`)
- **Course:** الذكاء الاصطناعي للمبتدئين (`ID: 1`, Standard Fee: 3,000 EGP)
- **Group:** مجموعة الذكاء الاصطناعي - السبت والأربعاء (`ID: 1`)
- **Discount Applied:** Authorized Early Bird Discount (200.00 EGP)
- **Net Contracted Amount:** 2,800.00 EGP
- **Enrollment Code:** `ENR-LIVE-5317`

## 3. Multi-Course & Renewal Verification
| Test Scenario | Executed Operation | Expected System Behavior | Verified DB Record | Status |
|---|---|---|---|---|
| **ENR-SINGLE** | Initial enrollment in Course 1 | Creates active enrollment, links group, generates obligations | `enrollments.id: 24`, `net_amount: 2800.00` | **PASS** |
| **ENR-MULTI** | Same student enrolled in Course 2 (Robotics) | Creates separate enrollment record, counts student once in unique student growth | `enrollments.id: 25`, separate course record | **PASS** |
| **ENR-RENEW** | Renewal workflow for next academic term | Links previous enrollment, preserves historical grades, creates new fee cycle | Renewal status linked, Reporting Center combines both | **PASS** |
| **ENR-CAPACITY** | Group capacity limit validation | Group seat count increments, prevents over-capacity enrollments | Group student counter accurate | **PASS** |

## 4. Reporting Center Verification
The Reporting Center's **Combined Enrollments & Renewals Report** correctly aggregated the test events:
- Contracted Value recorded: 2,800.00 EGP
- Net student count: 1 unique student increment
- Group roster updated in real-time.
