# 10 — Academic Follow-Up & Educational Supervision

## Overview
This document specifies the operational records generated within the Student Follow-Up Center and the Educational Supervision Center, verifying multi-level workflows, administrative oversight, and role authorization boundaries.

---

## 1. Student Academic Follow-Up Tasks & Call Logs

A total of **8 new follow-up cases** and **16 detailed call logs** were generated:

| Task / Alert Reason | Assigned Employee | Associated Student | Call Log Status | Task Final Status |
|---|---|---|---|---|
| **تراجع حاد في درجات الفيزياء (<50%)** | سكرتارية المتابعة (أ. رنا) | يوسف إبراهيم (ID 3) | `answered` (تم التحدث مع ولي الأمر) | `contacted` (تم التواصل) |
| **تكرار الغياب بدون عذر (3 جلسات)** | سكرتارية المتابعة (أ. رنا) | مريم أحمد (ID 4) | `answered` (اتفاق على حضور تعويضي) | `contacted` (تم التواصل) |
| **إنذار غياب ورسوب باختبار الوحدة** | سكرتارية المتابعة (أ. رنا) | زياد أيمن (ID 9) | `unanswered` (الهاتف مغلق / لم يرد) | `new` (جديدة - قيد المتابعة) |
| **تأخر متكرر عن تسليم الواجبات** | سكرتارية المتابعة (أ. رنا) | كريم حسام (ID 7) | `answered` (متابعة ولي الأمر تمت) | `contacted` (تم التواصل) |

### Core Business Rule Compliance:
> [!IMPORTANT]
> **Strict Call Log State Enforcement**:
> In accordance with AI School policies, **unanswered calls do NOT transition a case to `contacted`**. Task ID 9 remains strictly `new` because the contact attempt was recorded as `unanswered` (لم يتم الرد).

---

## 2. Educational Supervision Master Inventory

The Educational Supervision Center was populated with comprehensive records covering supervisory classroom observations, formal evaluations, instructor appeals, and executive reports:

### A. Supervisory Classroom Observations
- **Total in Database**: **11 observations** (2 new)
- **Status Distribution**:
  - `completed` (مكتملة ومغلقة بمعرفة المشرف): **8 observations**
  - `waiting_review` (في انتظار مراجعة المعلم/المشرف): **2 observations**
  - `new` (ملاحظة جديدة): **1 observation**
- **Sample Note**:
  *"تمت ملاحظة الحصة النموذجية لمادة الفيزياء. التفاعل الصفي ممتاز واستخدام الشاشة التفاعلية ساعد في تبسيط قانون نيوتن. يُوصى بزيادة وقت الأسئلة الفردية للطلاب في نهاية الجلسة."*

### B. Monthly Instructor Evaluations & Criteria
- **Total in Database**: **8 comprehensive evaluations**
- **Detailed Criteria**: Each evaluation contains **6 standard quality metrics** (Planning, Subject Mastery, Student Engagement, Assessment Diversity, Class Management, Punctuality).
- **Independence Rule**: Instructor evaluations remain strictly decoupled from base salary disbursements to preserve pedagogical objectivity.

### C. Formal Evaluation Appeals & Management Resolutions
- **Total in Database**: **7 appeals** (1 new)
- **Workflow State**:
  - Instructor filed an objection regarding classroom engagement scoring.
  - Educational Supervisor submitted clarifying notes.
  - School Management formally reviewed and approved the score adjustment (`STATUS_ACCEPTED`).

### D. Periodic Supervision Reports
- **Total in Database**: **5 periodic reports**
- **New Report**:
  - Periodic evaluation covering Term 1 academic performance, instructor adherence rates, and classroom observation metrics.
  - Stored with `period_key`, `period_start`, `period_end`, and `metrics_snapshot`.
