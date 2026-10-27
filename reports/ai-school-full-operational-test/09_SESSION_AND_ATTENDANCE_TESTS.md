# 09 — Educational Sessions & Attendance Recording Tests

## 1. Session Scheduling & Classroom Workflow
- **Group:** مجموعة الذكاء الاصطناعي (`Group ID: 1`)
- **Instructor Assigned:** أحمد محمد (`User ID: 14`)
- **Classroom:** قاعة 101 - معمل الحاسب
- **Scheduled Session ID:** `21`
- **Date & Time:** 2026-10-22 16:00:00

## 2. Real Attendance Recording Test
Four students in the test group were marked via the Livewire attendance matrix:
- **Student A (يوسف أحمد - ID: 109):** `present` (حاضر)
- **Student B (كريم محمود - ID: 110):** `present` (حاضر)
- **Student C (سارة علي - ID: 111):** `present` (حاضر)
- **Student D (عمر خالد - ID: 112):** `absent` (غائب)

## 3. Critical Business Rule Enforcement
> **Rule Check:** "A session without recorded attendance must NOT be interpreted as all students absent."
- Created an unrecorded scheduled session #22.
- Verified in Student Profile, Parent Portal, and Academic Reports: The session displays status **"مجدولة / لم يتم رصد الحضور"** (Scheduled / Not Recorded), and student absence counts were NOT incremented.
- Attendance percentage accurately calculated as $3/4 = 75\%$ for session #21.
