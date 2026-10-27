# Report 05: Complete 29 Test Students Data Manifest

## 1. Distribution Summary
- **Total Test Students:** **29 distinct students**
- **Group A (Python Junior):** 12 students (Indices 1 to 12)
- **Group B (HTML & CSS):** 5 students (Indices 13 to 17)
- **Group C (Scratch):** 12 students (Indices 18 to 29)
- **Existing Baseline Test Account:** Index 1 (`01007287273`, Student ID 203) integrated into Group A.

---

## 2. Complete Student Master Manifest
| Index | Student ID | Student Full Name | Mobile / Username | Assigned Group | Upfront Paid | Enrollment Status |
|---|---|---|---|---|---|---|
| 1 | 203 | زياد طارق - طالب أبو المطامير | `01007287273` | Group A — Python Junior | 1500 EGP | <span style="color:green">Active Enrolled</span> |
| 2 | 204 | يوسف أحمد محمود | `01007287002` | Group A — Python Junior | 500 EGP | <span style="color:green">Active Enrolled</span> |
| 3 | 205 | عمر خالد عبد الرحمن | `01007287003` | Group A — Python Junior | 500 EGP | <span style="color:green">Active Enrolled</span> |
| 4 | 206 | مصطفى إبراهيم علي | `01007287004` | Group A — Python Junior | 1500 EGP | <span style="color:green">Active Enrolled</span> |
| 5 | 207 | مريم محمد سعيد | `01007287005` | Group A — Python Junior | 500 EGP | <span style="color:green">Active Enrolled</span> |
| 6 | 208 | سلمى شريف يوسف | `01007287006` | Group A — Python Junior | 1500 EGP | <span style="color:green">Active Enrolled</span> |
| 7 | 209 | حمزة طارق كمال | `01007287007` | Group A — Python Junior | 500 EGP | <span style="color:green">Active Enrolled</span> |
| 8 | 210 | عبد الله ياسر حسن | `01007287008` | Group A — Python Junior | 1500 EGP | <span style="color:green">Active Enrolled</span> |
| 9 | 211 | جنى رامي فاروق | `01007287009` | Group A — Python Junior | 500 EGP | <span style="color:green">Active Enrolled</span> |
| 10 | 212 | كريم هيثم عادل | `01007287010` | Group A — Python Junior | 500 EGP | <span style="color:green">Active Enrolled</span> |
| 11 | 213 | هنا وائل سامح | `01007287011` | Group A — Python Junior | 1500 EGP | <span style="color:green">Active Enrolled</span> |
| 12 | 214 | نور أسامة فوزي | `01007287012` | Group A — Python Junior | 500 EGP | <span style="color:green">Active Enrolled</span> |
| 13 | 215 | بلال حسام متولي | `01007287013` | Group B — HTML & CSS | 1500 EGP | <span style="color:green">Active Enrolled</span> |
| 14 | 216 | رقية ممدوح فؤاد | `01007287014` | Group B — HTML & CSS | 500 EGP | <span style="color:green">Active Enrolled</span> |
| 15 | 217 | فارس علاء نبيل | `01007287015` | Group B — HTML & CSS | 1500 EGP | <span style="color:green">Active Enrolled</span> |
| 16 | 218 | ليلى حازم شوقي | `01007287016` | Group B — HTML & CSS | 500 EGP | <span style="color:green">Active Enrolled</span> |
| 17 | 219 | يحيى زكريا سليمان | `01007287017` | Group B — HTML & CSS | 500 EGP | <span style="color:red">Canceled & Settled</span> |
| 18 | 220 | مالك شريف عبد الله | `01007287018` | Group C — Scratch | 1200 EGP | <span style="color:green">Active Enrolled</span> |
| 19 | 221 | فريدة أحمد مختار | `01007287019` | Group C — Scratch | 400 EGP | <span style="color:green">Active Enrolled</span> |
| 20 | 222 | آدم محمد توفيق | `01007287020` | Group C — Scratch | 400 EGP | <span style="color:green">Active Enrolled</span> |
| 21 | 223 | تاليا وليد رشاد | `01007287021` | Group C — Scratch | 1200 EGP | <span style="color:green">Active Enrolled</span> |
| 22 | 224 | سليم هاني ماهر | `01007287022` | Group C — Scratch | 400 EGP | <span style="color:green">Active Enrolled</span> |
| 23 | 225 | خديجة أشرف إسماعيل | `01007287023` | Group C — Scratch | 1200 EGP | <span style="color:green">Active Enrolled</span> |
| 24 | 226 | ياسين تامر صبري | `01007287024` | Group C — Scratch | 400 EGP | <span style="color:green">Active Enrolled</span> |
| 25 | 227 | كرمة إيهاب شكري | `01007287025` | Group C — Scratch | 400 EGP | <span style="color:green">Active Enrolled</span> |
| 26 | 228 | إياد عمرو نجيب | `01007287026` | Group C — Scratch | 1200 EGP | <span style="color:green">Active Enrolled</span> |
| 27 | 229 | دارين حسام شلبي | `01007287027` | Group C — Scratch | 400 EGP | <span style="color:green">Active Enrolled</span> |
| 28 | 230 | أنس ماجد وفيق | `01007287028` | Group C — Scratch | 1200 EGP | <span style="color:green">Active Enrolled</span> |
| 29 | 231 | صوفيا شادي رفعت | `01007287029` | Group C — Scratch | 400 EGP | <span style="color:green">Active Enrolled</span> |


> [!NOTE]
> Student Index 17 (يحيى زكريا سليمان, Student ID 219) was designated for the complete enrollment cancellation, debt waiver, and financial refund settlement lifecycle test.

---

## 3. Test Result Format
- **Test ID:** STU-MNF-01
- **Module:** Student Administration
- **Role:** Branch Manager / Registrar
- **Route:** `/admin/students`
- **Initial State:** Staging student directory.
- **Actual Action Executed:** Seeded and enrolled exactly 29 unique students across 3 groups.
- **Expected Result:** Exact counts: 12 in Group A, 5 in Group B, 12 in Group C; zero duplicate phones.
- **Actual Result:** 29 valid linked profiles with auth accounts and student records.
- **Status:** **PASS**