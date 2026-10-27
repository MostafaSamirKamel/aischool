# تقرير 02: علاقات الواجبات بالجلسات والسيشنات
# 02_SESSION_HOMEWORK_RELATIONSHIPS

## 1. تصميم العلاقات المحسنة
تمت إضافة حقول الربط بالسيشن إلى جدول `assignments` عبر الميجريشن:
`database/migrations/2026_10_25_210000_add_session_fields_to_assignments_table.php`

الحقول المضافة:
- `attendance_session_id` (foreign key nullable -> `attendance_sessions.id`)
- `session_schedule_id` (foreign key nullable -> `session_schedules.id`)
- `session_number` (unsignedInteger nullable)

---

## 2. المنطق المتبع لربط الواجبات
- **الواجبات المرتبطة بجلسة فعلية:** ترتبط عبر `attendance_session_id`.
- **الواجبات المجدولة مسبقاً:** ترتبط عبر `session_schedule_id`.
- **الواجبات التاريخية أو العامة (Unlinked Homework):** تكون فيها حقول السيشن `null`، ويتم عرضها بصورة منظمة في قسم مستقل: **"واجبات عامة للمجموعة (غير مرتبطة بسيشن محدد)"**، دون اختلاق أو تزوير أي ربط تاريخي غير موثق.

---

## 3. دوال النموذج `Assignment.php`
- `attendanceSession()`: علاقة `belongsTo(AttendanceSession::class)`
- `sessionSchedule()`: علاقة `belongsTo(SessionSchedule::class)`
- Accessors:
  - `getSessionDateAttribute()`: إرجاع تاريخ السيشن سواء كان جلسة فعلية أو مجدولة.
  - `getSessionTitleAttribute()`: إرجاع عنوان أو رقم الجلسة المرتبطة.

---

## 4. نموذج الإدخال (`AssignmentForm.php`)
تم تحديث فورم إنشاء وتعديل الواجبات في لوحة المحاضر لإتاحة اختيار السيشن المرتبط بكل سهولة من قائمة منسدلة تعرض جلسات المجموعة المتاحة:
```php
Forms\Components\Select::make('attendance_session_id')
    ->label('الجلسة / السيشن المرتبط')
    ->options(function ($get) {
        $groupId = $get('group_id');
        if (!$groupId) return [];
        return AttendanceSession::where('group_id', $groupId)
            ->orderBy('attendance_date')
            ->get()
            ->mapWithKeys(fn($s, $idx) => [
                $s->id => 'الجلسة ' . ($idx + 1) . ' (' . ($s->attendance_date?->format('Y-m-d') ?? 'بدون تاريخ') . ')'
            ]);
    })
    ->searchable()
    ->placeholder('واجب عام (غير مرتبط بسيشن محدد)'),
```
