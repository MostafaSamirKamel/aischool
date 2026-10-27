# 07 — Homework Assignments & Student Submissions

## Overview
This document details the generation of structured homework tasks, student submissions, and grading feedback across all active courses, confirming realistic operational diversity and interactive review workflows.

---

## 1. Homework Assignments Master Inventory

A total of **39 new assignments** were created (bringing the system total to **48 assignments**):

| Course & Level | Assignment Title | Due Window | Max Score | Status |
|---|---|---|---|---|
| **Physics Secondary 1** | تطبيقات قوانين نيوتن للحركة | T-14 to T-7 | 10.0 | Published |
| **Physics Secondary 1** | مسائل المقاومة الكهربائية وقانون أوم | T-5 to T+2 | 10.0 | Published |
| **English Level 3** | Reading Comprehension: Scientific Advances | T-10 to T-3 | 10.0 | Published |
| **English Level 3** | Essay: Impact of Artificial Intelligence | T-2 to T+5 | 10.0 | Published |
| **Mathematics Sec 1** | حل معادلات الدرجة الثانية جبرياً | T-12 to T-4 | 10.0 | Published |
| **Science Prep 3** | التفاعلات الكيميائية وسرعة التفاعل | T-8 to T-1 | 10.0 | Published |

---

## 2. Student Submissions & Grading Breakdown

A total of **125 new submissions** were created (bringing the system total to **133 submissions**):

| Submission State | Grade Range | Status In Database | Count | Characteristics |
|---|---|---|---|---|
| **High Performance (ممتاز)** | 9.0 – 10.0 / 10 | `graded` | 55 | Submitted well before deadline; high accuracy; detailed positive instructor feedback. |
| **Average Performance (جيد)** | 7.0 – 8.5 / 10 | `graded` | 42 | Submitted on-time or slightly delayed; minor corrections noted by instructor. |
| **At-Risk Performance (ضعيف)** | 3.5 – 5.0 / 10 | `graded` | 18 | Late submission; conceptual misunderstandings; recommendations to attend office hours. |
| **Pending Review (قيد التصحيح)** | — | `pending` | 10 | Submitted on time; awaiting instructor grading in queue. |
| **Unsubmitted Cases (لم يسلم)** | — | *No Submission Record* | Assorted | Students who missed deadline; appears as "لم يسلم" in reporting. |

---

## 3. Instructor Grading Workflow & Audit

Sample instructor feedback persisted in `assignment_submissions.feedback`:
- *"إجابة نموذجية ومتميزة، أحسنت في توضيح خطوات حل المسألة والتطبيق على القانون."* (Score: 10/10)
- *"عمل جيد، ولكن يُرجى مراجعة وحدات القياس الفيزيائية والتركيز في خطوات التحويل."* (Score: 7.5/10)
- *"هناك أخطاء جوهرية في فهم القانون الثاني، يرجى مراجعة المحاضرة وحضور السيشن التعويضي."* (Score: 4.0/10)

### UI Editability & Management:
- **Instructor Review Modal**: Instructors can open pending submissions, input scores, modify feedback, and publish grades.
- **Student Dashboard**: Students can download problem prompts, view their score, read teacher remarks, and see submission timestamps.
