# 13 — Management Appeal Decisions & Adjudication

## 1. Authority & Governance
To maintain impartial educational standards and protect instructor rights, formal appeals against supervisory evaluations can ONLY be adjudicated by Executive Management (`super_admin` or Branch Manager).

## 2. Decision Types
Management reviews the instructor's submission alongside the supervisor's evaluation notes and observation logs. Management may select one of three binding outcomes:
1. **Accepted (`accepted`):** Management upholds the instructor's appeal. Management may adjust criteria scores directly, triggering the creation of a new evaluation version snapshot in `instructor_evaluation_versions`.
2. **Rejected (`rejected`):** Management denies the appeal, validating the supervisor's evaluation. The evaluation returns to `published` status with management reasoning recorded.
3. **Returned for Clarification (`returned_for_clarification`):** Management requests additional documentation, class recordings, or written clarifications from both the supervisor and instructor.

## 3. Implementation in `EducationalSupervisionService`
```php
public function decideEvaluationAppeal(
    InstructorEvaluationAppeal $appeal,
    string $decision,
    User $manager,
    ?string $decisionNotes = null,
    ?array $scoreAdjustments = null
): InstructorEvaluationAppeal {
    if (!$manager->is_super_admin && !$manager->hasRole('branch_manager')) {
        throw new \DomainException('فقط الإدارة العليا أو مدير الفرع يملك صلاحية البت في الاعتراضات.');
    }

    $appeal->status = $decision;
    $appeal->decision_notes = $decisionNotes;
    $appeal->decided_by = $manager->id;
    $appeal->decided_at = now();
    $appeal->save();

    $evaluation = $appeal->evaluation;
    if ($decision === 'accepted' && !empty($scoreAdjustments)) {
        // Create pre-adjustment snapshot
        $this->createEvaluationVersionSnapshot($evaluation, 'Pre-appeal adjustment snapshot');
        
        // Apply adjustments
        foreach ($scoreAdjustments as $key => $val) {
            if (in_array($key, ['commitment_score', 'delivery_score', 'student_interaction_score', 'homework_followup_score', 'exam_execution_score', 'administrative_compliance_score'])) {
                $evaluation->$key = $val;
            }
        }
        $evaluation->recalculateOverallRating();
        $evaluation->status = InstructorEvaluation::STATUS_PUBLISHED;
        $evaluation->save();

        // Create post-adjustment snapshot
        $this->createEvaluationVersionSnapshot($evaluation, 'Post-appeal management adjustment snapshot');
    } else {
        $evaluation->status = InstructorEvaluation::STATUS_PUBLISHED;
        $evaluation->save();
    }

    return $appeal;
}
```
