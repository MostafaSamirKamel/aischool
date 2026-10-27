# تقرير 07: مسار تصحيح الطالب التالي دون انقطاع
# 07_NEXT_STUDENT_WORKFLOW

## 1. الهدف من ميزة "الطالب التالي" (Next Student Workflow)
توفير تجربة تصحيح فائقة السلاسة والسرعة للمحاضر، بحيث يستطيع تصحيح واجبات طلاب المجموعة المتتالية دون الحاجة للخروج من نافذة التقييم أو إعادة فتح المودال في كل مرة.

---

## 2. المنطق البرمجي (`HomeworkReviewCenter.php`)
تم تعريف خاصية محسوبة `getNextPendingStudentProperty()`:
```php
public function getNextPendingStudentProperty(): ?array
{
    if (!$this->selectedStudentId) {
        return null;
    }

    $roster = $this->studentRoster;
    return $roster->first(function ($row) {
        return $row['has_submission'] === true 
            && $row['review_status'] === 'awaiting_grading' 
            && $row['student_id'] != $this->selectedStudentId;
    });
}
```

ودالة الانتقال:
```php
public function nextStudent(): void
{
    $next = $this->nextPendingStudent;
    if ($next) {
        $this->openReview($next['student_id'], $next['submission']?->id);
    } else {
        Notification::make()
            ->title('تم الانتهاء من تصحيح كافة الحلول المسلّمة! 🎉')
            ->body('لا توجد حلول أخرى بانتظار التصحيح في هذا الواجب حالياً.')
            ->success()
            ->send();
    }
}
```

---

## 3. سلوك الواجهة التفاعلية
1. يظهر زر **"الطالب التالي"** بجانب زر "تم التصحيح" طالما يوجد طلاب آخرون سلموا حلولهم وبانتظار التقييم.
2. عند النقر عليه، يتم إغلاق بيانات الطالب الحالي تلقائياً واستبدالها بحل ومرفقات الطالب التالي فوراً.
3. يتم الحفاظ الكامل على سياق المجموعة والسيشن والواجب دون أي إعادة تحميل للصفحة (Zero Full Page Reloads).
4. عند اكتمال تصحيح جميع الطلاب، يتم إشعار المحاضر باكتمال تصحيح كافة واجبات الواجب بنجاح.
