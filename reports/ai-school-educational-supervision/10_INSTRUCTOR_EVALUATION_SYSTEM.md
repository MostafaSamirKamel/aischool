# 10 — Instructor Evaluation System & Multi-Criteria Rating Engine

## 1. Evaluation Architecture
The Instructor Evaluation System (`InstructorEvaluationResource`) enables supervisors to conduct both optional session-based evaluations and formal monthly evaluations.

## 2. Six Evaluative Criteria (1 to 5 Integer Rating)
Each criterion evaluates an essential dimension of instructor performance:
1. **Commitment & Punctuality (`commitment_score`):**
   - Starts and ends sessions on time.
   - Records attendance promptly within 24 hours.
   - Adheres to the approved syllabus timetable.
2. **Pedagogical Delivery & Clarity (`delivery_score`):**
   - Explains technical concepts clearly.
   - Uses interactive exercises and modern educational aids.
   - Adapts pace to student understanding.
3. **Student Interaction & Classroom Rapport (`student_interaction_score`):**
   - Fosters an encouraging and respectful environment.
   - Answers questions thoroughly.
   - Handles differing learning abilities patiently.
4. **Homework & Assignment Follow-up (`homework_followup_score`):**
   - Posts assignments regularly according to syllabus requirements.
   - Grades submissions within 48 hours.
   - Provides meaningful, personalized feedback to students.
5. **Exam Execution & Evaluation Accuracy (`exam_execution_score`):**
   - Administers exams punctually and under fair conditions.
   - Grades exam attempts accurately and without bias.
   - Publishes verified results on time.
6. **Administrative & Platform Compliance (`administrative_compliance_score`):**
   - Uses AI School platform features properly without bypassing tools.
   - Communicates proactively with supervisors and educational secretaries.
   - Responds constructively to supervisory observations.

## 3. The "Not Observed" Rule & Dynamic Arithmetic Average
In operational school environments, a supervisor might not observe a specific criterion during a given period (e.g., no exam was scheduled during that month).
- For each criterion $i$, a boolean flag `*_unobserved` is provided.
- If `*_unobserved = true`, the score is set to `null` or excluded from the calculation.
- **Formula:**
  $$\text{Overall Rating} = \frac{\sum_{i \in \text{Observed}} \text{Score}_i}{|\text{Observed}|}$$
- If no criteria are observed, `overall_rating` defaults to `null`.
- This ensures an instructor is never penalized with a zero score for an unobserved dimension.

## 4. Qualitative Analysis Sections
Alongside numerical scores, every evaluation contains three mandatory qualitative fields:
- `strengths`: Documented pedagogical strengths and standout achievements.
- `areas_for_improvement`: Concrete areas where the instructor needs to improve.
- `recommendations`: Actionable steps, training recommendations, or operational guidance.
